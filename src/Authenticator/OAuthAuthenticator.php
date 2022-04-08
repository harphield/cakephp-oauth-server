<?php
declare(strict_types=1);

namespace OAuthServer\Authenticator;

use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\Authenticator\StatelessInterface;
use Cake\Core\Configure;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use OAuthServer\Bridge\ResourceServerFactory;
use OAuthServer\Identifier\OAuthIdentifier;
use Psr\Http\Message\ServerRequestInterface;

/**
 * OAuth Authenticator
 *
 * Authenticates an identity based on an OAuth access token in the header.
 */
class OAuthAuthenticator extends AbstractAuthenticator implements StatelessInterface
{
    /**
     * @var \League\OAuth2\Server\ResourceServer
     */
    protected $Server;

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'publicKey' => null,
        'fields' => [
            OAuthIdentifier::CREDENTIAL_OAUTH => 'oauth_user_id',
        ],
    ];

    /**
     * @return \League\OAuth2\Server\ResourceServer
     */
    protected function getServer(): ResourceServer
    {
        if (!$this->Server) {
            $serverFactory = new ResourceServerFactory(
                $this->getConfig('publicKey', Configure::read('OAuthServer.publicKey'))
            );

            $this->setServer($serverFactory->create());
        }

        return $this->Server;
    }

    /**
     * @param \League\OAuth2\Server\ResourceServer $Server the ResourceServer instance
     * @return void
     */
    public function setServer(ResourceServer $Server): void
    {
        $this->Server = $Server;
    }

    /**
     * Authenticate a user based on the request information.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request to get authentication information.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        try {
            $request = $this->getServer()->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $e) {
            return new Result(null, Result::FAILURE_CREDENTIALS_INVALID, (array)$e->getMessage());
        }

        $userId = $request->getAttribute(
            $this->getConfig('fields.' . OAuthIdentifier::CREDENTIAL_OAUTH)
        );
        $data = [
            OAuthIdentifier::CREDENTIAL_OAUTH => $userId,
            'oauth' => [
                'access_token_id' => $request->getAttribute('oauth_access_token_id'),
                'user_id' => $request->getAttribute('oauth_user_id'),
                'client_id' => $request->getAttribute('oauth_client_id'),
                'scopes' => $request->getAttribute('oauth_scopes'),
            ],
        ];

        $user = $this->_identifier->identify($data);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->_identifier->getErrors());
        }

        return new Result($user, Result::SUCCESS);
    }

    /**
     * No-op method.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request A request object.
     * @return void
     */
    public function unauthorizedChallenge(ServerRequestInterface $request): void
    {
    }
}
