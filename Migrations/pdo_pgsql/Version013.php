<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Adding / at the end of app_default_route if necessary
 */
class Version013 extends AbstractMigration
{
    const VERSION = '0.13.0';

    public function getName()
    {
        return self::VERSION;
    }

    /**
     * Adding / at the end of app_default_route if necessary
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql = 'UPDATE public.tr_application_app SET app_default_route=app_default_route || \'/\' ';
        $sql .= 'WHERE length(app_default_route) - length(replace(app_default_route, \'/\', \'\')) = 1 ';
        $sql .= 'AND right(app_default_route, 1) <> \'/\'';
        $this->addSql($sql);
    }

    /**
     * Delete / at the end of app_default_route
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $sql = 'UPDATE public.tr_application_app SET app_default_route=left(app_default_route, -1) ';
        $sql .= 'WHERE length(app_default_route) - length(replace(app_default_route, \'/\', \'\')) = 2 ';
        $sql .= 'AND right(app_default_route, 1) = \'/\'';
        $this->addSql($sql);
    }
}
