# Digital Wallet Example App
Tech stack:
> PHP 8.4+, Laravel 12, VueJS 3.5, Tailwind CSS 4.1, Sqlite/Mysql 8, Pusher


### Important notes
- This implementation is done according to [task.md](task.md)
- It focuses only on backend and frontend implementation of digital wallet, it has only basic authorization
- DDD and TDD applied during development
- **NO** enterprise features were implemented, such as: CQRS read models, multi-currency, partitioning, etc.

## Running this project
There are two possible ways to run this project:
1. Machine with php, composer and npm installed, SQLite as DB
2. Machine with docker, php, composer and npm installed, Mysql DB in docker

### 1. Running with SQLite
1.1. Copy env file
```bash
cp .env.example .env
```
1.2. After that you need to add Pusher credentials to .env file, can be obtained at pusher.com.

1.3. 
```bash
composer install
npm install
npm run build
php artisan migrate
composer dev
```

1.4. After that it should be accessible via http://127.0.0.1:8000 by default.

### 2. Running with Mysql in Docker
2.1. Copy env file
```bash
cp .env.example.docker .env
```
2.2. After that you need to add Pusher credentials to .env file, can be obtained at pusher.com.
2.3. Run scripts
```bash
composer install
npm install
npm run build
sh docker-start.sh
php artisan migrate
composer dev
```
2.4. After that it should be accessible via http://127.0.0.1:8000 by default.
2.5. Docker can be stopped by running
```bash
sh docker-stop.sh
```
2.6. You can clean all docker-related things by running
```bash
sh docker-clean.sh
```
