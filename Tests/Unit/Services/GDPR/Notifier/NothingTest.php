<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\Notifier;

use Monolog\Logger;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\Nothing;
use CanalTP\SamCoreBundle\Tests\Unit\Services\GdprTestCase;

class NothingTest extends GdprTestCase
{
    protected function setUp()
    {
        $this->initLogger();
    }

    public function testHandleWithUsualUser()
    {
        $user = $this->mockUser(1, null);

        $notifier = new Nothing(
            $this->mockEntityManager(0, 0),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $this->assertEquals(false, $notifier->handle($user));
        $this->assertLogMessageExists('no action', Logger::INFO);
    }

    public function testHandleUserWithoutCustomer()
    {
        $user = $this->mockUserWithoutCustomer(1, null);

        $notifier = new Nothing(
            $this->mockEntityManager(0, 0),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $this->assertEquals(false, $notifier->handle($user));

        $this->assertLogMessageExists('Client not found', Logger::INFO);
        $this->assertLogMessageExists('no action', Logger::INFO);
    }
}
