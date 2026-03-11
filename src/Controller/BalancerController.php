<?php

namespace App\Controller;

use App\domain\Machine;
use App\repository\MachineRepository;
use App\repository\ProcessRepository;
use App\service\BalancerService;
use App\domain\Process;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BalancerController
{
    private MachineRepository $machineRepository;
    private BalancerService $balancerService;
    private ProcessRepository $processRepository;

    public function __construct(MachineRepository $machineRepository,
                                BalancerService   $balancerService, ProcessRepository $processRepository)
    {
        $this->machineRepository = $machineRepository;
        $this->balancerService = $balancerService;
        $this->processRepository = $processRepository;
    }

    #[Route('/machines', methods: ['POST'])]
    public function addMachine(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $machine = new Machine(
            uniqid(),
            $data['memory'],
            $data['cpu']
        );

        $this->machineRepository->save($machine);

        $this->balancerService->rebalance();

        return new JsonResponse([
            'status' => 'machine created',
            'id' => $machine->getId()
        ]);
    }

    #[Route('/machines', methods: ['GET'])]
    public function getMachines(): JsonResponse
    {
        $machines = $this->machineRepository->findAll();

        return new JsonResponse($machines);
    }


    #[Route('/processes', methods: ['POST'])]
    public function addProcess(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $process = new Process(
            uniqid(),
            $data['memory'],
            $data['cpu']
        );

        $this->balancerService->addProcess($process);

        return new JsonResponse([
            'status' => 'process created, rebalanced'
        ]);
    }


    #[Route('/state', methods: ['GET'])]
    public function getState(): JsonResponse
    {
        $machines = $this->machineRepository->findAll();
        $processes = $this->processRepository->findAll();

        $machineData = [];
        foreach ($machines as $machine) {

            $machineData[] = [
                'id' => $machine->getId(),
                'totalMemory' => $machine->getTotalMemory(),
                'totalCpu' => $machine->getTotalCpu(),
                'usedMemory' => $machine->getUsedMemory(),
                'usedCpu' => $machine->getUsedCpu(),
            ];
        }

        $processData = [];
        foreach ($processes as $process) {

            $processData[] = [
                'id' => $process->getId(),
                'memory' => $process->getRequiredMemory(),
                'cpu' => $process->getRequiredCpu(),
                'machineId' => $process->getMachineId(),
            ];
        }

        return new JsonResponse([
            'machines' => $machineData,
            'processes' => $processData
        ]);
    }



    #[Route('/processes/{id}', methods: ['DELETE'])]
    public function deleteProcess(string $id): JsonResponse
    {
        $this->processRepository->delete($id);

        $this->balancerService->rebalance();

        return new JsonResponse([
            'status' => 'process deleted'
        ]);
    }


    #[Route('/machines/{id}', methods: ['DELETE'])]
    public function deleteMachine(string $id): JsonResponse
    {
        $this->machineRepository->delete($id);

        $this->balancerService->rebalance();

        return new JsonResponse([
            'status' => 'machine deleted'
        ]);
    }
}
