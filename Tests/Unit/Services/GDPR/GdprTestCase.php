<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services;

use CanalTP\SamCoreBundle\Services\GDPR\DeletionNotifier;
use CanalTP\SamCoreBundle\Services\GDPR\WarningNotifier;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamCoreBundle\Entity\Customer;
use CanalTP\SamCoreBundle\Tests\Unit\UnitTestCase;

class GdprTestCase extends UnitTestCase
{
    protected function mockUser($id, $deletionDate)
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();

        $customer
            ->method('getName')
            ->willReturn('test');

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getDeletionDate', 'getCustomer', 'hasRole'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn($id);

        $user
            ->method('getDeletionDate')
            ->willReturn($deletionDate);

        $user
            ->method('getCustomer')
            ->willReturn($customer);

        $user
            ->method('hasRole')
            ->willReturnCallback([$this, 'userHasRole']);

        return $user;
    }

    protected function getSamGdprWarningNotifierMock()
    {
        $mock = $this->getMockBuilder(WarningNotifier::class)
            ->disableOriginalConstructor()
            ->setMethods(['handle'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('handle')
            ->willReturn(true);

        return $mock;
    }

    protected function getSamGdprDeletionNotifierMock()
    {
        $mock = $this->getMockBuilder(DeletionNotifier::class)
            ->disableOriginalConstructor()
            ->setMethods(['handle'])
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('handle')
            ->willReturn(true);

        return $mock;
    }

    protected function mockMailer()
    {
        $mailer = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();

        $mailer
            ->method('send')
            ->willReturn(true);

        return $mailer;
    }

    protected function generateDateInFuture($afterInterval = '1M')
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P' . $afterInterval);

        $delDate = $now->add($interval);

        return $delDate;
    }

    public function userHasRole($role)
    {
        return false;
    }
}
