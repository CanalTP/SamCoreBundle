<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\Notifier;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Warning;
use CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\GdprTestCase;

class WarningTest extends GdprTestCase
{
    private $userIsSuperAdmin = false;

    protected function setUp()
    {
        $this->initLogger();
    }

    public function testConstants()
    {
        $this->assertEquals('1M', Warning::DELETING_AFTER);
    }

    public function testHandleWithUsualUser()
    {
        $user = $this->mockUser(1, null);

        $notifier = new Warning(
            $this->mockObjectManager(1, 1),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $this->assertEquals(true, $notifier->handle($user));

        $expectedDeletionDate = $this->generateDateInFuture();

        $this->assertEquals(
            $expectedDeletionDate->format('Y-m-d H:i'),
            $this->getUserDeletionDate($user)->format('Y-m-d H:i')
        );
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
            ->expects($this->exactly(1))
            ->method('persist');
        $emMock
            ->method('flush')
            ->will($this->throwException(new \Exception('test')));

        $notifier = new Warning(
            $emMock,
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $user = $this->mockUser(1, null);
        $this->assertEquals(false, $notifier->handle($user));
    }

    public function testEmailSendingFails()
    {
        $mailerMock = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();

        $mailerMock
            ->method('send')
            ->willReturn(0);

        $notifier = new Warning(
            $this->mockObjectManager(0, 0),
            $this->logger,
            $this->mockTemplating(),
            $mailerMock,
            $this->mockTranslator()
        );

        $user = $this->mockUser(1, null);
        $this->assertEquals(false, $notifier->handle($user));
    }
}
