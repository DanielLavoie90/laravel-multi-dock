<?php

/**
 * Load correct autoloader depending on install location.
 */
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once __DIR__.'/../vendor/autoload.php';
} else {
    require_once __DIR__.'/../../../autoload.php';
}

require_once __DIR__.'/includes/facades.php';
require_once __DIR__.'/includes/helpers.php';

use Illuminate\Container\Container;
use Silly\Application;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use function Valet\info;
use function Valet\output;
use function Valet\table;
use function Valet\warning;

Container::setInstance(new Container());

$app = new Application('Valet for laravel docker', "1.0");

$app->command('db [name]', function ($input, $output, $name = null){
    if(!$name){
        $helper = $this->getHelperSet()->get('question');
        $question = new ConfirmationQuestion("Create database " . CALL_SITE . "? [Y/n]", true);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Ok bye!');
            return;
        }
    }
    $dbName = $name ?? CALL_SITE;
    $command ="CREATE DATABASE IF NOT EXISTS $dbName DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci";

    $output = null;
    $result = null;

    exec("docker-compose exec -T mysql mysql -uhomestead -psecret -e \"$command\"", $output, $result);

    if($result != 0){
        warning("Something went wrong!\n");
        output($output);
        return;
    }

    output("Database $dbName created with success!");
})->descriptions("Create a new database in the mysql docker.", [
    'name' => 'name of the database to create. (Default: folder where you called valet)'
]);

$app->command('link [--name=] [--dist=] [--tld=] [-s|--subdomain]', function ($input, $output, $subdomain, $name = null, $dist = 'public', $tld = 'test'){
    Nginx::validateArguments($tld);

    $distPath = $dist == 'null' ? '' : $dist;
    $siteName = $name ?? CALL_SITE;
    $serverName = "$siteName.$tld";
    $serverNames = $subdomain ? "$serverName *.$serverName" : "$serverName";

    if(file_exists(NGINX_CONF_PATH . "/$serverName.conf")){
        if (!Prompt::yesNoQuestion($this, $input, $output, "Configuration file `$serverName.conf` already exists. Overwrite? [y/N]")) {
            output('Keeping existing configuration.');
            return 0;
        }
    }

    Nginx::link($distPath, $serverName, $serverNames);
})->descriptions("Link a new site to the nginx docker.", [
    '--name' => 'Specify the name of the site. (Default: App folder name)',
    '--dist' => 'Distribution folder inside your application. Put `null` for root path. (Default=public)',
    '--subdomain' => 'Apply a wildcard subdomain to the server name in Nginx conf.',
    '--tld' => 'Change the TLD for your application. (Default=test)'
]);

$app->command('test', function(){
//    $output = $result = null;
//    exec("cd " . DOCKER_COMPOSE_PATH . " && docker-compose restart site", $output, $result);
//
//    output($result);
    DockerCompose::restart('site');
    output(get_class($this));
});

/*
 * Run the application.
 */
$app->run();
