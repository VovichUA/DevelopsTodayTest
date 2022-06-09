#-----------------------------------------------------------
# Docker
#-----------------------------------------------------------

# Only first start application!!!
bootstrap:
	# run api
	cp ./app/.env.example ./app/.env
	cp docker-compose.override.example.yml docker-compose.override.yml
	make build
	make composer-install

	make chmod-permissions
	make artisan cmd="config:clear"
	make artisan cmd="key:generate"
	make artisan cmd="migrate"
	make artisan cmd="migrate:refresh --seed"
	make artisan cmd="passport:install"

# Build and up docker containers
build:
	docker-compose up -d --build

# Build and up docker containers
rebuild:
	make stop
	make build

# Wake up docker containers
start:
	docker-compose up -d
	make composer-install
	make migrate
	make npm cmd="install --no-save"
	make npm cmd="run build"
#	make npm cmd="run dev"

# Shut down docker containers
stop:
	docker-compose down

# Show a status of each container
status:
	docker-compose ps

# Show logs of each container
logs:
	docker-compose logs

# Restart all containers
restart:
	make stop
	make start

# Show the client logs
logs-client:
	docker-compose logs client

# Build containers with no cache option
build-no-cache:
	docker-compose build --no-cache

# Run terminal of the php container
exec-php:
	docker-compose exec -T app /bin/sh

# Run terminal of the client container
exec-client:
	docker-compose exec -T client /bin/sh

chmod-permissions:
	docker-compose exec -T app chmod 777 -R storage/
	docker-compose exec -T app chmod 777 -R bootstrap/cache

composer:
    ifneq ($(cmd),)
	    docker-compose exec -T app /bin/sh -c "composer $(cmd)"
    else
	    docker-compose exec -T app /bin/sh -c "composer"
    endif

artisan:
    ifneq ($(cmd),)
		docker-compose exec -T app php artisan $(cmd)
    else
		docker-compose exec -T app php artisan
    endif

npm:
    ifneq ($(cmd),)
		docker-compose exec client sh -c "npm $(cmd)"
    else
		docker-compose exec client sh -c "npm"
    endif

code-style-fix:
	make artisan cmd="fixer:fix --diff"

code-style-check:
	make artisan cmd="fixer:fix  --verbose --show-progress=dots --dry-run"

#-----------------------------------------------------------
# Database
#-----------------------------------------------------------

# Run database migrations
migrate:
	docker-compose exec app php artisan migrate

# Run migrations rollback
db-rollback:
	docker-compose exec app php artisan migrate:rollback

# Rollback alias
rollback: db-rollback

# Run seeders
db-seed:
	docker-compose exec app php artisan db:seed


#-----------------------------------------------------------
# Queue
#-----------------------------------------------------------

# Restart queue process
queue-restart:
	docker-compose exec app php artisan queue:restart

#-----------------------------------------------------------
# Dependencies
#-----------------------------------------------------------

# Install composer dependencies
composer-install:
	docker-compose exec app composer install

# Update composer dependencies
composer-update:
	docker-compose exec app composer update


