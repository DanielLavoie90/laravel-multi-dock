<?php


namespace Valet;


class Artisan
{
    private $containerName = 'artisan';
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
        $this->docker->run("run --rm --entrypoint \"php /var/www/html/$site/artisan\" $this->containerName $command");
    }
}