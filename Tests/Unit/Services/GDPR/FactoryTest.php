<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services;

use CanalTP\SamCoreBundle\Services\GDPR\DeletionNotifier;
use CanalTP\SamCoreBundle\Services\GDPR\WarningNotifier;
use CanalTP\SamCoreBundle\Services\GDPR\Factory;

class FactoryTest extends GdprTestCase
{
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
        $interval = new \DateInterval('P1D');
        $delDate = $now->add($interval);

        return [
            [$this->mockUser(1, null), WarningNotifier::class],
            [$this->mockUser(1, $delDate), DeletionNotifier::class],
        ];
    }

    protected function getSamGdprWarningNotifierMock()
    {
        return $this->getMockBuilder(WarningNotifier::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getSamGdprDeletionNotifierMock()
    {
        return $this->getMockBuilder(DeletionNotifier::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
