<?php

namespace Valet\Commands;

use Artisan;
use Composer;
use Silly\Application;

class ComposerCommands implements Commands
{
    public static function register(Application $app)
    {
        $app->command('composer:run com*', function ($com) {
            mustBeCallFromSite();
            $command = join(' ', $com);
            Composer::run($command);
        })
            ->descriptions("Run a composer command.", [
                'com' => 'The command to be execute with composer.'
            ])
            ->setAliases(['composer', 'c']);

        $app->command('composer:du', function () {
            mustBeCallFromSite();
            Composer::run('dump-autoload');
        })
            ->descriptions("Run composer dump-autoload for the current site.")
            ->setAliases(['du']);

        $app->command('composer:install', function () {
            mustBeCallFromSite();
            Composer::run('install --ignore-platform-reqs');
        })
            ->descriptions("Run composer install for the current site.")
            ->setAliases(['ci']);

        $app->command('composer:require package', function ($package) {
            mustBeCallFromSite();
            Composer::run("require $package --ignore-platform-reqs");
        })
            ->descriptions("Run composer require for the current site.", [
                'package' => 'The package to install.'
            ])
            ->setAliases(['creq']);

        $app->command('composer:update', function () {
            mustBeCallFromSite();
            Composer::run('update --ignore-platform-reqs');
        })
            ->descriptions("Run composer update for the current site.")
            ->setAliases(['cu']);
    }
}