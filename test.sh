vendor/bin/php-cs-fixer fix src
vendor/bin/php-cs-fixer fix tests
vendor/bin/phpstan analyse --level 8 src tests
php test.php