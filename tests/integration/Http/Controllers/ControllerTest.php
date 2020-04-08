<?php

namespace Tester\Http\Controllers;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LaravelCode\Middleware\Facades\HttpClient;
use LaravelCode\Middleware\Facades\OAuthClient;
use Mockery;
use Orchestra\Testbench\TestCase;
use Test\TestTrait;

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
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
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
            'LaravelCode\Middleware\OauthProvider',
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
