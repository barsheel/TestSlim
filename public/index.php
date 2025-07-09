<?php
namespace App;
// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use App\UserDAO;
use Testslim\Validator;
use Testslim\Authentificator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Responseж;

const DB_FILENAME = __DIR__.'/../data/database.sqlite';

session_start();

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$container->set(\PDO::class, function () {
    $file = DB_FILENAME;
    //mkdir($file, 0755, true);
    $conn = new \PDO("sqlite:{$file}");
    $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    return $conn;
});

$container->set(UserDAO::class, new UserDAO($container->get(\PDO::class)));

$app = AppFactory::createFromContainer($container);
$app->add(MethodOverrideMiddleware::class);

$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser();

$validator = new Validator();

$authentificator = new Authentificator();

$loginMiddleware = function ($request, $handler) use ($authentificator) {
    
    $login = $_SESSION['login'] ?? 'none';
    $password = $_SESSION['password'] ?? 'none';
    $errors = $authentificator->authUser($login, $password);

    if ($request->getUri()->getPath() !== '/login' && count($errors) !== 0) {
        $this->get('flash')->addMessage('success', 'you must log in');
        $response = new \Slim\Psr7\Response();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    return $handler->handle($request);
};

$app->add($loginMiddleware);

//NEW USER FORM
$app->get('/users/new', function ($request, $response, $args) {
    $params = [
        'user' => [
            'name' => '',
            'email' => ''
        ],
        'errors' => []
    ];
    
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
})->setName('new_user');


//SHOW USER
$app->get('/users/{id}', function ($request, $response, $args)  {

    $id = $args['id'];
    $user = $this->get(UserDAO::class)->getUserById($id);
    $params = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email']
    ];

    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');


//SHOW ALL USERS (SEARCH)
$app->get('/users', function ($request, $response, $args) {
    print_r(__DIR__.'/data/database.json');
    $term = $request->getQueryParam('term') ?? '';
    $users = $this->get(UserDAO::class)->readAll();

    /*$selectedUsers = array_filter (
        $users,
        fn ($user) => str_contains($user['name'] ?? "", $term)
    );*/

    $params = [
        'users' => $users,
        'term' => $term,
        'flash' => $this->get('flash')->getMessages()
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

//ADD USER
$app->post('/users', function ($request, $response, $args) use ($router, $validator) {
    $user = $request->getParsedBodyParam('user');

    $errors = $validator->validate($user);
    if (count($errors) === 0)
    {
            $this->get(UserDAO::class)->insert($user['name'], $user['email']);
            $this->get('flash')->addMessage('success', 'User sucessfully added');
            return $response->withRedirect($router->urlFor('users'), 302);
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response->withStatus(422), 'users/new.phtml', $params);
})->setName('add_user');

//EDIT USER FORM
$app->get('/users/{id}/edit', function ($request, $response, array $args)
{   
    $id = $args['id'];
    $user = $this->get(UserDAO::class)->getUserById($id);

    $params = [
        'user' => $user
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});

//EDIT USER
$app->patch('/users/{id}', function ($request, $response, $args) use ($router, $validator) { 
    $users = $this->get(UserDAO::class)->readAll();
    $id = $args['id'];

    $userToPatch = $request->getParsedBodyParam('user');

    $name = $userToPatch['name'];
    $email = $userToPatch['email'];

    $errors = $validator->validate($userToPatch);

    $newUser = User::createFromArray([
        'id' => $id,
        'name' => $name,
        'email' => $email
    ]);

    if (count($errors) === 0)
        {

            $this->get(UserDAO::class)->update($newUser);
            $this->get('flash')->addMessage('success', 'User sucessfully updated');
            return $response->withRedirect($router->urlFor('users'), 302);
    }

    $params = [
        'user' => $newUser,
        'errors' => $errors
    ];
    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('patch_user');


$app->get('/login', function ($request, $response) use ($authentificator) {
    $params = [
        'flash' => $this->get('flash')->getMessages()
    ];
    return $this->get('renderer')->render($response, 'users/login.phtml', $params);
});

$app->post('/login', function ($request, $response) use ($authentificator, $router) {
    
    $login = $request->getParsedBodyParam('login');
    $password = $request->getParsedBodyParam('password');

    $errors = $authentificator->authUser($login, $password);

    if (count($errors) === 0) {
        
        $_SESSION['login'] = $login;
        $_SESSION['password'] = $password;

        $this->get('flash')->addMessage('success', 'access granted');
        return $response->withRedirect($router->urlFor('users'));
    }

    $params = [
        'login' => $login,
        'errors' => $errors,
    ];
    return $this->get('renderer')->render($response->withStatus(401), 'users/login.phtml', $params);
});

$app->get('/logout', function ($request, $response) use ($router) {
    $_SESSION = [];
    session_destroy();
    session_start();
    $this->get('flash')->addMessage('success', 'GTFO BTCH');
    return $response->withRedirect($router->urlFor('users'));
});

$app->run();