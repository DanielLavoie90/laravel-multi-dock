<?php

namespace Valet;

class Mysql
{
    public $docker;
    private $containerName = 'mysql8';

    public function __construct(DockerCompose $docker)
    {
        $this->docker = $docker;
    }

    public function useMysql8($version = '')
    {
        $this->containerName = "mysql8$version";
    }

    public function run($command, $user='homestead', $password='secret')
    {
        $this->docker->run("exec -T $this->containerName mysql -u$user -p$password -e \\\"$command\\\"");
    }

    public function createDatabase($name, $mysql='')
    {
        if($mysql) {
            $this->useMysql8($mysql);
        }
        $command = "CREATE DATABASE IF NOT EXISTS $name DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci";
        $this->run($command);
    }

    public function createUser($name, $password, $database=null, $mysql='')
    {
        if($mysql) {
            $this->useMysql8($mysql);
        }
        $identifiedKW = $mysql == '0' ? 'identified with mysql_native_password by' : 'IDENTIFIED BY';
        $command = "CREATE USER IF NOT EXISTS '$name'@'localhost' $identifiedKW '$password';";
        $this->run($command, 'root', 'secret');
        if($database) {
            $this->grantAccess($name, $password, $database);
        }
    }

    public function alterUser($name, $password, $database=null, $mysql='')
    {
        if($mysql) {
            $this->useMysql8($mysql);
        }
        $identifiedKW = $mysql == '0' ? 'identified with mysql_native_password by' : 'IDENTIFIED BY';
        $command = "ALTER USER '$name'@'localhost' $identifiedKW '$password';";
        $this->run($command, 'root', 'secret');
        if($database) {
            $this->grantAccess($name, $password, $database);
        }
    }

    public function grantAccess($name, $password, $database='*', $grant='ALL', $withGrantOption=true, $mysql='')
    {
        if($mysql) {
            $this->useMysql8($mysql);
        }
//        $identifiedKW = $mysql == '0' ? 'identified with mysql_native_password by' : 'IDENTIFIED BY';
        $command = "GRANT $grant ON $database.* TO '$name'@'localhost'" .
            ($withGrantOption ? " WITH GRANT OPTION;" : ";");
        $this->run($command, 'root', 'secret');

        $command = "GRANT $grant ON $database.* TO '$name'@'%'" .
            ($withGrantOption ? " WITH GRANT OPTION;" : ";");
        $this->run($command, 'root', 'secret');
    }
}
