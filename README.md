install:
```
composer install
```

config: (you can add more)
```
cp src/config/config.php.dist src/config/config.dev.php
cp src/config/config.php.dist src/config/config.test.php
cp src/config/config.php.dist src/config/config.live.php
```
Set enviroment in `src/config/env.php` to one of the following: `dev`, `test`, `live`

Set the proper values in files: 

    `src/config/config.dev.php` <- for local developement
    
    `src/config/config.test.php` <- for testing
    
    `src/config/config.live.php` <- for live
    

DB:
see in `webapp_test.sql`, impoert db and/or rename it as it set up in config (see above)

tail monitoring: (path maybe different on your local environment)
```
tail -f /var/log/apache2/error.log -f /var/www/webapp/logs/*.log -f /var/log/apache2/access.log
```

tests:
```
./test.sh
```
