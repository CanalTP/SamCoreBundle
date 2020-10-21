SamCoreBundle
=============

This bundle is part of [Navitia Mobility Manager](https://github.com/CanalTP/navitia-mobility-manager)
It contains
- Entities (Application, Customer, Role...)
- Customer management

Unit Tests
----------

**Requirements** : [docker](https://docs.docker.com/get-docker/)

You can launch unit tests following these steps :

1. Build docker image
```
mkdir -p ${HOME}/.config/composer
_UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml build --no-cache --force-rm --pull samcore-app
```
2. Launch composer
```
rm -f composer.lock
_UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm samcore-app composer install --no-interaction --prefer-dist
```
Note : first launch can be very long (>45mins) depending on your device resources.

3. Launch unit tests
```
_UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm samcore-app \
./vendor/bin/phpunit --testsuite=SamCoreUnit --log-junit=docs/unit/logs/junit.xml --coverage-html=docs/unit/CodeCoverage --coverage-clover=docs/unit/logs/coverage.xml
```
You could see the coverage result in the folder docs/unit/CodeCoverage

License
-------

This bundle is released under the [GPL-3.0 License](LICENSE)
