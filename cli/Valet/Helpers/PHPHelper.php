<?php

namespace Valet\Helpers;

class PHPHelper
{
    const DefaultPHP = '8';

    public static function getDefaultPhpContainer(): string
    {
        switch ($_SERVER['PHP_VERSION']) {
            case '8.0':
                return '8';
            case '7.4':
            case '7.3':
                return '74';
        }
        return self::DefaultPHP;
    }
}
