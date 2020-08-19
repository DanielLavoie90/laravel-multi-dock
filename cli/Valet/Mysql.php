<?php

namespace Valet;

class Mysql
{
    public $docker;
    private $containerName = 'mysql';

    public function __construct(DockerCompose $docker)
    {
        $this->docker = $docker;
    }

    public function run($command, $user='homestead', $password='secret')
    {
        $this->docker->run("exec -T $this->containerName mysql -u$user -p$password -e \"$command\"");
    }

    public function createDatabase($name)
    {
        $command = "CREATE DATABASE IF NOT EXISTS $name DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci";
        $this->run($command);
    }

    public function createUser($name, $password, $database=null)
    {
        $command = "CREATE USER IF NOT EXISTS '$name'@'localhost' IDENTIFIED BY '$password';";
        $this->run($command, 'root', 'secret');
        if($database) {
            $this->grantAccess($name, $password, $database);
        }
    }

    public function grantAccess($name, $password, $database='*', $grant='ALL', $withGrantOption=true)
    {
        $command = "GRANT $grant ON $database.* TO '$name'@'localhost' IDENTIFIED BY '$password'" .
            ($withGrantOption ? "WITH GRANT OPTION;" : ";");
        $this->run($command, 'root', 'secret');

        $command = "GRANT $grant ON $database.* TO '$name'@'%' IDENTIFIED BY '$password'" .
            ($withGrantOption ? "WITH GRANT OPTION;" : ";");
        $this->run($command, 'root', 'secret');
    }
}