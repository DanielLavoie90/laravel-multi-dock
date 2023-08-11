<?php

namespace Valet\Helpers;

class PHPHelper
{
    const DefaultPHP = '';

    public static function getDefaultPhpContainer(): string
    {
        switch ($_SERVER['PHP_VERSION']) {
            case '8.0':
                return '8';
            case '7.4':
                return '';
            case '7.3':
                return '73';
        }
        return self::DefaultPHP;
    }
}
