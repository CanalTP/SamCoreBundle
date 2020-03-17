<?php

namespace CanalTP\SamCoreBundle\Tests\Unit;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TestHandler
     */
    protected $logHandler;

    /**
     * Initializes PSR logger
     */
    protected function initLogger()
    {
        $this->logger = new Logger('test');
        $this->logHandler = new TestHandler();
        $this->logger->pushHandler($this->logHandler);
    }

    /**
     * Mocks container
     *
     * @return Container
     */
    protected function mockContainer()
    {
        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $container
            ->method('get')
            ->willReturnCallback([$this, 'getMockService']);

        return $container;
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

    public function getMockService($serviceAlias, $arg)
    {
        $name = str_replace(' ', '', ucwords(str_replace([':', '\\', '.'], ' ', $serviceAlias)));
        $methodName = 'get' . $name . 'Mock';

        return $this->$methodName();
    }

    protected function getGdprWarningNotifier()
    {
        return new \stdClass();
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
}
