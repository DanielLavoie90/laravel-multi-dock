<?php


namespace Valet\Commands;

use Silly\Application;

interface Commands
{
    public static function register(Application $app);
}