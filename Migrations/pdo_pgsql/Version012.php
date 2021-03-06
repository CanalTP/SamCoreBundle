<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Adding created_at column in public.t_user_usr table
 */
class Version012 extends AbstractMigration
{
    const VERSION = '0.12.0';

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
        $sql = 'ALTER TABLE public.t_user_usr ';
        $sql.= 'ADD COLUMN usr_deletion_date timestamp(0) without time zone DEFAULT NULL::timestamp without time zone';

        $this->addSql($sql);
        $this->addSql('comment on column public.t_user_usr.usr_deletion_date is \'Date when this record will be deleted (GDPR)\';');
    }

    /**
     * Drop timezone column from user table
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE public.t_user_usr DROP COLUMN usr_deletion_date');
    }
}
