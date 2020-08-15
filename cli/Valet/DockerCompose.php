<?php

namespace Valet;

class DockerCompose
{
    public $cli;

    public function __construct(CommandLine $cli)
    {
        $this->cli = $cli;
    }

    public function run($command)
    {
        return $this->cli->run("cd " . DOCKER_COMPOSE_PATH . " && $command");
    }

    public function restart($container)
    {
        $this->run("docker-compose restart $container");
    }
}