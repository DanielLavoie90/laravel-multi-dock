<?php

namespace Valet;

class Npm
{
    private $containerName = 'npm';
    /**
     * @var DockerCompose
     */
    private DockerCompose $docker;

    public function __construct(DockerCompose $docker)
    {
        $this->docker = $docker;
    }

    public function run($command)
    {
        $site = CALL_SITE;
        $this->docker->run("run --rm --w /var/www/html/$site $this->containerName $command");
    }
}