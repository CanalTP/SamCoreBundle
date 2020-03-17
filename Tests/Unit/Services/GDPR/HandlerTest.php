<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services;

use Monolog\Logger;
use CanalTP\SamCoreBundle\Services\GDPR\Handler as GdprHandler;
use CanalTP\SamCoreBundle\Entity\Customer;
use CanalTP\SamCoreBundle\Tests\Unit\UnitTestCase;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamCoreBundle\Services\GDPR\WarningNotifier;

class HandlerTest extends UnitTestCase
{
    /**
     * @var GdprHandler
     */
    private $gdprHandler;

    /**
     * @var array
     */
    private $users;

    protected function setUp()
    {
        $this->initLogger();
    }

    public function testConstant()
    {
        $this->assertEquals('5D', GdprHandler::INACTIVITY_INTERVAL);
    }
    /**
     * @param array $users
     * @param int $expectedAffectedUsers
     *
     * @dataProvider runDataProvider
     *
     */
    public function testRun($users, $expectedAffectedUsers)
    {
        $this->users = $users;

        $this->gdprHandler = new GdprHandler(
            $this->mockEntityManager(0),
            $this->logger,
            $this->mockContainer()
        );

        $affectedUsers = $this->gdprHandler->run();

        $this->assertEquals($expectedAffectedUsers, $affectedUsers);

        $expectedLogMessage = 'Found '. $expectedAffectedUsers .' inactive users';

        //check logs
        $this->assertLogMessageExists($expectedLogMessage, Logger::INFO);
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

    public function runDataProvider()
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P1D');
        $delDate = $now->add($interval);

        return [
            [[],                                            0],
            [[$this->mockUser(1, null)],     1],
        ];
    }

    private function mockUser($id, $deletionDate)
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
            ->setMethods(['getId', 'getDeletionDate', 'getCustomer'])
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

        return $user;
    }

    public function getCanalTPSamEcoreUserManagerBundleUserMock()
    {
        $mock = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(['getIncativeUsersSince'])
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getIncativeUsersSince')
            ->willReturn(
                $this->users
            );

        return $mock;
    }
}
