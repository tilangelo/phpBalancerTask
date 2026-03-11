<?php

namespace App\domain;

class Machine
{
    private string $id;
    private int $totalMemory;
    private int $totalCpu;

    private int $usedMemory = 0;
    private int $usedCpu = 0;

    public function __construct(
        string $id,
        int $totalMemory,
        int $totalCpu
    ) {
        $this->id = $id;
        $this->totalMemory = $totalMemory;
        $this->totalCpu = $totalCpu;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTotalMemory(): int
    {
        return $this->totalMemory;
    }

    public function getTotalCpu(): int
    {
        return $this->totalCpu;
    }

    public function getUsedMemory(): int
    {
        return $this->usedMemory;
    }

    public function getUsedCpu(): int
    {
        return $this->usedCpu;
    }



    public function setUsedResources(int $memory, int $cpu): void
    {
        $this->usedMemory = $memory;
        $this->usedCpu = $cpu;
    }


    public function canRunProcess(Process $process): bool
    {
        return
            ($this->usedMemory + $process->getRequiredMemory() <= $this->totalMemory)
            &&
            ($this->usedCpu + $process->getRequiredCpu() <= $this->totalCpu);
    }

    public function assignProcess(Process $process): void
    {
        $this->usedMemory += $process->getRequiredMemory();
        $this->usedCpu += $process->getRequiredCpu();
    }

    public function getLoad(): float
    {
        $memoryLoad = $this->usedMemory / $this->totalMemory;
        $cpuLoad = $this->usedCpu / $this->totalCpu;

        return max($memoryLoad, $cpuLoad);
    }
}
