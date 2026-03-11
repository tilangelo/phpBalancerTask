<?php

namespace App\service;

use App\domain\Process;
use App\repository\MachineRepository;
use App\repository\ProcessRepository;

class BalancerService
{
    private MachineRepository $machineRepository;
    private ProcessRepository $processRepository;

    public function __construct(MachineRepository $machineRepository,
                                ProcessRepository $processRepository) {
        $this->machineRepository = $machineRepository;
        $this->processRepository = $processRepository;
    }

    public function addProcess(Process $process): void
    {
        $this->processRepository->save($process);

        $this->rebalance();
    }


    public function rebalance(): void
    {
        $machines = $this->machineRepository->findAll();
        $processes = $this->processRepository->findAll();

        if (empty($machines)) {
            return;
        }

        // сбрасываю загрузку машин
        foreach ($machines as $machine) {
            $machine->setUsedResources(0, 0);
        }

        // сортирую процессы по нагрузке (memory + cpu)
        usort($processes, function ($a, $b) {
            $loadA = $a->getRequiredMemory() + $a->getRequiredCpu();
            $loadB = $b->getRequiredMemory() + $b->getRequiredCpu();

            return $loadB <=> $loadA;
        });

        // перераспределяю
        foreach ($processes as $process) {

            $bestMachine = null;
            $bestLoad = PHP_FLOAT_MAX;

            foreach ($machines as $machine) {

                if (!$machine->canRunProcess($process)) {
                    continue;
                }

                $load = $machine->getLoad();

                if ($load < $bestLoad) {
                    $bestLoad = $load;
                    $bestMachine = $machine;
                }
            }

            if ($bestMachine) {

                $bestMachine->assignProcess($process);
                $process->assignMachine($bestMachine->getId());

            } else {

                // процесс нельзя разместить
                $process->assignMachine(null);
            }
        }

        // сохраняю машины
        foreach ($machines as $machine) {
            $this->machineRepository->save($machine);
        }

        // сохраняю процессы
        foreach ($processes as $process) {
            $this->processRepository->save($process);
        }
    }
}
