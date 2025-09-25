<?php

namespace Valet;

class DockerCompose
{
    public $cli;

    public function __construct(CommandLine $cli)
    {
        $this->cli = $cli;
    }

    public function run($command, $passthru=true, $onError=null)
    {
        $dockerCommand = "cd " . DOCKER_COMPOSE_PATH . " && script -q -c \"docker-compose $command\" /dev/null";
        if($passthru) {
            output('<info>Passthru: '.$dockerCommand.'</info>');
            $this->cli->passthru($dockerCommand);
            return true;
        } else {
            output('<info>'.$dockerCommand.'</info>');
            return $this->cli->run($dockerCommand, $onError);
        }
    }

    public function up($build = false)
    {
        $command = "up -d" . ($build ? " --build" : "");
        $this->run($command);
    }

    public function stop()
    {
        $this->run("stop");
    }

    public function restart($container = null)
    {
        $this->run("restart $container");
    }

    public function services()
    {
        $this->run("ps --services");
    }

    public function checkService($service)
    {
        $result = $this->run("ps $service", false, function ($errorCode, $output){
            warning('Invalid service name. Valid services are:');
            $this->services();
            return false;
        });
        return !str_starts_with($result, 'No such service');
    }
}
