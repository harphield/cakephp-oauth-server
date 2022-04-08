<?php
declare(strict_types=1);

namespace OAuthServer\Test\TestCase\Bridge;

use Cake\TestSuite\TestCase;
use League\OAuth2\Server\ResourceServer;
use OAuthServer\Bridge\ResourceServerFactory;
use OAuthServer\Plugin as OAuthServerPlugin;

class ResourceServerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $OAuthServerPlugin = new OAuthServerPlugin();

        $publicKeyPath = $OAuthServerPlugin->getPath() . 'tests/Fixture/test-pub.pem';
        chmod($publicKeyPath, 0600);

        $factory = new ResourceServerFactory($publicKeyPath);

        $this->assertInstanceOf(ResourceServer::class, $factory->create());
    }
}
