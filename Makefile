PORT ?= 8000
.PHONY: start

start:
    php -S 0.0.0.0:$(PORT) -t public public/index.php