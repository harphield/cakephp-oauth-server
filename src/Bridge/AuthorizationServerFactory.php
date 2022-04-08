<?php
declare(strict_types=1);

namespace OAuthServer\Bridge;

use Defuse\Crypto\Key;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use OAuthServer\Bridge\Repository\AccessTokenRepository;
use OAuthServer\Bridge\Repository\ClientRepository;
use OAuthServer\Bridge\Repository\ScopeRepository;

/**
 * Build AuthorizationServer
 */
class AuthorizationServerFactory
{
    /**
     * @var \League\OAuth2\Server\Repositories\ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface
     */
    private $accessTokenRepository;

    /**
     * @var \League\OAuth2\Server\Repositories\ScopeRepositoryInterface
     */
    private $scopeRepository;

    /**
     * @var \League\OAuth2\Server\CryptKey
     */
    private $privateKey;

    /**
     * @var \Defuse\Crypto\Key
     */
    private $encryptionKey;

    /**
     * AuthorizationServerFactory constructor.
     *
     * @param \League\OAuth2\Server\CryptKey|string $privateKey the PrivateKey's path or a CryptKey instance.
     * @param \Defuse\Crypto\Key|string $encryptionKey the Encryption key string or a Key instance,
     */
    public function __construct($privateKey, $encryptionKey)
    {
        $this->setPrivateKey($privateKey);
        $this->setEncryptionKey($encryptionKey);
    }

    /**
     * @return \League\OAuth2\Server\AuthorizationServer
     */
    public function create(): AuthorizationServer
    {
        return new AuthorizationServer(
            $this->getClientRepository(),
            $this->getAccessTokenRepository(),
            $this->getScopeRepository(),
            $this->getPrivateKey(),
            $this->getEncryptionKey()
        );
    }

    /**
     * @return \League\OAuth2\Server\Repositories\ClientRepositoryInterface
     */
    public function getClientRepository(): ClientRepositoryInterface
    {
        if (!$this->clientRepository) {
            $this->clientRepository = new ClientRepository();
        }

        return $this->clientRepository;
    }

    /**
     * @param \League\OAuth2\Server\Repositories\ClientRepositoryInterface $clientRepository the Repository
     * @return void
     */
    public function setClientRepository(ClientRepositoryInterface $clientRepository): void
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * @return \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface
     */
    public function getAccessTokenRepository(): AccessTokenRepositoryInterface
    {
        if (!$this->accessTokenRepository) {
            $this->accessTokenRepository = new AccessTokenRepository();
        }

        return $this->accessTokenRepository;
    }

    /**
     * @param \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface $accessTokenRepository the Repository
     * @return void
     */
    public function setAccessTokenRepository(AccessTokenRepositoryInterface $accessTokenRepository): void
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * @return \League\OAuth2\Server\Repositories\ScopeRepositoryInterface
     */
    public function getScopeRepository(): ScopeRepositoryInterface
    {
        if (!$this->scopeRepository) {
            $this->scopeRepository = new ScopeRepository();
        }

        return $this->scopeRepository;
    }

    /**
     * @param \League\OAuth2\Server\Repositories\ScopeRepositoryInterface $scopeRepository the Repository
     * @return void
     */
    public function setScopeRepository(ScopeRepositoryInterface $scopeRepository): void
    {
        $this->scopeRepository = $scopeRepository;
    }

    /**
     * @return \League\OAuth2\Server\CryptKey
     */
    public function getPrivateKey(): CryptKey
    {
        return $this->privateKey;
    }

    /**
     * @param \League\OAuth2\Server\CryptKey|string $privateKey the PrivateKey's path or a CryptKey instance.
     * @return void
     */
    public function setPrivateKey($privateKey): void
    {
        if (is_string($privateKey)) {
            $privateKey = new CryptKey($privateKey);
        }

        $this->privateKey = $privateKey;
    }

    /**
     * @return \Defuse\Crypto\Key
     */
    public function getEncryptionKey(): Key
    {
        return $this->encryptionKey;
    }

    /**
     * @param \Defuse\Crypto\Key|string $encryptionKey the Encryption key string or a Key instance,
     * @return void
     */
    public function setEncryptionKey($encryptionKey): void
    {
        if (is_string($encryptionKey)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $encryptionKey = Key::loadFromAsciiSafeString($encryptionKey);
        }

        $this->encryptionKey = $encryptionKey;
    }
}
