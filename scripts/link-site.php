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

if(!isset($arguments['folder'])){
    exit("Missing site folder.");
}
$site = $arguments['folder'];
$dist = $arguments['dist'] ?? "public";

$isSubDomain = false;
if(isset($arguments['name'])){
    $dotCount = substr_count($arguments['name'], '.');
    if ($dotCount >= 1){
        $isSubDomain = true;
    }
    if($dotCount <= 1){
        $serverName = $arguments['name'] . ".test";
    } else {
        $serverName = $arguments['name'];
    }
} else {
    $serverName = "$site.test";
}
$serverNames = $isSubDomain ? $serverName : "$serverName *.$serverName";


if(!is_dir(realpath(__DIR__ . "/../src/$site"))){
    exit("$site not found in /src folder.");
}
if(file_exists(realpath(__DIR__ . "/../nginx/$serverName.conf"))){
    exit("$site already exists.");
}

$stub = file_get_contents(__DIR__ . '/nginx.stub');

$conf = str_replace(
    ['{SERVER_NAME}', '{APP_DIR}', '{APP_DIST}'],
    [$serverNames, $site, $dist],
    $stub);


file_put_contents(__DIR__ . "/../nginx/$serverName.conf", $conf);

print_r("Site $site linked at $serverName");
exit(0);