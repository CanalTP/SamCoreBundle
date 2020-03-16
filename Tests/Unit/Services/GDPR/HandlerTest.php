<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use CanalTP\SamCoreBundle\Services\GDPR\Handler as GdprHandler;
use CanalTP\SamCoreBundle\Entity\Customer;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GdprHandler
     */
    private $gdprHandler;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var TestHandler
     */
    protected $logHandler;

    /**
     * @var array
     */
    private $users;

    protected function setUp()
    {
        $this->initLogger();
    }

    /**
     * @param array $users
     * @param int $nbUpdatedRecords
     * @param array $expectedDelDates
     * @param array $expectedLogMessages
     *
     * @dataProvider runDataProvider
     *
     */
    public function testRun($users, $nbUpdatedRecords, $expectedDelDates, $expectedLogMessages)
    {
        $this->users = $users;
        $this->gdprHandler = new GdprHandler(
            $this->mockEntityManager($nbUpdatedRecords),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer()
        );

        $this->gdprHandler->run();

        //check logs
        foreach ($expectedLogMessages as $expectedLogMessage) {
            $this->assertLogMessageExists($expectedLogMessage['msg'], $expectedLogMessage['level']);
        }

        //check that user deletion date is altered
        foreach ($this->users as $user) {
            $this->assertArrayHasKey($user->getId(), $expectedDelDates);

            $this->assertEquals(
                $this->getUserDeletionDate($user)->format('Y-m-d h:'),
                $expectedDelDates[$user->getId()]->format('Y-m-d h:')
            );
        }
    }

    public function runDataProvider()
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P1D');
        $delDate = $now->add($interval);

        return [
            [
                [],
                0,
                [],
                [
                    ['msg' => 'Found 0 inactive users', 'level' => Logger::INFO]
                ]
            ],
            [
                [
                    $this->mockUser(1, null)
                ],
                1,
                [1 => $delDate],
                [
                    [
                        'msg' => 'Found 1 inactive users',
                        'level' => Logger::INFO
                    ],
                    [
                        'msg' => 'Client test: User ID 1: deletion date has been set to ' . $delDate->format('Y-m-d'),
                        'level' => Logger::INFO
                    ],
                    [
                        'msg' => 'Client test: User ID 1: alert email has been sent',
                        'level' => Logger::INFO
                    ],
                ]
            ],
        ];
    }

    private function getUserDeletionDate($user)
    {
        $property = new \ReflectionProperty(get_class($user), 'deletionDate');
        $property->setAccessible(true);

        return $property->getValue($user);
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

    /**
     * Stubs entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function mockEntityManager($nbUpdatedRecords)
    {
        $mock = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['persist', 'flush', 'getRepository'])
            ->getMock();

        $mock
            ->method('getRepository')
            ->willReturnCallback([$this, 'getRepositoryMock']);

        $mock
            ->expects($this->exactly($nbUpdatedRecords))
            ->method('persist');

        $mock
            ->expects($this->exactly($nbUpdatedRecords))
            ->method('flush');

        return $mock;
    }

    /**
     * Initializes PSR logger
     */
    protected function initLogger()
    {
        $this->logger = new Logger('gdpr');
        $this->logHandler = new TestHandler();
        $this->logger->pushHandler($this->logHandler);
    }

    /**
     * Asserts that message is logged
     *
     * @param string $message log message
     * @param integer $level log level
     */
    protected function assertLogMessageExists($message, $level)
    {
        $recordIsFound = $this->logHandler->hasRecordThatContains($message, $level);
        $this->assertTrue($recordIsFound, $message . ' with level ' . $level . ' could not found in logger');
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

    protected function mockTemplating()
    {
        $mailer = $this->getMockBuilder('\Symfony\Bundle\TwigBundle\TwigEngine')
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $mailer
            ->method('render')
            ->willReturn('abcd');

        return $mailer;
    }

    /**
     * Retrieves correct mock object for $repositoryName
     *
     * @param string $repositoryName
     * @param array $args
     * @return mixed
     */
    public function getRepositoryMock($repositoryName)
    {
        $methodName = 'get' . str_replace([':', '\\'], '', $repositoryName) . 'Mock';

        return $this->$methodName();
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
