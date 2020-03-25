<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services;

use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Nothing;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Deletion;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Warning;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamCoreBundle\Entity\Customer;
use CanalTP\SamCoreBundle\Tests\Unit\UnitTestCase;

class GdprTestCase extends UnitTestCase
{
    protected function mockUser($id, $deletionDate, $lastLoginDate = null, $isSuperAdmin = false, $mockMethods = [])
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();

        $customer
            ->method('getName')
            ->willReturn('test');

        $methods = array_merge(['getId', 'getLastLogin', 'getDeletionDate', 'getCustomer', 'hasRole'], $mockMethods);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        $user
            ->method('getId')
            ->willReturn($id);

        $user
            ->method('getLastLogin')
            ->willReturn($lastLoginDate);

        $user
            ->method('getDeletionDate')
            ->willReturn($deletionDate);

        $user
            ->method('getCustomer')
            ->willReturn($customer);

        $user
            ->method('hasRole')
            ->willReturnCallback(function ($role) use ($isSuperAdmin) {
                return $role === 'ROLE_SUPER_ADMIN' && $isSuperAdmin;
            });

        return $user;
    }

    protected function getUserDeletionDate($user)
    {
        $reflectionClass = new \ReflectionClass(User::class);
        $reflectionProperty = $reflectionClass->getProperty('deletionDate');
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($user);
    }

    protected function getSamGdprWarningNotifierMock()
    {
        return $this->getNotifierMock(Warning::class);
    }

    protected function getSamGdprNothingNotifierMock()
    {
        return $this->getNotifierMock(Nothing::class);
    }

    protected function getSamGdprDeletionNotifierMock()
    {
        return $this->getNotifierMock(Deletion::class);
    }

    private function getNotifierMock($class)
    {
        $mock = $this->getMockBuilder($class)
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

    /**
     * Mocks translation object
     *
     * @return Symfony\Component\Translation\TranslatorInterface
     */
    protected function mockTranslator()
    {
        $mock = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
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
