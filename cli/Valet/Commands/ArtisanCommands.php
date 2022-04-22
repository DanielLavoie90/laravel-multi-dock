<?php

namespace Valet\Commands;

use Artisan;
use Silly\Application;

class ArtisanCommands implements Commands
{
    public static function register(Application $app)
    {
        $app->command('artisan com* [--php=]', function ($com, $php = '8') {
            mustBeCallFromSite();
            $command = join(' ', $com);
            Artisan::run($command, "php$php");
        })
            ->descriptions("Run an artisan command for the current site.", [
                'com' => 'Command you wish to run for artisan. (i.e. migrate)'
            ])
            ->setAliases(['art']);

        $app->command('artisan:tinker [--php=]', function ($php = '8') {
            mustBeCallFromSite();
            Artisan::run('tinker', "php$php");
        })->setAliases(['tinker']);

        $app->command('artisan:migrate [-f|--fresh] [-s|--seed] [--php=]', function ($fresh, $seed, $php = '8') {
            mustBeCallFromSite();
            $command = 'migrate';
            if ($fresh) {
                $command .= ':fresh';
                if ($seed) {
                    $command .= ' --seed';
                }
            }
            Artisan::run($command, "php$php");
        })
            ->descriptions("Run artisan migrate command for the current site.", [
                '--fresh' => 'Run a fresh migration.',
                '--seed'  => 'Seed the database after fresh migration'
            ])
            ->setAliases(['mig']);

        $app->command('artisan:seed [-c|--class=] [--php=]', function ($class = null, $php = '8') {
            mustBeCallFromSite();
            $command = 'db:seed';
            if($class){
                $command .= " --class=$class";
            }
            Artisan::run($command, "php$php");
        })
            ->descriptions("Run artisan migrate command for the current site.", [
                '--class' => 'Run a specific seeder class'
            ])
            ->setAliases(['seed']);;
    }
}
