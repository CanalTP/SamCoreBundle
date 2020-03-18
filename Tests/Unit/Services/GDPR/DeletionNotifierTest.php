<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR;

use CanalTP\SamCoreBundle\Services\GDPR\DeletionNotifier;
use CanalTP\SamCoreBundle\Tests\Unit\Services\GdprTestCase;

class DeletionNotifierTest extends GdprTestCase
{
    private $userIsSuperAdmin = false;

    protected function setUp()
    {
        $this->initLogger();
    }

    public function testConstants()
    {
        $this->assertEquals('1M', DeletionNotifier::DELETING_AFTER);
    }

    public function testHandleWithSuperAdmin()
    {
        $this->userIsSuperAdmin = true;
        $user = $this->mockUser(1, $this->generateDateInFuture());

        $notifier = new DeletionNotifier(
            $this->mockEntityManager(0, 0),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer()
        );

        $this->assertEquals(false, $notifier->handle($user));
    }

    public function testHandleWithUsualUser()
    {
        $user = $this->mockUser(1, $this->generateDateInPast());

        $notifier = new DeletionNotifier(
            $this->mockEntityManager(0, 1),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer()
        );

        $this->assertEquals(true, $notifier->handle($user));
    }

    public function testFlushImpossible()
    {
        $emMock = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['remove'])
            ->getMock();

        $emMock
            ->method('remove')
            ->will($this->throwException(new \Exception('test')));

        $notifier = new DeletionNotifier($emMock, $this->logger, $this->mockTemplating(), $this->mockMailer());

        $user = $this->mockUser(1, $this->generateDateInFuture());
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

        $notifier = new DeletionNotifier(
            $this->mockEntityManager(0, 0),
            $this->logger,
            $this->mockTemplating(),
            $mailerMock
        );

        $user = $this->mockUser(1, $this->generateDateInPast());
        $this->assertEquals(false, $notifier->handle($user));
    }

    public function userHasRole($role)
    {
        return $role === 'ROLE_SUPER_ADMIN' && $this->userIsSuperAdmin;
    }

    private function generateDateInPast($pastInterval = '1M')
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P'.$pastInterval);

        $delDate = $now->sub($interval);

        return $delDate;
    }
}
