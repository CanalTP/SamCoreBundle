<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version007 extends AbstractMigration
{
    const VERSION = '0.0.7';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE t_user_usr DROP IF EXISTS usr_expires_at;');
        $this->addSql('ALTER TABLE t_user_usr ADD COLUMN usr_expires_at timestamp(0) without time zone;');
        $this->addSql('ALTER TABLE t_user_usr ALTER COLUMN usr_last_login SET DEFAULT NULL::timestamp without time zone;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE t_user_usr DROP IF EXISTS usr_expires_at;');
        $this->addSql('ALTER TABLE t_user_usr ADD COLUMN usr_expires_at boolean;');
    }
}
