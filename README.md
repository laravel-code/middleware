# Middleware for OAuth microservices

This packages contains Middleware to authenticate api clients against a passport configured server.

It also contains a client to request a ```bearer``` token that will get cached en will be used 
for any future requests between microservices.


# The setup

Lets say you have a todo app with users. Then you would create two microservices.
- users (passport)
- todo

Within the users microservice you should create a client that can be used with the todo app.

When making requests on the todo api, it will check with the users api if the api is authorized 
to make requests with other microservices.

If it does, it will return a bearer token that will be used when talking to other microservice api's


# Installation

```shell script
composer require laravel-code/middleware
```

The ```middlewareServiceProvider``` will be automatically discovered bij laravel.

Add the following fields in your ```.env```
```dotenv
AUTHORIZATION_API_HOST=http://docker.for.mac.localhost
AUTHORIZATION_API_TOKEN=/oauth/token
AUTHORIZATION_API_JTI=/api/jti
AUTHORIZATION_API_CLIENT_ID=3
AUTHORIZATION_API_CLIENT_SECRET=""
AUTHORIZATION_API_SCOPES=""
```

Publish the config

```shell script
php artisan vendor:publish --tag=middleware-config
```

Copy the ```oauth-public.key``` from your accounts server into ```/storage/oauth-public.key```

# Available middleware

Register the needed middleware in ```App\Http\kernel.php``` within the ```protected $routeMiddleware``` property


```php
...
        'oauth' => \laravelCode\Middleware\Http\OAuthMiddleWare::class,
        'scope' => \laravelCode\Middleware\Http\ScopeMiddleware::class,
        'role' => \laravelCode\Middleware\Http\RoleMiddleware::class,
...
```


## OAuth
```\LaravelCode\Middleware\Http\OAuthMiddleware```

This will check if the provided bearer token is valid and will request the user profile from the accounts server

```php
Route::group(['middleware' => ['oauth']], function() {
    Route::get('/projects', [\App\Http\Controllers\Api\ProjectsController::class, 'index']);
});
```

## Scopes
```\LaravelCode\Middleware\Http\ScopMiddleware```

This will check if the provided bearer token has the required role

```php
Route::group(['middleware' => ['scope:admin']], function() {
    Route::get('/projects', [\App\Http\Controllers\Api\ProjectsController::class, 'index']);
});
```

## Roles
```\LaravelCode\Middleware\Http\RoleMiddleware```

This will check if the fetched uses has the correct access for the resource

```php
Route::group(['middleware' => ['role:admin']], function() {
    Route::get('/projects', [\App\Http\Controllers\Api\ProjectsController::class, 'index']);
});
```

## Combining middleware

```php
Route::group(['middleware' => ['oauth', 'scope:admin', 'role:admin']], function() {
    Route::get('/projects', [\App\Http\Controllers\Api\ProjectsController::class, 'index']);
});
```

# Service

```LaravelCode\Middleware\Services\ApiService```

You can extend on this abstract class to communicate with other microservices
connected to the same account server.

```php
Namespace App\Services;

use LaravelCode\Middleware\Services\ApiService;

class ToDoService extends ApiService {

    protected function getBaseUrl()
    {
        return 'http://todos/api';
    }
}

```

Register your new service in the boot method of a provider

```php
app()->bind(ToDoService::class, function () {
    return new ToDoService(app()->get(OAuthClient::class));
});
```


## Changes to User model

```php
    protected $appends = ['roles'];

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->using(RoleUser::class)
            ->withPivot([
                'req_get',
                'req_post',
                'req_put',
                'req_patch',
                'req_delete',
            ]);
    }

    public function getRolesAttribute()
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->getRelation('roles');
    }

```