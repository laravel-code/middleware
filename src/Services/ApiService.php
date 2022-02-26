<?php

namespace LaravelCode\Middleware\Services;

use Exception;
use LaravelCode\Middleware\Factories\OAuthClient;

abstract class ApiService
{
    /**
     * @var OAuthClient
     */
    protected OAuthClient $service;

    /**
     * ApiService constructor.
     * @param OAuthClient $service
     */
    public function __construct(OAuthClient $service)
    {
        $this->service = $service;
    }

    /**
     * @param string $resource
     * @param array $query
     * @return mixed
     * @throws Exception
     */
    public function index(string $resource, array $query = []): mixed
    {
        $params = [
            'query' => $query,
        ];

        return $this->service->client('get', $this->getBaseUrl(), $resource, $params);
    }

    /**
     * @return mixed
     */
    abstract protected function getBaseUrl();

    /**
     * @param string $resource
     * @param string|int $id
     * @param array $query
     * @return mixed
     * @throws Exception
     */
    public function show(string $resource, string|int $id, array $query = []): mixed
    {
        $params = [
            'query' => $query,
        ];

        return $this->service->client('get', $this->getBaseUrl(), "$resource/$id", $params);
    }

    /**
     * @param string $resource
     * @param string|int $id
     * @return mixed
     * @throws Exception
     */
    public function delete(string $resource, string|int $id): mixed
    {
        return $this->service->client('delete', $this->getBaseUrl(), "$resource/$id");
    }

    /**
     * @param string $resource
     * @param string|int $id
     * @param array $formParams
     * @return mixed
     * @throws Exception
     */
    public function update(string $resource, string|int $id, array $formParams = []): mixed
    {
        $params = [
            'form_params' => $formParams,
        ];

        return $this->service->client('put', $this->getBaseUrl(), "$resource/$id", $params);
    }

    /**
     * @param string $resource
     * @param array $formParams
     * @return mixed
     * @throws Exception
     */
    public function store(string $resource, array $formParams = []): mixed
    {
        $params = [
            'form_params' => $formParams,
        ];

        return $this->service->client('post', $this->getBaseUrl(), $resource, $params);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $query
     * @param array $formParams
     * @param array $headers
     * @return mixed
     * @throws Exception
     */
    public function request(string $method, string $path, array $query = [], array $formParams = [], array $headers = []): mixed
    {
        $params = [
            'query' => $query,
            'form_params' => $formParams,
            'headers' => $headers,
        ];

        return $this->service->client($method, $this->getBaseUrl(), $path, $params);
    }
}
