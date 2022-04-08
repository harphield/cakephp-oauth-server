<?php
declare(strict_types=1);

namespace OAuthServer\Controller\Component;

use Authentication\AuthenticationServiceInterface;
use Authentication\Authenticator\AuthenticatorInterface;
use Cake\Controller\Component;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\NotImplementedException;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use DateInterval;
use InvalidArgumentException;
use League\OAuth2\Server\AuthorizationServer;
use OAuthServer\Bridge\AuthorizationServerFactory;
use OAuthServer\Bridge\GrantFactory;
use OAuthServer\Bridge\UserFinderByUserCredentialsInterface;

class OAuthComponent extends Component implements UserFinderByUserCredentialsInterface
{
    /**
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $Server;

    /**
     * Grant types currently supported by the plugin
     *
     * @var array
     */
    protected $_allowedGrants = ['AuthCode', 'RefreshToken', 'ClientCredentials', 'Password'];

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'supportedGrants' => [
            'AuthCode',
            'RefreshToken',
            'ClientCredentials',
            'Password',
        ],
        'passwordAuthenticator' => 'Form',
        'userIdentityPath' => 'id',
        'privateKey' => null,
        'encryptionKey' => null,
        'accessTokenTTL' => 'PT1H',
        'refreshTokenTTL' => 'P1M',
        'authCodeTTL' => 'PT10M',
    ];

    /**
     * @param array $config Config array
     * @return void
     */
    public function initialize(array $config): void
    {
        if ($this->getConfig('server') && $this->getConfig('server') instanceof AuthorizationServer) {
            $this->setServer($this->getConfig('server'));
        }
        // Override supportedGrants option without merging.
        if (isset($config['supportedGrants'])) {
            $this->setConfig('supportedGrants', $config['supportedGrants'], false);
        }

        // setup enabled grant types.
        $server = $this->getServer();
        $supportedGrants = $this->getConfig('supportedGrants');
        $supportedGrants = $this->_registry->normalizeArray($supportedGrants);

        $grantFactory = new GrantFactory($this);

        foreach ($supportedGrants as $properties) {
            $grant = $properties['class'];

            if (!in_array($grant, $this->_allowedGrants)) {
                throw new NotImplementedException(__('The {0} grant type is not supported by the OAuthServer'));
            }

            $objGrant = $grantFactory->create($grant);

            if (method_exists($objGrant, 'setRefreshTokenTTL')) {
                $objGrant->setRefreshTokenTTL(new DateInterval($this->getConfig('refreshTokenTTL')));
            }

            foreach ($properties['config'] as $key => $value) {
                $method = 'set' . Inflector::camelize($key);
                if (is_callable([$objGrant, $method])) {
                    $objGrant->$method($value);
                }
            }

            $server->enableGrantType($objGrant, new DateInterval($this->getConfig('accessTokenTTL')));
        }
    }

    /**
     * @return \League\OAuth2\Server\AuthorizationServer
     */
    public function getServer(): AuthorizationServer
    {
        if (!$this->Server) {
            $factory = new AuthorizationServerFactory(
                $this->getConfig('privateKey'),
                $this->getConfig('encryptionKey')
            );

            $this->setServer($factory->create());
        }

        return $this->Server;
    }

    /**
     * @param \League\OAuth2\Server\AuthorizationServer $Server a AuthorizationServer instance.
     * @return void
     */
    public function setServer(AuthorizationServer $Server): void
    {
        $this->Server = $Server;
    }

    /**
     * @inheritDoc
     */
    public function findUser($username, $password): ?EntityInterface
    {
        $controller = $this->_registry->getController();
        $authenticationService = $this->getAuthenticationService();
        $authenticator = $this->getPasswordAuthenticator();

        $uri = $controller->getRequest()->getUri();
        $uri = $uri->withPath($authenticator->getConfig('loginUrl'));

        $request = $controller->getRequest()
            ->withData($authenticator->getConfig('fields.username'), $username)
            ->withData($authenticator->getConfig('fields.password'), $password)
            ->withUri($uri);

        $user = $authenticator->authenticate($request);

        if ($user->isValid() === false) {
            return null;
        }

        return new Entity($user->getData()->toArray());
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentityPath()
    {
        return $this->getConfig('userIdentityPath');
    }

    /**
     * @return \OAuthServer\Controller\Component\Authentication\AuthenticationServiceInterface
     */
    protected function getAuthenticationService(): AuthenticationServiceInterface
    {
        $controller = $this->_registry->getController();
        $authenticationService = $controller->getRequest()->getAttribute('authentication');

        if (!$authenticationService) {
            throw new InvalidArgumentException(
                __('OAuthComponent require \Cake\Authentication\AuthenticationService.')
            );
        }

        return $authenticationService;
    }

    /**
     * @return \Authentication\Authenticator\AuthenticatorInterface
     */
    protected function getPasswordAuthenticator(): AuthenticatorInterface
    {
        $authenticationService = $this->getAuthenticationService();

        $authenticator = $authenticationService->authenticators()->get($this->getConfig('passwordAuthenticator'));

        if (!$authenticator) {
            throw new InvalidArgumentException(__('Can\'t get PasswordAuthenticator.'));
        }

        return $authenticator;
    }
}
