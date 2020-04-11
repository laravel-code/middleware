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