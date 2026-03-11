<?php

namespace App\domain;

class Process
{
    private string $id;
    private int $requiredMemory;
    private int $requiredCpu;
    private ?string $machineId;

    public function __construct(
        string $id,
        int $requiredMemory,
        int $requiredCpu,
        ?string $machineId = null
    ) {
        $this->id = $id;
        $this->requiredMemory = $requiredMemory;
        $this->requiredCpu = $requiredCpu;
        $this->machineId = $machineId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRequiredMemory(): int
    {
        return $this->requiredMemory;
    }

    public function getRequiredCpu(): int
    {
        return $this->requiredCpu;
    }

    public function getMachineId(): ?string
    {
        return $this->machineId;
    }

    public function assignMachine(?string $machineId): void
    {
        $this->machineId = $machineId;
    }
}
