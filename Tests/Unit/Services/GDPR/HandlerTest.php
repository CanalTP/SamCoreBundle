<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services;

use Monolog\Logger;
use CanalTP\SamCoreBundle\Services\GDPR\Handler as GdprHandler;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class HandlerTest extends GdprTestCase
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
            $this->mockEntityManager(0, 0),
            $this->logger,
            $this->mockContainer()
        );

        $affectedUsers = $this->gdprHandler->run();

        $this->assertEquals($expectedAffectedUsers, $affectedUsers);

        $expectedLogMessage = 'Found '. $expectedAffectedUsers .' inactive users';

        //check logs
        $this->assertLogMessageExists($expectedLogMessage, Logger::INFO);
    }

    public function runDataProvider()
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P1D');
        $delDate = $now->add($interval);

        return [
            [[],                                         0],
            [[$this->mockUser(1, null)], 1],
            [[$this->mockUser(1, $delDate)],         1],
        ];
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
            ->willReturn($this->users);

        return $mock;
    }
}
