<?php

/**
 * Load correct autoloader depending on install location.
 */
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

require_once __DIR__ . '/includes/facades.php';
require_once __DIR__ . '/includes/helpers.php';

use Illuminate\Container\Container;
use Silly\Application;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use function Valet\info;
use function Valet\isSite;
use function Valet\output;
use function Valet\table;
use function Valet\warning;

Container::setInstance(new Container());

$app = new Application('Valet for laravel docker', "1.0");

function mustBeCallFromSite()
{
    if (isSite()) {
        return CALL_SITE;
    }
    throw new InvalidArgumentException("Cannot find folder: `" . DOCKER_COMPOSE_PATH . '/src/' . CALL_SITE . "`");
}

$app->command('docker com', function ($com) {
    DockerCompose::run($com);
})
    ->descriptions('Run a command in docker-compose.', [
        'com' => 'Command for docker-compose to execute'
    ])
    ->setAliases(['d']);

$app->command('docker:up', function () {
    DockerCompose::up();
})
    ->descriptions("Run docker-compose up.")
    ->setAliases(['up']);

$app->command('docker:restart', function () {
    DockerCompose::restart();
})
    ->descriptions("Restart the docker.")
    ->setAliases(['restart']);

$app->command('docker:stop', function () {
    DockerCompose::stop();
})
    ->descriptions("Shutdown the docker.")
    ->setAliases(['stop']);

$app->command('ssh [container]', function ($container = 'php') {
    if (DockerCompose::checkService($container)) {
        DockerCompose::run("run $container sh");
    }
})
    ->descriptions("SSH into a container", [
        'container' => 'Name of the service of the container you want to ssh into. (Default=php)'
    ]);

$app->command('db:new [name]', function ($input, $output, $name = null) {
    if (!$name) {
        if (!Prompt::yesNoQuestion($this, $input, $output, "Create database " . CALL_SITE . "? [Y/n]", true)) {
            $output->writeln('Ok bye!');
            return;
        }
    }
    $dbName = $name ?? CALL_SITE;

    Mysql::createDatabase($dbName);
})
    ->descriptions("Create a new database.", [
        'name' => 'name of the database to create. (Default: folder where you called valet)'
    ]);

$app->command('db:user name password [--database=]', function ($name, $password, $database = null) {
    Mysql::createUser($name, $password, $database);
})
    ->descriptions('Create a new database user.', [
        'name' => 'Name of the user to add.',
        'password' => 'Password for the user.',
        '--database' => 'Database to give access to the user. Use * to grant all. (Default=null)'
    ]);

$app->command('db:grant user password database [--grant=] [-o|--without-grant-option]', function ($user, $password, $database, $withoutGrantOption, $grant = 'ALL') {
    Mysql::grantAccess($user, $password, $database, $grant, !$withoutGrantOption);
})
    ->descriptions('Give a user access to a database.', [
        'user' => 'User you want to give access.',
        'password' => 'Password the user will use to access.',
        'database' => 'The database you want to give access to. (use * for all)',
        '--grant' => 'The name of the grant you want the user to have.',
        '--without-grant-option' => "Don't give grant option to the user for that database."
    ])
    ->setAliases(['grant']);

$app->command('site:link [--name=] [--dist=] [--tld=] [--subdomain] [-s|--secure]', function ($input, $output, $subdomain, $secure, $name = null, $dist = 'public', $tld = 'test') {
    mustBeCallFromSite();
    Nginx::validateArguments($tld);

    $distPath = $dist == 'null' ? '' : $dist;
    $siteName = $name ?? CALL_SITE;
    $serverName = "$siteName.$tld";
    $serverNames = $subdomain ? "$serverName *.$serverName" : "$serverName";

    if (file_exists(NGINX_CONF_PATH . "/$serverName.conf")) {
        if (!Prompt::yesNoQuestion($this, $input, $output, "Configuration file `$serverName.conf` already exists. Overwrite? [y/N]")) {
            output('Keeping existing configuration.');
            return 0;
        }
    }

    Nginx::link($distPath, $serverName, $serverNames);
})
    ->descriptions("Link a new site to the nginx docker.", [
        '--name' => 'Specify the name of the site. (Default: App folder name)',
        '--dist' => 'Distribution folder inside your application. Put `null` for root path. (Default=public)',
        '--secure' => 'Secure the new site with SSL.',
        '--subdomain' => 'Apply a wildcard subdomain to the server name in Nginx conf.',
        '--tld' => 'Change the TLD for your application. (Default=test)'
    ])
    ->setAliases(['link']);

$app->command('site:secure [--name=] [--dist=] [--tld=] [--subdomain]', function ($subdomain, $name = null, $dist = 'public', $tld = 'test') {
    mustBeCallFromSite();
    Nginx::validateArguments($tld);

    $distPath = $dist == 'null' ? '' : $dist;
    $siteName = $name ?? CALL_SITE;
    $serverName = "$siteName.$tld";
    $serverNames = $subdomain ? "$serverName *.$serverName" : "$serverName";

    Nginx::secure($distPath, $serverName, $serverNames);
})
    ->descriptions("Secure a site with SSL.", [
        '--name' => 'Specify the name of the site. (Default: App folder name)',
        '--dist' => 'Distribution folder inside your application. Put `null` for root path. (Default=public)',
        '--subdomain' => 'Apply a wildcard subdomain to the server name in Nginx conf.',
        '--tld' => 'Change the TLD for your application. (Default=test)'
    ])
    ->setAliases(['secure']);

$app->command('composer com', function ($com) {
    mustBeCallFromSite();
    Composer::run($com);
})
    ->descriptions("Run composer dump-autoload for the current site.")
    ->setAliases(['c']);

$app->command('composer:du', function () {
    mustBeCallFromSite();
    Composer::run('dump-autoload');
})
    ->descriptions("Run composer dump-autoload for the current site.")
    ->setAliases(['du']);

$app->command('composer:install', function () {
    mustBeCallFromSite();
    Composer::run('install');
})
    ->descriptions("Run composer install for the current site.")
    ->setAliases(['ci']);

$app->command('composer:update', function () {
    mustBeCallFromSite();
    Composer::run('update');
})
    ->descriptions("Run composer update for the current site.")
    ->setAliases(['cu']);

$app->command('artisan com', function ($com) {
    Artisan::run($com);
})
    ->descriptions("Run an artisan command for the current site.", [
        'com' => 'Command you wish to run for artisan. (i.e. migrate)'
    ])
    ->setAliases(['art']);

/*
 * Run the application.
 */
$app->run();
