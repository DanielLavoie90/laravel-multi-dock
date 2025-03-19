<?php


namespace Valet;


class Drush
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
        $this->docker->run("exec $phpContainer php /var/www/html/$site/vendor/bin/drush $command");
    }
}
