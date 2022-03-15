SHELL:=/bin/bash
NAME = phpda\:cli
MAMBA_PATH = $(mamba)

.PHONY: build
build:
	docker build -t $(NAME) -f ./docker/Dockerfile .
	docker run --rm -it -v $(shell pwd):/app $(NAME) bash -c "composer install"
	cp ./config/parameters.php ./parameters.local.php

.PHONY: run
run:
	docker run --rm -it \
		-v $(shell pwd):/app \
		-v $(MAMBA_PATH):/mamba \
		$(NAME) bash -c "bin/phpda analyze --config /app/parameters.local.php"
