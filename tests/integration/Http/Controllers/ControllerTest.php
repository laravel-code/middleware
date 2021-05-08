<?php

namespace Test\integration\Http\Controllers;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request as ClientRequest;
use Illuminate\Support\Facades\Auth;
use LaravelCode\Middleware\Facades\HttpClient;
use LaravelCode\Middleware\Facades\OAuthClient;
use Mockery;
use Orchestra\Testbench\TestCase;
use Test\TestTrait;
use TestApp\AuthServiceProvider;
use TestApp\Models\User;

class ControllerTest extends TestCase
{
    use TestTrait;

    public function testClientToken()
    {
        $requests = [];
        $history = Middleware::history($requests);

        $stack = HandlerStack::create(
            new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'access_token' => $this->createClientToken(),
                    'expires_in' => time() + 3600,
                    'token_type' => 'jwt',
                ])),
            ])
        );
        $stack->push($history);
        $this->mockHttpClient($stack);

        $response = $this->getJson('api/client');
        $response->assertJson(['ok' => true]);
        $response->assertOk();

        $this->assertRequests($requests, [
            function (Request $request) {
                parse_str($request->getBody()->getContents(), $body);
                $this->assertEquals('client_credentials', $body['grant_type']);
                $this->assertEquals('3', $body['client_id']);
                $this->assertEquals('b89260e9-4fa3-4e87-b2ab-58d746be491a', $body['client_secret']);
                $this->assertSame('profile', $body['scope']);
            },
        ]);
    }

    public function testClientTokenContentTypeException()
    {
        $requests = [];
        $history = Middleware::history($requests);

        $stack = HandlerStack::create(
            new MockHandler([
                new Response(200, ['Content-Type' => 'text/html'], 'bogus data'),
            ])
        );
        $stack->push($history);
        $this->mockHttpClient($stack);

        $response = $this->getJson('api/client');
        $this->assertEquals('JSON accepted but received text/html', $response->exception->getMessage());
        $this->assertEquals(500, $response->exception->getCode());
    }

    public function testClientDenied()
    {
        $this->createClientToken();

        $requests = [];
        $history = Middleware::history($requests);

        $stack = HandlerStack::create(
            new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], json_encode(null)),
            ])
        );
        $stack->push($history);
        $this->mockHttpClient($stack);

        $response = $this->getJson('api/client');
        $this->assertEquals('Api client could not get authorized', $response->exception->getMessage());
        $this->assertEquals(401, $response->exception->getCode());
    }

    public function testOauthToken()
    {
        $requests = [];
        $history = Middleware::history($requests);

        $stack = HandlerStack::create(
            new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'access_token' => $this->createClientToken(),
                    'expires_in' => time() + 3600,
                    'token_type' => 'jwt',
                ])),
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'id' => 1,
                ])),
            ])
        );
        $stack->push($history);
        $this->mockHttpClient($stack);

        $response = $this->getJson('api/oauth', ['Authorization' => 'Bearer '.$this->createUserToken()]);
        $this->assertTrue($response->json('ok'));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertRequests($requests, [
            function (Request $request) {
                parse_str($request->getBody()->getContents(), $body);
                $this->assertEquals('client_credentials', $body['grant_type']);
                $this->assertEquals('3', $body['client_id']);
                $this->assertEquals('b89260e9-4fa3-4e87-b2ab-58d746be491a', $body['client_secret']);
                $this->assertSame('profile', $body['scope']);
            },
            function (Request $request) {
                $this->assertEquals('/api/profile', $request->getUri()->getPath());
                $this->assertNotEmpty($request->getHeaderLine('Authorization'));
            },
        ]);
    }

    /** Test route  */
    public function testOauthOrGuestStepGuest()
    {
        $requests = [];
        $history = Middleware::history($requests);

        $stack = HandlerStack::create(
            new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'access_token' => $this->createClientToken(),
                    'expires_in' => time() + 3600,
                    'token_type' => 'jwt',
                ])),
            ])
        );
        $stack->push($history);
        $this->mockHttpClient($stack);

        $response = $this->getJson('api/oauth/guest');
        $this->assertNull($response->json('id'));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertRequests($requests, [
            function (Request $request) {
                parse_str($request->getBody()->getContents(), $body);
                $this->assertEquals('client_credentials', $body['grant_type']);
                $this->assertEquals('3', $body['client_id']);
                $this->assertEquals('b89260e9-4fa3-4e87-b2ab-58d746be491a', $body['client_secret']);
                $this->assertSame('profile', $body['scope']);
            },
        ]);
    }

    public function testOauthOrGuestStepOAuth()
    {
        $requests = [];
        $history = Middleware::history($requests);

        $stack = HandlerStack::create(
            new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'access_token' => $this->createClientToken(),
                    'expires_in' => time() + 3600,
                    'token_type' => 'jwt',
                ])),
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'id' => 1,
                ])),
            ])
        );
        $stack->push($history);
        $this->mockHttpClient($stack);

        $response = $this->getJson('api/oauth/guest', ['Authorization' => 'Bearer '.$this->createUserToken()]);
        $this->assertEquals(1, $response->json('id'));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertRequests($requests, [
            function (Request $request) {
                parse_str($request->getBody()->getContents(), $body);
                $this->assertEquals('client_credentials', $body['grant_type']);
                $this->assertEquals('3', $body['client_id']);
                $this->assertEquals('b89260e9-4fa3-4e87-b2ab-58d746be491a', $body['client_secret']);
                $this->assertSame('profile', $body['scope']);
            },
            function (Request $request) {
                $this->assertEquals('/api/profile', $request->getUri()->getPath());
                $this->assertNotEmpty($request->getHeaderLine('Authorization'));
            },
        ]);
    }

    public function testUserToken()
    {
        $requests = [];
        $history = Middleware::history($requests);

        $stack = HandlerStack::create(
            new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'id' => 1,
                ])),
            ])
        );
        $stack->push($history);
        $this->mockHttpClient($stack);

        $response = $this->getJson('api/user', ['Authorization' => 'Bearer '.$this->createUserToken()]);
        $this->assertTrue($response->json('ok'));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertRequests($requests, [
            function (Request $request) {
                $this->assertEquals('/api/profile', $request->getUri()->getPath());
                $this->assertNotEmpty($request->getHeaderLine('Authorization'));
            },
        ]);
    }

    public function testPassportApiCredentials()
    {
        $token = $this->createUserToken();
        $response = $this->getJson('api/profile', [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ]);
        $response->assertHeader('X_OAUTH_USER_ID', 1);
        $response->assertHeader('X_OAUTH_CLIENT_ID', 2);
        $response->assertJson(['id' => 1]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);

        // call migrations specific to our tests, e.g. to seed the db
        // the path option should be an absolute path.

        $this->loadMigrationsFrom([
            '--database' => 'testing',
        ]);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../../../fixtures'),
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->setStoragePath($app);
        $app['config']->set('app.key', 'base64:uJTCCZW1wsX8vRKGMRd3gUU+zzP/caRsxHilmWlZOkI=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('passport.storage.database.connection', 'testing');
        $app['config']->set('auth.guards.api.driver', 'passport');
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('oauth', [
            'host' => 'https://dummy.dummy',
            'token' => '/api/token',
            'client_id' => 3,
            'client_secret' => 'b89260e9-4fa3-4e87-b2ab-58d746be491a',
            'scopes' => 'profile',
            'public_key' => realpath(__DIR__.'/../../../oauth-public.key'),
        ]);

        $app['router']->middleware(['oauth.client'])->get('api/client', function () {
            return response()->json(['ok' => true]);
        });

        $app['router']->middleware(['oauth.user'])->get('api/user', function () {
            return response()->json(['ok' => true]);
        });

        $app['router']->middleware(['oauth'])->get('api/oauth', function () {
            return response()->json(['ok' => true]);
        });

        $app['router']->middleware(['oauth.guest'])->get('api/oauth/guest', function () {
            return response()->json(Auth::getUser());
        });

        $app['router']->middleware(['oauth.api'])->get('api/profile', function (ClientRequest $request, Session $session) {
            return response()->json($request->user(), 200, [
                'X_OAUTH_CLIENT_ID' => $request->get('X_OAUTH_CLIENT_ID'),
                'X_OAUTH_USER_ID' => $request->get('X_OAUTH_USER_ID'),
            ]);
        });

        $app['router']->middleware(['oauth.api:admin'])->get('api/admin', function () {
        });

        $app['config']->set('app.debug', true);
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AuthServiceProvider::class,
            'Laravel\Passport\PassportServiceProvider',
            'LaravelCode\Middleware\MiddlewareProvider',
        ];
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'TestApp\Http\Kernel');
    }

    protected function getPackageAliases($app)
    {
        return [
            'OAuthClient' => OAuthClient::class,
            'HttpClient' => HttpClient::class,
        ];
    }
}
