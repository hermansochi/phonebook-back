init: create-newtork init-ci 

init-ci: docker-down-clear \
	docker-pull docker-build docker-up \
	api-init

create-newtork:
	docker network create --attachable traefik-public || true

up: create-newtork docker-up
down: docker-down
restart: down up

prune:
	docker system prune -af --volumes

memory:
	sudo sh -c "echo 3 > /proc/sys/vm/drop_caches"

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans
	docker network rm traefik-public

docker-down-clear:
	docker compose down -v --remove-orphans

docker-pull:
	docker compose pull

docker-build:
	docker compose build --pull

api-init: api-permissions api-composer-install api-wait-db api-migrations api-fixtures api-generate-docs

api-permissions:
	docker run --rm -v ${PWD}/api:/app -w /app alpine chmod -R 777 storage bootstrap

api-composer-install:
	docker compose run --rm php-cli composer install

api-tests:
	docker compose run --rm php-cli php artisan test

api-test:
	docker compose run --rm php-cli ./vendor/bin/phpunit

api-tests-coverage:
	docker compose run --rm php-cli vendor/bin/phpunit --coverage-html reports/

api-psalm:
	docker compose run --rm php-cli ./vendor/bin/psalm --show-info=true

api-lint:
	docker compose run --rm php-cli ./vendor/bin/pint

api-analyze:
	docker compose run --rm php-cli ./vendor/bin/psalm

api-wait-db:
	docker compose run --rm php-cli wait-for-it api-postgres:5432 -t 30

api-migrations:
	docker compose run --rm php-cli php artisan migrate:fresh
	docker compose run --rm php-cli php artisan github:get

api-fixtures:
#	docker compose run --rm php-cli php artisan db:seed
#	docker compose run --rm php-cli php artisan avatar:add

api-generate-docs:
#	mkdir -p ${PWD}/api/.scribe/endpoints/ && cp ${PWD}/docs/scribe/* ${PWD}/api/.scribe/endpoints/
#	docker compose run --rm php-cli php artisan route:clear
#	docker compose run --rm php-cli php artisan config:clear
#	docker compose run --rm php-cli php artisan scribe:generate

build: build-api

build-api:
	docker --log-level=debug build --pull --file=api/docker/production/nginx/Dockerfile --tag=${REGISTRY}/herman-team-api:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/php-fpm/Dockerfile --tag=${REGISTRY}/php-fpm:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/php-cli/Dockerfile --tag=${REGISTRY}/appinit:${IMAGE_TAG} api

try-build:
	REGISTRY=localhost IMAGE_TAG=0 make build

push: push-api

push-api:
	docker push ${REGISTRY}/herman-team-api:${IMAGE_TAG}
	docker push ${REGISTRY}/php-fpm:${IMAGE_TAG}
	docker push ${REGISTRY}/appinit:${IMAGE_TAG}

deploy:
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'docker network create --driver=overlay --attachable traefik-public || true'
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'docker node update --label-add back.manager=true $$(docker info -f "{{.Swarm.NodeID}}")'
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'rm -rf back_${BUILD_NUMBER} && mkdir back_${BUILD_NUMBER}'
	envsubst < docker-compose-production.yml > docker-compose-production-env.yml
	scp -o StrictHostKeyChecking=no -P ${PORT} docker-compose-production-env.yml deploy@${HOST}:back_${BUILD_NUMBER}/docker-compose.yml
	rm -f docker-compose-production-env.yml
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'mkdir back_${BUILD_NUMBER}/secrets'
	scp -o StrictHostKeyChecking=no -P ${PORT} ${API_DB_PASSWORD_FILE} deploy@${HOST}:back_${BUILD_NUMBER}/secrets/api_db_password
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd back_${BUILD_NUMBER} && docker stack deploy  --compose-file docker-compose.yml server --with-registry-auth --prune'
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd back_${BUILD_NUMBER} && docker service update --force --with-registry-auth server_appinit'

deploy-clean:
	rm -f docker-compose-production-env.yml

rollback:
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd back_${BUILD_NUMBER} && docker stack deploy --compose-file docker-compose.yml auction --with-registry-auth --prune'

validate-jenkins:
	curl --user ${USER} -X POST -F "jenkinsfile=<Jenkinsfile" ${HOST}/pipeline-model-converter/validate