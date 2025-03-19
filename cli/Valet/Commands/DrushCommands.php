<?php

namespace Valet\Commands;

use Silly\Application;
use Drush;
use Valet\Helpers\PHPHelper;

class DrushCommands implements Commands
{
    public static function register(Application $app)
    {
        $app->command('drush com* [--php=]', function ($com, $php = null) {
            $php = $php ?? PHPHelper::getDefaultPhpContainer();
            mustBeCallFromSite();
            $command = join(' ', $com);
            Drush::run($command, "php$php");
        })
            ->descriptions("Run an artisan command for the current site.", [
                'com' => 'Command you wish to run for artisan. (i.e. migrate)'
            ])
            ->setAliases(['dr']);
    }
}
