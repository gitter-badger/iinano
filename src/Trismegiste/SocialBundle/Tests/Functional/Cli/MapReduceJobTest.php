<?php

/*
 * iinano
 */

namespace Trismegiste\SocialBundle\Tests\Functional\Cli;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Trismegiste\SocialBundle\Cli\MapReduceJob;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * MapReduceJobTest is a ...
 */
class MapReduceJobTest extends WebTestCase
{

    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new MapReduceJob());
        $command = $application->find('social:mr');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['command' => $command->getName(), 'classname' => 'RepeatCounter']);
        $this->assertRegExp('#index#', $commandTester->getDisplay());
    }

}
