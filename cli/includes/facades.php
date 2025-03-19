<?php

use Illuminate\Container\Container;

class Facade
{
    /**
     * The key for the binding in the container.
     *
     * @return string
     */
    public static function containerKey()
    {
        return 'Valet\\'.basename(str_replace('\\', '/', get_called_class()));
    }

    /**
     * Call a non-static method on the facade.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $resolvedInstance = Container::getInstance()->make(static::containerKey());

        return call_user_func_array([$resolvedInstance, $method], $parameters);
    }
}

class Artisan extends Facade
{
}

class CommandLine extends Facade
{
}

class Composer extends Facade
{
}

class Drush extends Facade
{
}

class DockerCompose extends Facade
{
}

class Filesystem extends Facade
{
}

class Mysql extends Facade
{
}

class Nginx extends Facade
{
}

class Npm extends Facade
{
}

class Prompt extends Facade
{
}
