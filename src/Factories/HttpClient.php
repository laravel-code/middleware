<?php

namespace LaravelCode\Middleware\Factories;

use GuzzleHttp\Client;

class HttpClient
{
    private $client;

    /**
     * HttpClient constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->client = new Client($options);
    }

    public function getClient()
    {
        return $this->client;
    }
}
