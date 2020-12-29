<?php

namespace Valet;

use phpseclib\Crypt\RSA;
use phpseclib\File\X509;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;

class Nginx
{
    public $cli;
    public $files;
    private $docker;
    private $containerName = 'site';


    public function __construct(CommandLine $cli, Filesystem $files, DockerCompose $docker)
    {
        $this->cli = $cli;
        $this->files = $files;
        $this->docker = $docker;
    }



    public function validateArguments($tld)
    {
        if(!$tld || strlen($tld) == 0){
            warning("Argument `tld` must at least contain one character.");
            throw new InvalidArgumentException("Argument `tld` must at least contain one character.");
        }
        if(!isSite()){
            throw new InvalidArgumentException("Cannot find folder: `" . DOCKER_COMPOSE_PATH . '/src/' . CALL_SITE . "`");
        }
    }

    //TODO Secure
    public function link($dist, $baseName, $serverNames, $secure = false)
    {
        if($secure){
            $this->secure($dist, $baseName, $serverNames);
            return;
        }

        $conf = $this->buildUnsecureConf($baseName, $serverNames, CALL_SITE, $dist);

        if(!$this->files->put(NGINX_CONF_PATH . "$baseName.conf", $conf)){
            throw new RuntimeException("Could not save conf file to: {DockerPath}/nginx/$baseName.conf");
        }

        output("Configuration $baseName.conf saved to nginx with success.");

        $this->docker->restart($this->containerName);
    }

    public function buildUnsecureConf($baseName, $serverNames, $directory, $dist)
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/nginx.laravel.stub');
        if(!$stub){
            throw new RuntimeException("Cannot read `nginx.laravel.stub` file!");
        }

        return str_replace(
            ['{SERVER_NAME}', '{APP_DIR}', '{APP_DIST}'],
            [$serverNames, "$directory", $dist],
            $stub);
    }

    public function secure($dist, $baseName, $serverNames)
    {
        $this->files->ensureDirExists($this->certificatesPath(), user());
        $this->createCertificate($baseName);
        $conf = $this->buildSecureConf($baseName, $serverNames, CALL_SITE, $dist);

        if(!$this->files->put(NGINX_CONF_PATH . "$baseName.conf", $conf)){
            throw new RuntimeException("Could not save conf file to: {DockerPath}/nginx/$baseName.conf");
        }

        $this->docker->restart($this->containerName);
    }

    public function buildSecureConf($baseName, $serverNames, $directory, $dist)
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/nginx.secure.laravel.stub');
        if(!$stub){
            throw new RuntimeException("Cannot read `nginx.secure.laravel.stub` file!");
        }
        $path = $this->certificatesDockerPath();

        return str_replace(
            ['{SERVER_NAME}', '{APP_DIR}', '{APP_DIST}', '{SITE_CERT}', '{SITE_KEY}'],
            [$serverNames, $directory, "/$dist", "$path/$baseName.crt", "$path/$baseName.key"],
            $stub);
    }

    public function createCertificate($serverName)
    {
        $keyPath = $this->certificatesPath().'/'.$serverName.'.key';
        $csrPath = $this->certificatesPath().'/'.$serverName.'.csr';
        $crtPath = $this->certificatesPath().'/'.$serverName.'.crt';

        $this->createPrivateKey($keyPath);
        $this->createSigningRequest($serverName, $keyPath, $csrPath);
        $this->createSignedCertificate($keyPath, $csrPath, $crtPath);

        $this->trustCertificate($crtPath);
    }

    public function createPrivateKey($keyPath)
    {
        $key = (new RSA())->createKey(2048);

        $this->files->putAsUser($keyPath, $key['privatekey']);
    }

    public function createSigningRequest($serverName, $keyPath, $csrPath)
    {
        $privKey = new RSA();
        $privKey->loadKey($this->files->get($keyPath));

        $x509 = new X509();
        $x509->setPrivateKey($privKey);
        $x509->setDNProp('commonname', $serverName);

        $x509->loadCSR($x509->saveCSR($x509->signCSR()));

        $x509->setExtension('id-ce-subjectAltName', [
            ['dNSName' => $serverName],
            ['dNSName' => "*.$serverName"],
        ]);

        $csr = $x509->saveCSR($x509->signCSR());

        $this->files->putAsUser($csrPath, $csr);
    }

    public function createSignedCertificate($keyPath, $csrPath, $crtPath)
    {
        $privKey = new RSA();
        $privKey->loadKey($this->files->get($keyPath));

        $subject = new X509();
        $subject->loadCSR($this->files->get($csrPath));

        $issuer = new X509();
        $issuer->setPrivateKey($privKey);
        $issuer->setDN($subject->getDN());

        $x509 = new X509();
        $x509->makeCA();
        $x509->setStartDate('-1 day');

        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');
        $certificate = $x509->saveX509($result);

        $this->files->putAsUser($crtPath, $certificate);
    }

    public function trustCertificate($crtPath)
    {
        output($this->cli->run(sprintf('cmd "/C certutil -addstore "Root" "%s""', $crtPath)));
    }

    public function certificatesPath()
    {
        return DOCKER_COMPOSE_PATH.'/data/certificates';
    }

    public function certificatesDockerPath()
    {
        return '/etc/nginx/ssl';
    }

}
