<?php

namespace App\repository;

use App\domain\Process;
use App\infrastructure\RedisClient;

class ProcessRepository
{
    private $redis;

    public function __construct(RedisClient $redisClient)
    {
        $this->redis = $redisClient->getClient();
    }

    public function save(Process $process): void
    {
        $key = "process:" . $process->getId();

        $data = [
            'id' => $process->getId(),
            'requiredMemory' => $process->getRequiredMemory(),
            'requiredCpu' => $process->getRequiredCpu(),
            'machineId' => $process->getMachineId()
        ];

        $this->redis->set($key, json_encode($data));
        $this->redis->sadd("processes", [$process->getId()]);
    }

    public function findAll(): array
    {
        $ids = $this->redis->smembers("processes");

        $processes = [];

        foreach ($ids as $id) {

            $data = $this->redis->get("process:$id");

            if (!$data) {
                continue;
            }

            $decoded = json_decode($data, true);

            $process = new Process(
                $decoded['id'],
                $decoded['requiredMemory'],
                $decoded['requiredCpu'],
                $decoded['machineId']
            );

            $processes[] = $process;
        }

        return $processes;
    }

    public function delete(string $id): void
    {
        $this->redis->del("process:$id");
        $this->redis->srem("processes", $id);
    }
}
