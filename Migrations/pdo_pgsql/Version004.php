<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version004 extends AbstractMigration
{
    const VERSION = '0.0.4';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql('CREATE SEQUENCE public.t_perimeter_per_per_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->addSql('CREATE TABLE public.t_perimeter_per (per_id INT NOT NULL, cus_id INT DEFAULT NULL, per_external_coverage_id VARCHAR(255) NOT NULL, per_external_network_id VARCHAR(255) NOT NULL, PRIMARY KEY(per_id));');
        $this->addSql('CREATE INDEX IDX_6B5760DABC4EE2B0 ON public.t_perimeter_per (cus_id);');
        $this->addSql('ALTER TABLE public.t_perimeter_per ADD CONSTRAINT FK_6B5760DABC4EE2B0 FOREIGN KEY (cus_id) REFERENCES public.tr_customer_cus (cus_id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->addSql('CREATE UNIQUE INDEX customer_id_external_coverage_id_external_network_id_idx ON public.t_perimeter_per (cus_id, per_external_coverage_id, per_external_network_id);');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP INDEX IDX_6B5760DABC4EE2B0;');
        $this->addSql('ALTER TABLE public.t_perimeter_per DROP CONSTRAINT FK_6B5760DABC4EE2B0;');
        $this->addSql('DROP SEQUENCE public.t_perimeter_per_per_id_seq;');
        $this->addSql('DROP TABLE public.t_perimeter_per CASCADE;');
    }
}
