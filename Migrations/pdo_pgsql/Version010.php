<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Adding timezone column in public.t_user_usr table
 */
class Version010 extends AbstractMigration
{
    const VERSION = '0.10.1';

    public function getName()
    {
        return self::VERSION;
    }

    /**
     * Adding timezone column into user table
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE public.t_user_usr ADD COLUMN usr_timezone varchar(255) NOT NULL DEFAULT 'Europe/Paris'");
    }

    /**
     * Drop timezone column from user table
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE public.t_user_usr DROP COLUMN usr_timezone");
    }
}
