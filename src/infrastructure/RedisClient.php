<?php

namespace App\infrastructure;

use Predis\Client;

class RedisClient
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'scheme' => 'tcp',
            'host' => 'redis',
            'port' => 6379,
        ]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
