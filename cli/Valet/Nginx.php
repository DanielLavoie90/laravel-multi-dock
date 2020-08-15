<?php

namespace Valet;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;

class Nginx
{
    public $cli;
    public $files;
    private $containerName = 'site';

    public function __construct(CommandLine $cli, Filesystem $files)
    {
        $this->cli = $cli;
        $this->files = $files;
    }

    public function validateArguments($tld)
    {
        if(!$tld || strlen($tld) == 0){
            warning("Argument `tld` must at least contain one character.");
            throw new InvalidArgumentException("Argument `tld` must at least contain one character.");
        }
        if(!is_dir(DOCKER_COMPOSE_PATH . '/src/' . CALL_SITE)){
            throw new InvalidArgumentException("Cannot find folder: `" . DOCKER_COMPOSE_PATH . '/src/' . CALL_SITE . "`");
        }
    }

    //TODO Secure
    public function link($distPath, $serverName, $serverNames, $secure = false)
    {
        $stub = $this->files->get(__DIR__ . '/stubs/nginx.laravel.stub');
        if(!$stub){
            throw new RuntimeException("Cannot read `nginx.laravel.stub` file!");
        }

        $conf = str_replace(
            ['{SERVER_NAME}', '{APP_DIR}', '{APP_DIST}'],
            [$serverNames, CALL_SITE, $distPath],
            $stub);

        if(!$this->files->put(NGINX_CONF_PATH . "/$serverName.conf", $conf)){
            throw new RuntimeException("Could not save conf file to: {DockerPath}/nginx/$serverName.conf");
        }

        output("Configuration $serverName.conf saved to nginx with success.");

        //TODO use DockerCompose
        DockerCompose::restart($this->containerName);
    }

}