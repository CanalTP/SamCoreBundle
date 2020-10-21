<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version008 extends AbstractMigration
{
    const VERSION = '0.0.8';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE tr_application_app ADD COLUMN app_bundle_name VARCHAR(255);');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE tr_application_app DROP COLUMN app_bundle_name;');
    }
}
