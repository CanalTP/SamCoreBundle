<?php

namespace CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\Notifier;

use Monolog\Logger;
use CanalTP\SamCoreBundle\Services\GDPR\Notifier\SuperAdmin;
use CanalTP\SamCoreBundle\Tests\Unit\Services\GDPR\GdprTestCase;

class SuperAdminTest extends GdprTestCase
{
    protected function setUp()
    {
        $this->initLogger();
    }

    public function testHandle()
    {
        $user = $this->mockUser(1, null);

        $notifier = new SuperAdmin(
            $this->mockObjectManager(0, 0),
            $this->logger,
            $this->mockTemplating(),
            $this->mockMailer(),
            $this->mockTranslator()
        );

        $this->assertEquals(false, $notifier->handle($user));
        $this->assertLogMessageExists('no action, user is super admin', Logger::INFO);
    }
}
