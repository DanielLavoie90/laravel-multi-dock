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

    public function run($command, $phpContainer = 'php8')
    {
        $site = CALL_SITE;
        $this->docker->run("exec -it -w /var/www/html/$site $phpContainer php artisan $command");
    }
}
