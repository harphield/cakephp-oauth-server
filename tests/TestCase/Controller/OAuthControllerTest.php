<?php
declare(strict_types=1);

namespace OAuthServer\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;
use OAuthServer\Controller\OAuthController;
use OAuthServer\Plugin as OAuthServerPlugin;
use TestApp\AuthenticationServiceProvider;
use TestApp\Controller\TestAppController;

class OAuthControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'plugin.OAuthServer.Clients',
        'plugin.OAuthServer.Scopes',
        'plugin.OAuthServer.AccessTokens',
        'plugin.OAuthServer.AccessTokenScopes',
        'plugin.OAuthServer.AuthCodes',
        'plugin.OAuthServer.AuthCodeScopes',
        'plugin.OAuthServer.RefreshTokens',
        'plugin.OAuthServer.Users',
    ];

    /**
     * @noinspection PhpIncludeInspection
     */
    public function setUp(): void
    {
        // class Router needs to be loaded in order for TestCase to automatically include routes
        // not really sure how to do it properly, this hotfix seems good enough
        Router::defaultRouteClass();

        parent::setUp();

        Router::connect('/');
        Router::scope('/', static function (RouteBuilder $route) {
            $OAuthServerPlugin = new OAuthServerPlugin();
            $OAuthServerPlugin->routes($route);

            $route->fallbacks();
        });

        $authenticationServiceProvider = new AuthenticationServiceProvider();
        $this->configRequest([
            'attributes' => [
                'authentication' => $authenticationServiceProvider->getAuthenticationService(new ServerRequest()),
            ],
        ]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testInstanceOfClassFromConfig()
    {
        $controller = new OAuthController();
        $this->assertInstanceOf(TestAppController::class, $controller);
    }

    public function testAssertRoute()
    {
        $parsed = Router::parseRequest(new ServerRequest(['url' => '/oauth']));
        $this->assertEquals([
            'controller' => 'OAuth',
            'action' => 'oauth',
            'plugin' => 'OAuthServer',
            'pass' => [],
            '_matchedRoute' => '/oauth',
        ], $parsed);

        $parsed = Router::parseRequest(new ServerRequest(['url' => '/oauth/authorize']));
        $this->assertEquals([
            'controller' => 'OAuth',
            'action' => 'authorize',
            'plugin' => 'OAuthServer',
            'pass' => [],
            '_matchedRoute' => '/oauth/authorize',
        ], $parsed);

        $parsed = Router::parseRequest(new ServerRequest(['url' => '/oauth/access_token']));
        $this->assertEquals([
            'controller' => 'OAuth',
            'action' => 'accessToken',
            'plugin' => 'OAuthServer',
            'pass' => [],
            '_matchedRoute' => '/oauth/access_token',
        ], $parsed);
    }

    public function testOauthRedirectsToAuthorize()
    {
        $this->get($this->url('/oauth') . '?client_id=CID&anything=at_all');
        $this->assertRedirect(['controller' => 'OAuth', 'action' => 'authorize', '?' => ['client_id' => 'CID', 'anything' => 'at_all']]);
        $this->assertResponseCode(301);
    }

    public function testAuthorizeLoginRedirectWhenNotLoggedIn()
    {
        $query = ['client_id' => 'TEST', 'redirect_uri' => 'http://www.example.com', 'response_type' => 'code', 'scope' => 'test'];
        $authorizeUrl = $this->url('/oauth/authorize') . '?' . http_build_query($query);

        $this->get($authorizeUrl);

        // cakephp/authentication plugin does not absolute( `fullBase` ) url
        $this->assertRedirectContains(Router::url(['plugin' => false, 'controller' => 'Users', 'action' => 'login', '?' => ['redirect' => $authorizeUrl], '_full' => false]));
    }

    public function testAuthorizeInvalidClientId()
    {
        $this->session(['Auth.User.id' => 'user1']);
        $query = ['client_id' => 'INVALID', 'redirect_uri' => 'http://www.example.com', 'response_type' => 'code', 'scope' => 'test'];
        $this->get($this->url('/oauth/authorize') . '?' . http_build_query($query));

        $this->assertResponseError('Client authentication failed');
    }

    public function testAuthorizeInvalidRedirectUri()
    {
        $this->session(['Auth.User.id' => 'user1']);
        $query = ['client_id' => 'TEST', 'redirect_uri' => 'http://invalid.example.com', 'response_type' => 'code', 'scope' => 'test'];
        $this->get($this->url('/oauth/authorize') . '?' . http_build_query($query));

        $this->assertResponseError('Client authentication failed');
    }

    public function testGetAuthorize()
    {
        $this->session(['Auth.User.id' => 'user1']);
        $query = ['client_id' => 'TEST', 'redirect_uri' => 'http://www.example.com', 'response_type' => 'code', 'scope' => 'test'];
        $this->get($this->url('/oauth/authorize') . '?' . http_build_query($query));

        $this->assertResponseOk();

        $this->assertResponseContains('Test would like to access:');
    }

    public function testAuthorizeLoginRedirectWhenWithPromptLogin()
    {
        $this->session(['Auth.User.id' => 'user1']);
        $query = ['prompt' => 'login', 'client_id' => 'TEST', 'redirect_uri' => 'http://www.example.com', 'response_type' => 'code', 'scope' => 'test'];
        $authorizeUrl = $this->url('/oauth/authorize') . '?' . http_build_query($query);
        unset($query['prompt']);
        $expectedLoginRedirectUrl = $this->url('/oauth/authorize') . '?' . http_build_query($query);
        $this->get($authorizeUrl);

        $this->assertSession(null, 'Auth.User.id', 'will logged out');
        $this->assertRedirect($expectedLoginRedirectUrl);
    }

    public function testAuthorizationCodeDeny()
    {
        $this->session(['Auth.User.id' => 'user1']);

        $query = ['client_id' => 'TEST', 'redirect_uri' => 'http://www.example.com', 'response_type' => 'code', 'scope' => 'test'];
        $this->post($this->url('/oauth/authorize') . '?' . http_build_query($query), ['authorization' => 'Deny']);

        $this->assertRedirect();

        $redirectUrl = $this->_response->getHeaderLine('Location');
        $this->assertStringStartsWith('http://www.example.comerror=access_denied&message=', $redirectUrl);
    }

    public function testAuthorizationCode()
    {
        $this->session(['Auth.User.id' => 'user1']);

        $query = ['client_id' => 'TEST', 'redirect_uri' => 'http://www.example.com', 'response_type' => 'code', 'scope' => 'test'];
        $this->post($this->url('/oauth/authorize') . '?' . http_build_query($query), ['authorization' => 'Approve']);

        $this->assertRedirect();

        $redirectUrl = $this->_response->getHeaderLine('Location');
        $this->assertStringStartsWith('http://www.example.com?code=', $redirectUrl);
        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $responseQuery);

        // Logged out and get access token.
        $this->session(['Auth.User.id' => null]);

        $this->post('/oauth/access_token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'TEST',
            'client_secret' => 'TestSecret',
            'redirect_uri' => 'http://www.example.com',
            'code' => $responseQuery['code'],
        ]);
        $this->assertResponseOk();

        $response = $this->grabResponseJson();
        $this->assertSame('Bearer', $response['token_type']);
        $this->assertSame(3600, $response['expires_in']);
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
    }

    public function testClientCredentialsAuthorization()
    {
        $this->post('/oauth/access_token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'TEST',
            'client_secret' => 'TestSecret',
            'scope' => 'test',
        ]);
        $this->assertResponseOk();

        $response = $this->grabResponseJson();
        $this->assertSame('Bearer', $response['token_type']);
        $this->assertSame(3600, $response['expires_in']);
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayNotHasKey('refresh_token', $response);
    }

    public function testPasswordAuthorization()
    {
        $this->post('/oauth/access_token', [
            'grant_type' => 'password',
            'client_id' => 'TEST',
            'client_secret' => 'TestSecret',
            'scope' => 'test',
            'username' => 'user1@example.com',
            'password' => '123456',
        ]);
        $this->assertResponseOk();

        $response = $this->grabResponseJson();
        $this->assertSame('Bearer', $response['token_type']);
        $this->assertSame(3600, $response['expires_in']);
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
    }

    public function testRefreshToken()
    {
        $this->post('/oauth/access_token', [
            'grant_type' => 'password',
            'client_id' => 'TEST',
            'client_secret' => 'TestSecret',
            'scope' => 'test',
            'username' => 'user1@example.com',
            'password' => '123456',
        ]);

        $this->assertResponseOk();
        $response = $this->grabResponseJson();
        $this->assertSame('Bearer', $response['token_type']);
        // Allow 5 seconds difference
        $this->assertLessThanOrEqual(3600, $response['expires_in']);
        $this->assertGreaterThanOrEqual(3595, $response['expires_in']);
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);

        $this->post('/oauth/access_token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'TEST',
            'client_secret' => 'TestSecret',
            'scope' => 'test',
            'refresh_token' => $response['refresh_token'],
        ]);
        $this->assertResponseOk();
        $refreshed = $this->grabResponseJson();
        $this->assertSame('Bearer', $refreshed['token_type']);
        // Allow 5 seconds difference
        $this->assertLessThanOrEqual(3600, $refreshed['expires_in']);
        $this->assertGreaterThanOrEqual(3595, $response['expires_in']);
        $this->assertArrayHasKey('access_token', $refreshed);
        $this->assertArrayHasKey('refresh_token', $refreshed);
        $this->assertNotEquals($response['access_token'], $refreshed['access_token']);
        $this->assertNotEquals($response['refresh_token'], $refreshed['refresh_token']);
    }

    public function testNotPermitedAuthorization()
    {
        $this->post('/oauth/access_token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'AuthCodeOnly',
            'client_secret' => 'TestSecret',
            'scope' => 'test',
        ]);

        $this->assertResponseError('Client authentication failed');
    }

    public function testSetComponentConfigurationFromConfigure()
    {
        Configure::write('OAuthServer.accessTokenTTL', 'PT2H');
        $this->post('/oauth/access_token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'TEST',
            'client_secret' => 'TestSecret',
            'scope' => 'test',
        ]);
        $this->assertResponseOk();

        $response = $this->grabResponseJson();
        $this->assertSame(7200, $response['expires_in'], 'expires is 2 hours');
    }

    private function grabResponseJson()
    {
        return json_decode((string)$this->_response->getBody(), true);
    }

    private function url($path, $ext = null)
    {
        $ext = $ext ? ".$ext" : '';

        return $path . $ext;
    }
}
