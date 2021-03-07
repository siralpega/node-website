# Node Dashboard
By siralpega

## Description
A website used to display data from an API about statistics of a node (a server). 
This was created as a __quick__ project for server management, but was later turned into a portfolio showcase.
Using Vue.js, Laravel, and Firebase.

## Documents
Documentation of the project is found in the .design folder

## Requirements
A server (or local env) running:
- PHP [(for more)](https://laravel.com/docs/8.x/deployment#server-requirements)
- [Composer](https://getcomposer.org/)
- Node.js
- An API endpoint
- A firebase database

## Usage
In project folder, run the following commands:
- composer install --optimize-autoloader --no-dev
- cp .env.example .env
- php artisan key:generate
- php artisan config:cache
- php artisan route:cache
- php artisan view:cache
- npm install --production

You also need to add a cron task to the server: * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
(If you are running this locally, you can just run the command: php artisan schedule:work)

You also need to add your firebase database url to the .env file (public read/write), as well as the API endpoint. Examples of how these json files need to look is in the .design folder.

Finally, run the following command to start the server: php artisan serv

