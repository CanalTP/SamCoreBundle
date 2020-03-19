<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\Notifier;

use Monolog\Logger;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Reset;
use CanalTP\SamCoreBundle\Tests\Unit\Services\GdprTestCase;

class ResetTest extends GdprTestCase
{
    protected function setUp()
    {
        $this->initLogger();
    }

    public function testHandle()
    {
        $user = $this->mockUser(1, new \DateTime());

        $notifier = new Reset(
            $this->mockEntityManager(1, 1),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer()
        );

        $this->assertEquals(true, $notifier->handle($user));
        $this->assertNull($this->getUserDeletionDate($user));
        $this->assertLogMessageExists('deletion date has been unset', Logger::INFO);
    }

    public function testFlushImpossible()
    {
        $emMock = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['persist'])
            ->getMock();

        $emMock
            ->method('persist')
            ->will($this->throwException(new \Exception('test')));

        $notifier = new Reset($emMock, $this->logger, $this->mockTemplating(), $this->mockMailer());

        $user = $this->mockUser(1, null);
        $this->assertEquals(false, $notifier->handle($user));
        $this->assertLogMessageExists('test', Logger::ERROR);
    }
}
