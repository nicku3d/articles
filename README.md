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
       'host' => 'db',
       'user' => 'admin',
       'password' => 'admin',
       'db' => 'article',
      ];
      ```
1. Start apache and database with command:

        docker compose up
1. create new schema e.g 'article' and import database structure from file articles_db_dump.sql