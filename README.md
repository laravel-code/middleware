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

## Frontfacing api's

```LaravelCode\Middleware\Http\Middleware\OAuth```

This will check if the client is authorized and if the user is authorized.
It will set the Auth::user() on the Request object.

## System to Systems

```LaravelCode\Middleware\Http\Middleware\OAuthClient```

## Check Bearer token

```LaravelCode\Middleware\Http\Middleware\OAuthClient```

Check if the user Bearer token is valid.


## AccountsServer

```LaravelCode\Middleware\Http\Middleware\CheckApiCredentials```

This will any token, personal of client token.
When it is a personal token, the Auth user will get set on the Request

# Service

```LaravelCode\Middleware\Services\ApiService```

You can extend on this abstract class to communicate with other miscroservices
connected to the same account server.

```
<?php
Namespace App\Services;

use LaravelCode\Middleware\Services\ApiService;

class ToDoService extends ApiService {

    protected function getBaseUrl()
    {
        return 'http://todos/api';
    }
}

```