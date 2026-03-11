<?php

namespace App\repository;

use App\domain\Machine;
use App\infrastructure\RedisClient;

class MachineRepository
{
    private $redis;

    public function __construct(RedisClient $redisClient)
    {
        $this->redis = $redisClient->getClient();
    }

    public function save(Machine $machine): void
    {
        $key = "machine:" . $machine->getId();

        $data = [
            'id' => $machine->getId(),
            'totalMemory' => $machine->getTotalMemory(),
            'totalCpu' => $machine->getTotalCpu(),
            'usedMemory' => $machine->getUsedMemory(),
            'usedCpu' => $machine->getUsedCpu()
        ];

        $this->redis->set($key, json_encode($data));
        $this->redis->sadd("machines", [$machine->getId()]);
    }


    public function find(string $id): ?Machine
    {
        $data = $this->redis->get("machine:$id");

        if (!$data) {
            return null;
        }

        $decoded = json_decode($data, true);

        $machine = new Machine(
            $decoded['id'],
            $decoded['totalMemory'],
            $decoded['totalCpu']
        );

        $machine->setUsedResources(
            $decoded['usedMemory'],
            $decoded['usedCpu']
        );

        return $machine;
    }

    public function findAll(): array
    {
        $ids = $this->redis->smembers("machines");

        $machines = [];

        foreach ($ids as $id) {
            $machine = $this->find($id);

            if ($machine) {
                $machines[] = $machine;
            }
        }

        return $machines;
    }

    public function delete(string $id): void
    {
        $this->redis->del("machine:$id");
        $this->redis->srem("machines", $id);
    }
}
