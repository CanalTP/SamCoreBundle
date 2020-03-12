<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use CanalTP\SamCoreBundle\Services\Gdpr;

class GdprTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gdpr
     */
    private $gdpr;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var TestHandler
     */
    protected $logHandler;

    protected function setUp()
    {
        $this->initLogger();
        $this->gdpr = new Gdpr($this->mockEntityManager(), $this->logger);
    }

    /**
     * Stubs entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function mockEntityManager()
    {
        $mock = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    public function testRun()
    {
        $this->assertTrue($this->gdpr->run());
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
}
