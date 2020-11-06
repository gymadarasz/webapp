sudo rm -rf src/templates/cache/
vendor/bin/php-cs-fixer fix src
vendor/bin/php-cs-fixer fix tests
vendor/bin/phpstan analyse --level 8 src
vendor/bin/phpstan analyse --level 8 tests
php test.php