.PHONY: setup up down composer-install migrate seed

setup:
	docker compose up -d --build
	docker compose exec app composer install
	docker compose exec app php bin/migrate.php
	docker compose exec app php bin/seed.php

up:
	docker compose up -d

down:
	docker compose down

composer-install:
	docker compose exec app composer install

migrate:
	docker compose exec app php bin/migrate.php

seed:
	docker compose exec app php bin/seed.php
