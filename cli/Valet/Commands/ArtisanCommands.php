<?php

namespace Valet\Commands;

use Artisan;
use Silly\Application;

class ArtisanCommands implements Commands
{
    public static function register(Application $app)
    {
        $app->command('artisan com*', function ($com) {
            mustBeCallFromSite();
            $command = join(' ', $com);
            Artisan::run($command);
        })
            ->descriptions("Run an artisan command for the current site.", [
                'com' => 'Command you wish to run for artisan. (i.e. migrate)'
            ])
            ->setAliases(['art']);

        $app->command('artisan:tinker', function () {
            mustBeCallFromSite();
            Artisan::run('tinker');
        })->setAliases(['tinker']);

        $app->command('artisan:migrate [-f|--fresh] [-s|--seed]', function ($fresh, $seed) {
            mustBeCallFromSite();
            $command = 'migrate';
            if ($fresh) {
                $command .= ':fresh';
                if ($seed) {
                    $command .= ' --seed';
                }
            }
            Artisan::run($command);
        })
            ->descriptions("Run artisan migrate command for the current site.", [
                '--fresh' => 'Run a fresh migration.',
                '--seed'  => 'Seed the database after fresh migration'
            ])
            ->setAliases(['mig']);

        $app->command('artisan:seed [-c|--class=]', function ($class = null) {
            mustBeCallFromSite();
            $command = 'db:seed';
            if($class){
                $command .= " --class=$class";
            }
            Artisan::run($command);
        })
            ->descriptions("Run artisan migrate command for the current site.", [
                '--class' => 'Run a specific seeder class'
            ])
            ->setAliases(['seed']);;
    }
}