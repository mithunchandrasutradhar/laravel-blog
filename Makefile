.PHONY: up down build fresh logs shell artisan migrate seed

up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build --no-cache

fresh:
	docker-compose down -v
	docker-compose up -d --build
	docker-compose exec app php artisan migrate:fresh --seed

logs:
	docker-compose logs -f app

shell:
	docker-compose exec app bash

artisan:
	docker-compose exec app php artisan $(cmd)

migrate:
	docker-compose exec app php artisan migrate

seed:
	docker-compose exec app php artisan db:seed

npm-install:
	docker-compose exec app npm install

npm-build:
	docker-compose exec app npm run build

test:
	docker-compose exec app php artisan test

cache-clear:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

storage-link:
	docker-compose exec app php artisan storage:link
