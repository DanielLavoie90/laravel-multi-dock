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
use Valet\Commands\ArtisanCommands;
use Valet\Commands\ComposerCommands;
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

$app->command('docker:up [-b|--build]', function ($build) {
    DockerCompose::up($build);
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
        DockerCompose::run("run $container bash");
    }
})
    ->descriptions("SSH into a container", [
        'container' => 'Name of the service of the container you want to ssh into. (Default=php)'
    ]);

$app->command('db:new [name] [-5|--mysql-5]', function ($input, $output, $mysql5, $name = null) {
    if (!$name) {
        if (!Prompt::yesNoQuestion($this, $input, $output, "Create database " . CALL_SITE . "? [Y/n]", true)) {
            $output->writeln('Ok bye!');
            return;
        }
    }
    $dbName = $name ?? CALL_SITE;

    Mysql::createDatabase($dbName, !$mysql5);
})
    ->descriptions("Create a new database.", [
        'name' => 'name of the database to create. (Default: folder where you called valet)'
    ]);

$app->command('db:user name password [-d|--database=] [-5|--mysql-5]', function ($name, $password, $mysql5, $database = null) {
    Mysql::createUser($name, $password, $database, !$mysql5);
})
    ->descriptions('Create a new database user.', [
        'name' => 'Name of the user to add.',
        'password' => 'Password for the user.',
        '--database' => 'Database to give access to the user. Use * to grant all. (Default=null)'
    ]);

$app->command('db:grant user password [-5|--mysql-5] [-d|--database=] [-g|--grant=] [-o|--without-grant-option]', function ($user, $password, $withoutGrantOption, $mysql5, $database='*', $grant = 'ALL') {
    Mysql::grantAccess($user, $password, $database, $grant, !$withoutGrantOption, !$mysql5);
})
    ->descriptions('Give a user access to a database.', [
        'user' => 'User you want to give access.',
        'password' => 'Password the user will use to access.',
        '--database' => 'The database you want to give access to. (use * for all)',
        '--grant' => 'The name of the grant you want the user to have.',
        '--without-grant-option' => "Don't give grant option to the user for that database."
    ])
    ->setAliases(['grant']);

$app->command('site:link [--name=] [--dist=] [--tld=] [-d|--subdomain] [-s|--secure]', function ($input, $output, $subdomain, $secure, $name = null, $dist = 'public', $tld = 'vcap.me') {
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
        '--tld' => 'Change the TLD for your application. (Default=vcap.me)'
    ])
    ->setAliases(['link']);

$app->command('site:secure [--name=] [--dist=] [--tld=] [-d|--subdomain]', function ($subdomain, $name = null, $dist = 'public', $tld = 'vcap.me') {
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
        '--tld' => 'Change the TLD for your application. (Default=vcap.me)'
    ])
    ->setAliases(['secure']);

ComposerCommands::register($app);

ArtisanCommands::register($app);

$app->command('npm com*', function ($com) {
    mustBeCallFromSite();
    $command = join(' ', $com);
    Npm::run($command);
})->descriptions("Run an npm command for the current site.", [
    'com' => 'Command you wish to run for npm. (i.e. "run watch")'
])
    ->setAliases(['n']);
/*
 * Run the application.
 */
$app->run();
