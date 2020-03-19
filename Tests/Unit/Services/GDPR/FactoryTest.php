<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services;

use CanalTP\SamCoreBundle\Services\GDPR\Factory;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Nothing;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Reset;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Warning;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Deletion;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\SuperAdmin;

class FactoryTest extends GdprTestCase
{
    public function testConstants()
    {
        $this->assertEquals('5M', Factory::WARNING_AFTER);
        $this->assertEquals('6M', Factory::DELETE_AFTER);
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate($user, $expectedClass)
    {
        $notifier = Factory::create($user, $this->mockContainer());
        $this->assertInstanceOf($expectedClass, $notifier);
    }

    public function createDataProvider()
    {
        $now = new \DateTime();

        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->sub(new \DateInterval('P1M'));

        $oneMonthAfter = new \DateTime();
        $oneMonthAfter->add(new \DateInterval('P1M'));

        $sixMonthAgo = new \DateTime();
        $sixMonthAgo->sub(new \DateInterval('P6M'));

        $sevenMonthAgo = new \DateTime();
        $sevenMonthAgo->sub(new \DateInterval('P7M'));

        return [
            [$this->mockUser(0, null, null, true),  SuperAdmin::class],
            [$this->mockUser(1, $oneMonthAfter, null, true), SuperAdmin::class],
            [$this->mockUser(2, null, $now, true), SuperAdmin::class],
            [$this->mockUser(3, $oneMonthAfter, $now, true), SuperAdmin::class],
            [$this->mockUser(4, null, null), Warning::class],
            [$this->mockUser(5, null, $sixMonthAgo), Warning::class],
            [$this->mockUser(6, null, $sevenMonthAgo), Warning::class],
            [$this->mockUser(7, null, $oneMonthAgo), Nothing::class],
            [$this->mockUser(8, $oneMonthAgo, null), Deletion::class],
            [$this->mockUser(9, $sevenMonthAgo, null), Deletion::class],
            [$this->mockUser(10, $oneMonthAfter, null), Nothing::class],
            [$this->mockUser(11, $now, $sevenMonthAgo), Deletion::class],
            [$this->mockUser(12, $now, $oneMonthAgo), Reset::class],
        ];
    }

    protected function getSamGdprWarningNotifierMock()
    {
        return $this->getMockBuilder(Warning::class)->disableOriginalConstructor()->getMock();
    }

    protected function getSamGdprDeletionNotifierMock()
    {
        return $this->getMockBuilder(Deletion::class)->disableOriginalConstructor()->getMock();
    }

    protected function getSamGdprSuperadminNotifierMock()
    {
        return $this->getMockBuilder(SuperAdmin::class)->disableOriginalConstructor()->getMock();
    }

    protected function getSamGdprNothingNotifierMock()
    {
        return $this->getMockBuilder(Nothing::class)->disableOriginalConstructor()->getMock();
    }

    protected function getSamGdprResetNotifierMock()
    {
        return $this->getMockBuilder(Reset::class)->disableOriginalConstructor()->getMock();
    }
}
