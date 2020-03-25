<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version013 extends AbstractMigration
{
    const VERSION = '0.13.0';

    public function getName()
    {
        return self::VERSION;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE public.t_user_usr ALTER COLUMN cus_id SET NOT NULL');
        $this->addSql('ALTER TABLE public.t_user_usr ADD CONSTRAINT FK_9525B57F19EC6921 FOREIGN KEY (cus_id) REFERENCES public.tr_customer_cus (cus_id) ON UPDATE CASCADE ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE public.t_user_usr DROP CONSTRAINT FK_9525B57F19EC6921');
        $this->addSql('ALTER TABLE public.t_user_usr ALTER COLUMN cus_id DROP NOT NULL');
    }
}
