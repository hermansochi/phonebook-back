init: init-ci react-ready underdante-ready vue-ready
	
init-ci: docker-down-clear \
	react-clear vue-clear underdante-clear e2e-clear \
	docker-pull docker-build docker-up \
	api-init react-init underdante-init vue-init e2e-init

init-andrey: docker-down-clear \
	react-clear \
	docker-pull docker-build docker-up \
	api-init react-init

init-ann: docker-down-clear \
	vue-clear \
	docker-pull docker-build docker-up \
	api-init vue-init

init-underdante: docker-down-clear \
	underdante-clear \
	docker-pull docker-build docker-up \
	api-init underdante-init

up: docker-up
down: docker-down
restart: down up
lint: react-lint vue-lint
lint-fix: react-lint-fix
test-e2e:
	make api-migrations
	make api-fixtures
	make e2e-clear
	- make e2e-cucumber
	make e2e-report

test-smoke:
	make api-migrations
	make api-fixtures
	make e2e-clear
	- make smoke-cucumber
	make e2e-report

images:
	docker images

prune:
	docker system prune

memory:
	sudo sh -c "echo 3 > /proc/sys/vm/drop_caches"

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

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
	docker compose run --rm api-php-cli composer install

api-tests:
	docker compose run --rm api-php-cli php artisan test

api-test:
	docker compose run --rm api-php-cli ./vendor/bin/phpunit

api-tests-coverage:
	docker compose run --rm api-php-cli vendor/bin/phpunit --coverage-html reports/

api-psalm:
	docker compose run --rm api-php-cli ./vendor/bin/psalm --show-info=true

api-lint:
	docker compose run --rm api-php-cli ./vendor/bin/pint

api-analyze:
	docker compose run --rm api-php-cli ./vendor/bin/psalm

api-wait-db:
	docker compose run --rm api-php-cli wait-for-it api-postgres:5432 -t 30

api-migrations:
	docker compose run --rm api-php-cli php artisan migrate:fresh

api-fixtures:
	docker compose run --rm api-php-cli php artisan db:seed
	docker compose run --rm api-php-cli php artisan avatar:add

api-generate-docs:
	mkdir -p ${PWD}/api/.scribe/endpoints/ && cp ${PWD}/docs/scribe/* ${PWD}/api/.scribe/endpoints/
	docker compose run --rm api-php-cli php artisan route:clear
	docker compose run --rm api-php-cli php artisan config:clear
	docker compose run --rm api-php-cli php artisan scribe:generate

react-clear:
	docker run --rm -v ${PWD}/react:/app -w /app alpine sh -c 'rm -rf .ready build'

underdante-clear:
	docker run --rm -v ${PWD}/underdante:/app -w /app alpine sh -c 'rm -rf .ready build'

vue-clear:
	docker run --rm -v ${PWD}/vue:/app -w /app alpine sh -c 'rm -rf .ready dist'

react-init: react-yarn-install

underdante-init: underdante-yarn-install

vue-init: vue-npm-install

react-yarn-install:
	docker compose run --rm react-node-cli yarn install

underdante-yarn-install:
	docker compose run --rm underdante-node-cli yarn install

vue-npm-install:
	docker compose run --rm vue-node-cli npm instal

react-ready:
	docker run --rm -v ${PWD}/react:/app -w /app alpine touch .ready

underdante-ready:
	docker run --rm -v ${PWD}/underdante:/app -w /app alpine touch .ready

vue-ready:
	docker run --rm -v ${PWD}/vue:/app -w /app alpine touch .ready

react-lint:
	docker compose run --rm react-node-cli yarn eslint
	docker compose run --rm react-node-cli yarn stylelint

react-lint-fix:
	docker compose run --rm react-node-cli yarn eslint-fix

react-test-watch:
	docker compose run --rm react-node-cli yarn test

react-test:
	docker compose run --rm react-node-cli yarn test --watchAll=false

underdante-lint:
	docker compose run --rm underdante-node-cli yarn eslint
	docker compose run --rm underdante-node-cli yarn stylelint

underdante-lint-fix:
	docker compose run --rm underdante-node-cli yarn eslint-fix

underdante-test-watch:
	docker compose run --rm underdante-node-cli yarn test

underdante-test:
	docker compose run --rm underdante-node-cli yarn test --watchAll=false

vue-lint:
	docker compose run --rm vue-node-cli npm run lint

e2e-init: e2e-assets-install

e2e-assets-install:
	docker compose run --rm cucumber-node-cli yarn install

e2e-cucumber:
	docker compose run --rm cucumber-node-cli yarn e2e

smoke-cucumber:
	docker compose run --rm cucumber-node-cli yarn smoke

e2e-lint:
	docker compose run --rm cucumber-node-cli yarn lint

e2e-lint-fix:
	docker compose run --rm cucumber-node-cli yarn lint-fix

e2e-clear:
	docker run --rm -v ${PWD}/e2e:/app -w /app alpine sh -c 'rm -rf var/*'

e2e-report:
	docker compose run --rm cucumber-node-cli yarn report

build: build-api build-frontend

build-frontend:
	docker --log-level=debug build --pull --file=react/docker/production/nginx/Dockerfile --tag=${REGISTRY}/pet-react:${IMAGE_TAG} react
	docker --log-level=debug build --pull --file=frontend/docker/production/nginx/Dockerfile --tag=${REGISTRY}/frontend:${IMAGE_TAG} frontend
	docker --log-level=debug build --pull --file=underdante/docker/production/nginx/Dockerfile --tag=${REGISTRY}/pet-underdante:${IMAGE_TAG} underdante
	docker --log-level=debug build --pull --file=vue/docker/production/nginx/Dockerfile --tag=${REGISTRY}/pet-vue:${IMAGE_TAG} vue

build-api:
	docker --log-level=debug build --pull --file=api/docker/production/nginx/Dockerfile --tag=${REGISTRY}/pet-api:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/php-fpm/Dockerfile --tag=${REGISTRY}/api-php-fpm:${IMAGE_TAG} api
	docker --log-level=debug build --pull --file=api/docker/production/php-cli/Dockerfile --tag=${REGISTRY}/api-php-cli:${IMAGE_TAG} api

try-build:
	REGISTRY=localhost IMAGE_TAG=0 make build

push: push-frontend push-react push-underdante push-vue push-api

push-frontend:
	docker push ${REGISTRY}/frontend:${IMAGE_TAG}

push-react:
	docker push ${REGISTRY}/pet-react:${IMAGE_TAG}

push-underdante:
	docker push ${REGISTRY}/pet-underdante:${IMAGE_TAG}

push-vue:
	docker push ${REGISTRY}/pet-vue:${IMAGE_TAG}

push-api:
	docker push ${REGISTRY}/pet-api:${IMAGE_TAG}
	docker push ${REGISTRY}/api-php-fpm:${IMAGE_TAG}
	docker push ${REGISTRY}/api-php-cli:${IMAGE_TAG}

deploy:
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'rm -rf site_${BUILD_NUMBER} && mkdir site_${BUILD_NUMBER}'
	envsubst < docker-compose-production.yml > docker-compose-production-env.yml
	scp -o StrictHostKeyChecking=no -P ${PORT} docker-compose-production-env.yml deploy@${HOST}:site_${BUILD_NUMBER}/docker-compose.yml
	rm -f docker-compose-production-env.yml
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'mkdir site_${BUILD_NUMBER}/secrets'
	scp -o StrictHostKeyChecking=no -P ${PORT} ${API_DB_PASSWORD_FILE} deploy@${HOST}:site_${BUILD_NUMBER}/secrets/api_db_password
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker stack deploy  --compose-file docker-compose.yml server --with-registry-auth --prune'

deploy-clean:
	rm -f docker-compose-production-env.yml

rollback:
	ssh -o StrictHostKeyChecking=no deploy@${HOST} -p ${PORT} 'cd site_${BUILD_NUMBER} && docker stack deploy --compose-file docker-compose.yml auction --with-registry-auth --prune'

validate-jenkins:
	curl --user ${USER} -X POST -F "jenkinsfile=<Jenkinsfile" ${HOST}/pipeline-model-converter/validate