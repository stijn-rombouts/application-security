# MPI-001 Employment-Interest-Tool

##### First run:
- `cd code`
- `composer install`
- `npm install`
- `npm audit fix`
- `npm run build`
- `cp .env.example .env`
- `php artisan key:generate`
- `php artisan migrate --force` Migrate without confirmation
- `php artisan db:seed`
- `composer run dev`
- All commands: `cd code && composer install && npm install && npm audit fix && npm run build && cp .env.example .env && php artisan key:generate && php artisan migrate --force && php artisan db:seed && composer run dev`

##### From then on:
- `composer run dev`

##### Manually recreate the database
- `rm database/database.sqlite` Remove database file
- `php artisan migrate --force` Migrate without confirmation
- `php artisan db:seed`
- All commands: `rm database/database.sqlite && php artisan migrate --force && php artisan db:seed`

### Linux:
#### Fedora packages
- dnf install composer
- dnf install php-pdo
- dnf install php-mysqlnd

### when using Sail

all `php` commands -> `sail` (make sure that sail is running)

example: `sail artisan migrate --force`