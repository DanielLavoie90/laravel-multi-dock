<?php


namespace Valet;


class Artisan
{
    private $containerName = 'php';
    /**
     * @var DockerCompose
     */
    private DockerCompose $docker;

    public function __construct(DockerCompose $docker)
    {
        $this->docker = $docker;
    }

    public function run($command, $phpContainer = 'php8')
    {
        $site = CALL_SITE;
        $this->docker->run("run --rm --entrypoint \"$phpContainer /var/www/html/$site/artisan\" $this->containerName $command");
    }
}
