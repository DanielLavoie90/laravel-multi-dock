<?php

foreach ($argv as $arg) {
    if(strpos($arg, '--') === false){
        continue;
    }
    $arg = substr($arg, 2);
    $e=explode("=",$arg);
    if(count($e)==2)
        $arguments[$e[0]]=$e[1];
    else
        $arguments[$e[0]]=0;
}

$dbName = $arguments['name'];
$command ="CREATE DATABASE IF NOT EXISTS $dbName DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci";

shell_exec("docker-compose exec mysql mysql -uhomestead -psecret -e \"$command\"");