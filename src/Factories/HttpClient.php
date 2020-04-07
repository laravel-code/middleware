<?php

namespace LaravelCode\Middleware\Factories;

use GuzzleHttp\Client;

class HttpClient
{
    private $client;

    /**
     * HttpClient constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }
}
