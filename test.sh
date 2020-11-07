rm -rf src/templates/cache/

vendor/bin/php-cs-fixer fix src
vendor/bin/php-cs-fixer fix tests
vendor/bin/phpcs src --ignore=*/config/*
vendor/bin/phpcs tests
vendor/bin/phpcbf src
vendor/bin/phpcbf tests
vendor/bin/phpstan analyse --level 8 src
vendor/bin/phpstan analyse --level 8 tests

#vendor/bin/php-cs-fixer fix tests
#vendor/bin/phpcbf -h tests
#vendor/bin/phpstan analyse --level 8 tests
#vendor/bin/phpcs -h tests

php test.php