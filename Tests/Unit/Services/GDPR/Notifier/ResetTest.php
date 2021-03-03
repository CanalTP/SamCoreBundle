<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\Notifier;

use Monolog\Logger;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Reset;
use CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\GdprTestCase;

class ResetTest extends GdprTestCase
{
    protected function setUp()
    {
        $this->initLogger();
    }

    public function testHandle()
    {
        $user = $this->mockUser(1, new \DateTime(), new \DateTime(), null, false, ['setDeletionDate']);
        $user
            ->expects($this->once())
            ->method('setDeletionDate')
            ->with($this->equalTo(null))
            ->willReturn(true);

        $notifier = new Reset(
            $this->mockObjectManager(1, 1),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $this->assertEquals(true, $notifier->handle($user));
        $this->assertNull($this->getUserDeletionDate($user));
        $this->assertLogMessageExists('deletion date has been unset', Logger::INFO);
    }

    public function testFlushImpossible()
    {
        $emMock = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods([
                'find',
                'persist',
                'remove',
                'merge',
                'clear',
                'detach',
                'refresh',
                'flush',
                'getRepository',
                'getClassMetadata',
                'getMetadataFactory',
                'initializeObject',
                'contains'
                ])
            ->getMock();

        $emMock
            ->method('persist')
            ->will($this->throwException(new \Exception('test')));

        $notifier = new Reset(
            $emMock,
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $user = $this->mockUser(1, null);
        $this->assertEquals(false, $notifier->handle($user));
        $this->assertLogMessageExists('test', Logger::ERROR);
    }
}
