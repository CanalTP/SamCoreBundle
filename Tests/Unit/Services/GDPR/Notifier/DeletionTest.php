<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\Notifier;

use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Deletion;
use CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\GdprTestCase;

class DeletionTest extends GdprTestCase
{
    protected function setUp()
    {
        $this->initLogger();
    }

    public function testHandleWithUsualUser()
    {
        $user = $this->mockUser(1, $this->generateDateInPast(), new \DateTime());

        $notifier = new Deletion(
            $this->mockObjectManager(0, 1),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $this->assertEquals(true, $notifier->handle($user));
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
            ->method('remove')
            ->will($this->throwException(new \Exception('test')));

        $notifier = new Deletion(
            $emMock,
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $user = $this->mockUser(1, $this->generateDateInFuture(), new \DateTime());
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

        $notifier = new Deletion(
            $this->mockObjectManager(0, 0),
            $this->logger,
            $this->mockTemplating(),
            $mailerMock,
            $this->mockTranslator()
        );

        $user = $this->mockUser(1, $this->generateDateInPast(), new \DateTime());
        $this->assertEquals(false, $notifier->handle($user));
    }

    private function generateDateInPast($pastInterval = '1M')
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P'.$pastInterval);

        $delDate = $now->sub($interval);

        return $delDate;
    }
}
