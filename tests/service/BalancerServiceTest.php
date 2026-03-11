<?php

namespace App\Tests\service;

use PHPUnit\Framework\TestCase;
use App\domain\Machine;
use App\domain\Process;

class BalancerServiceTest extends TestCase
{
    public function testMachineCanRunProcess()
    {
        $machine = new Machine("1", 1000, 10);

        $process = new Process("p1", 200, 2);

        $this->assertTrue($machine->canRunProcess($process));
    }

    public function testMachineRejectsTooBigProcess()
    {
        $machine = new Machine("1", 1000, 10);

        $process = new Process("p1", 2000, 20);

        $this->assertFalse($machine->canRunProcess($process));
    }
}
