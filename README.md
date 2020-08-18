# Laravel Multi Hosting Docker

Based on:
- Valet cli for windows [cretueusebiu/valet-windows](https://github.com/cretueusebiu/valet-windows)
- Single app docker [shincoder/homestead-docker](https://github.com/jaouadk/homestead-docker)

## Config

You should add /bin to your PATH environment variable. This will enable you to access the cli from anywhere.

Out of the box you can simply put your projects into the src folder and start using the cli to link them.

If you want, you can change the docker-compose.yml to your need.

## Missing

I didn't create NPM commands yet. Next on my list.

## CLI

A lot of the commands needs to be run from inside your app root directory so that the cli can know which app you want to run the command into.
 - Composer commands
 - Artisan commands
 - Site commands
 
Just run valet list to see all available command for the cli.

```shell script
Valet for laravel docker 1.0

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  artisan
  composer          [c] Run composer dump-autoload for the current site.
  docker            [d] Run a command in docker-compose.
  help              Displays help for a command
  list              Lists commands
  ssh               SSH into a container
 composer
  composer:du       [du] Run composer dump-autoload for the current site.
  composer:install  [ci] Run composer install for the current site.
  composer:update   [cu] Run composer update for the current site.
 db
  db:grant          [grant] Give a user access to a database.
  db:new            Create a new database.
  db:user           Create a new database user.
 docker
  docker:restart    [restart] Restart the docker.
  docker:stop       [stop] Shutdown the docker.
  docker:up         [up] Run docker-compose up.
 site
  site:link         [link] Link a new site to the nginx docker.
  site:secure       [secure] Secure a site with SSL.

```

### Site

Probably the most important namespace of the commands. They create the nginx conf file used to serve your app.

You could specify if you want it to be secure, use wildcard subdomain, use a different tld (default=test) or use a 
different name than your app root folder name (i.e. /src/My-App would give My-App.test by default).

Certificates are generated in the /certificates folder and conf files in the /nginx folder. Both are synced with the `site` 
container. You can modify the conf as you want but keep in mind that if you run the link/secure command for that site, 
your conf will be overwritten.


#### Examples 
Runned from /src/My-Super-App
```shell script
valet link --name=mysuperapp --dist=distrib --tld=local --subdomain -s
```
Would generate a conf file with:
 - server_name mysuperapp.local *.mysuperapp.local
 - SSL enabled
 - root directory at /var/www/html/My-Super-App/distrib

### Artisan

Use "" for command with space. It should be run in your app root directory (i.e. /src/MyApp).

#### Examples
```shell script
valet artisan migrate
valet artisan "migrate:fresh --seed"
```

### Composer

Use "" for command with space. It should be run in your app root directory (i.e. /src/MyApp).

Some shortcuts exist for dump-autoload(du), install(ci) and update(cu).

#### Examples
```shell script
valet composer du       # Composer dump-autoload
valet du                # Same as previous
```

### Docker

Use "" for command with space. It can be run from anywhere on your computer.

#### Examples
```shell script
valet docker "restart php"  # Restart one service
valet docker:restart php    # Same as previous
valer docker:restart        # Restart all services
```

### SSH

SSH into a container. The name represents the service name of the container.

Run this command to get the name of the services or check the docker-composer.yml
```shell script
valet docker "ps --services"
```

#### Examples
```shell script
valet ssh site
```

### Database

I've created some commands to create a new database, a new user or add grant to a user.

#### Examples
```shell script
valet db:new app_db       # Create an app_db schema
valet db:new              # Create a schema name after the directory your in now
valet db:user user_abcd P@ssword1234 --database=this_db_only # Create user user_abcd with only access to this_db_only
valet db:user master M@st3r --database=* # Create user master and give access to all database with ALL grant
valet db:grant user_abcd P@ssword1234 this_db_only --grant=ALL -o # Grant user ALL grant on database this_db_only without grant option
```
