rm -rf src/templates/cache/
rm -rf mails/

echo "------------- php-cs-fixer -------------"
vendor/bin/php-cs-fixer fix src
vendor/bin/php-cs-fixer fix tests

echo "------------- csfix.php -------------"
php ./csfix.php src
php ./csfix.php test

echo "------------- phpcs -------------"
vendor/bin/phpcs src --ignore=*/config/*
vendor/bin/phpcs tests

echo "------------- phpcbf -------------"
vendor/bin/phpcbf src
vendor/bin/phpcbf tests

echo "------------- phpstan -------------"
vendor/bin/phpstan analyse --level 8 src
vendor/bin/phpstan analyse --level 8 tests

echo "------------- phpmd -------------"
vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode
vendor/bin/phpmd tests text cleancode,codesize,controversial,design,naming,unusedcode

php test.php