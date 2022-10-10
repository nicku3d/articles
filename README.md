## Simple service in which one can create, edit and view articles

**Project specs:**
- PHP VERSION: 8.1.11
- MYSQL VERSION: 8.0.30
- COMPOSER VERSION: 2.2.6

**Before using:**

1. composer install
1. create config file named "db-config.php" in ./App/Config/ directory with your database credentials

      EXAMPLE db-config.php:
      
      ```php
      <?php
      return [
       'host' => '172.20.0.2',
       'user' => 'admin',
       'password' => 'admin',
       'db' => 'article',
      ];
      ```
1. Start apache and database with command:

        docker compose up
1. Create new schema e.g 'article' and import database structure from file articles_db_dump.sql
1. If you want you can run PHP unit tests from docker container:

        docker exec -it php-apache ./vendor/phpunit/phpunit/phpunit ./tests