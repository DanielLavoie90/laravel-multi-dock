<?php


namespace Valet;


use Symfony\Component\Console\Exception\InvalidArgumentException;

class Composer
{
    private $containerName = 'composer';
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
        $this->docker->run("run --rm $this->containerName --working-dir=/var/www/html/$site $command");
    }

}