<?php


namespace Valet;


use Symfony\Component\Console\Exception\InvalidArgumentException;

class Artisan
{
    /**
     * @var DockerCompose
     */
    private DockerCompose $docker;

    public function __construct(DockerCompose $docker)
    {
        $this->docker = $docker;
    }

    public function run($command, $phpContainer = 'php')
    {
        $site = CALL_SITE;
        $this->docker->run("run --rm --entrypoint \"php /var/www/html/$site/artisan\" $phpContainer $command");
    }
}
