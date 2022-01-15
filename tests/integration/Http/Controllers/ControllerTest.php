<?php

namespace Test\Http\Controllers;

use Illuminate\Http\Client\Request;
use LaravelCode\Middleware\Facades\ClientToken;
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

    public function testOauthMiddleware()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
            ], 200, ['Headers']),
        ]);

        $response = $this->getJson('api/client', ['Authorization' => 'Bearer ' . $this->createUserToken()]);
        $response->assertJson(['ok' => true]);
        $response->assertOk();

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testScopeMiddlewareSuccess()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
            ], 200, ['Headers']),
        ]);

        $response = $this->getJson('api/scope', ['Authorization' => 'Bearer ' . $this->createUserToken()]);
        $response->assertJson(['ok' => true]);
        $response->assertOk();

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testScopeMiddlewareFailed()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
            ], 200, ['Headers']),
        ]);

        $response = $this->getJson('api/scope', ['Authorization' => 'Bearer ' . $this->createUserToken('wrong-scope')]);

        $response->assertStatus(403);

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testGetRoleMiddlewareSuccess()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
                'roles' => [
                    [
                        'name' => 'admin',
                        'pivot' => [
                            'req_post' => true,
                            'req_put' => true,
                            'req_delete' => true,
                            'req_get' => true,
                        ],
                    ],
                ],
            ], 200, ['Headers']),
        ]);

        $response = $this->getJson('api/admin', ['Authorization' => 'Bearer ' . $this->createUserToken()]);

        $response->assertJson(['ok' => true]);
        $response->assertOk();

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testPostRoleMiddlewareSuccess()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
                'roles' => [
                    [
                        'name' => 'admin',
                        'pivot' => [
                            'req_post' => true,
                            'req_put' => true,
                            'req_delete' => true,
                            'req_get' => true,
                        ],
                    ],
                ],
            ], 200, ['Headers']),
        ]);

        $response = $this->postJson('api/admin', [], ['Authorization' => 'Bearer ' . $this->createUserToken()]);

        $response->assertJson(['ok' => true]);
        $response->assertOk();

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testDeleteRoleMiddlewareSuccess()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
                'roles' => [
                    [
                        'name' => 'admin',
                        'pivot' => [
                            'req_post' => true,
                            'req_put' => true,
                            'req_delete' => true,
                            'req_get' => true,
                        ],
                    ],
                ],
            ], 200, ['Headers']),
        ]);

        $response = $this->delete('api/admin', [], ['Authorization' => 'Bearer ' . $this->createUserToken()]);

        $response->assertJson(['ok' => true]);
        $response->assertOk();

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testPutRoleMiddlewareSuccess()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
                'roles' => [
                    [
                        'name' => 'admin',
                        'pivot' => [
                            'req_post' => true,
                            'req_put' => true,
                            'req_delete' => true,
                            'req_get' => true,
                        ],
                    ],
                ],
            ], 200, ['Headers']),
        ]);

        $response = $this->put('api/admin', [], ['Authorization' => 'Bearer ' . $this->createUserToken()]);

        $response->assertJson(['ok' => true]);
        $response->assertOk();

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testPutRoleMiddlewareNoAccess()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
                'roles' => [
                    [
                        'name' => 'admin',
                        'pivot' => [
                            'req_post' => false,
                            'req_put' => false,
                            'req_delete' => false,
                            'req_get' => false,
                        ],
                    ],
                ],
            ], 200, ['Headers']),
        ]);

        $response = $this->put('api/admin', [], ['Authorization' => 'Bearer ' . $this->createUserToken()]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'User does not have required permission(put) on role(admin)']);

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testPutRoleMiddlewareNoRoles()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([
                'name' => 'John Doe',
            ], 200, ['Headers']),
        ]);

        $response = $this->put('api/admin', [], ['Authorization' => 'Bearer ' . $this->createUserToken()]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'User does not have required role(admin)']);

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
    }

    public function testPutRoleMiddlewareNoProfile()
    {
        \Http::fake([
            'accounts.dummy/api/token' => \Http::response([
                'access_token' => $this->createClientToken(),
                'expires_in' => (new \DateTimeImmutable('+1 hour'))->getTimestamp(),

            ], 200),
            'accounts.dummy/api/jti/*' => \Http::response([], 200, ['Headers']),
        ]);

        $response = $this->put('api/admin', [], ['Authorization' => 'Bearer ' . $this->createUserToken()]);

        $response->assertStatus(403);

        \Http::assertSent(function (Request $request) {
            return $request->url() === 'https://accounts.dummy/api/token';
        });

        \Http::assertSent(function (Request $request) {
            return strpos($request->url(), 'https://accounts.dummy/api/jti/') === 0;
        });
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
            '--path' => realpath(__DIR__ . '/../../../fixtures'),
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
            'host' => 'https://accounts.dummy',
            'path' => '/api/token',
            'client_id' => 3,
            'client_secret' => 'b89260e9-4fa3-4e87-b2ab-58d746be491a',
            'scopes' => 'profile',
            'public_key' => realpath(__DIR__ . '/../../../oauth-public.key'),
        ]);

        $app['router']->middleware(['oauth'])->get('api/client', function () {
            return response()->json(['ok' => true]);
        });

        $app['router']->middleware(['oauth', 'oauth.scope:profile'])->get('api/scope', function () {
            return response()->json(['ok' => true]);
        });

        $app['router']->middleware(['oauth', 'oauth.role:admin'])->group(function () use ($app) {
            $app['router']->get('api/admin', function () {
                return response()->json(['ok' => true]);
            });

            $app['router']->post('api/admin', function () {
                return response()->json(['ok' => true]);
            });

            $app['router']->put('api/admin', function () {
                return response()->json(['ok' => true]);
            });

            $app['router']->delete('api/admin', function () {
                return response()->json(['ok' => true]);
            });
        });

        $app['router']->middleware(['oauth.role:admin'])->group(function () use ($app) {
            $app['router']->get('api/no-user', function () {
                return response()->json(['ok' => true]);
            });
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
            'ClientToken' => ClientToken::class,
        ];
    }
}
