<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version003 extends AbstractMigration
{
    const VERSION = '0.0.3';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql('UPDATE tr_application_app SET app_canonical_name=\'samcore\', app_name=\'SamCore\' where app_canonical_name=\'sam\'');
        $this->addSql('CREATE TABLE public.tr_customer_cus (cus_id SERIAL NOT NULL, cus_name VARCHAR(255) NOT NULL, cus_name_canonical VARCHAR(255) NOT NULL, cus_logo_path VARCHAR(255) DEFAULT NULL, cus_locked BOOLEAN NOT NULL, cus_navitia_token VARCHAR(255) DEFAULT \'\', cus_created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cus_updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(cus_id));');

        $this->addSql('CREATE UNIQUE INDEX name_idx ON public.tr_customer_cus (cus_name_canonical);');
        $this->addSql('CREATE TABLE public.tj_customer_application_cap (cus_id INT NOT NULL, app_id INT NOT NULL, PRIMARY KEY(cus_id, app_id));');
        $this->addSql('CREATE INDEX IDX_9425A57FBC4EE2B0 ON public.tj_customer_application_cap (cus_id);');
        $this->addSql('CREATE INDEX IDX_9425A57F7987212D ON public.tj_customer_application_cap (app_id);');

        $this->addSql('ALTER TABLE public.t_user_usr ADD COLUMN cus_id INT DEFAULT NULL;');
        $this->addSql('CREATE INDEX IDX_8DAB2D03BC4EE2B0 ON public.t_user_usr (cus_id);');
        $this->addSql('ALTER TABLE public.tj_customer_application_cap ADD CONSTRAINT FK_9425A57FBC4EE2B0 FOREIGN KEY (cus_id) REFERENCES public.tr_customer_cus (cus_id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->addSql('ALTER TABLE public.tj_customer_application_cap ADD CONSTRAINT FK_9425A57F7987212D FOREIGN KEY (app_id) REFERENCES public.tr_application_app (app_id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('UPDATE tr_application_app SET app_canonical_name=\'sam\' where app_canonical_name=\'samcore\'');
        $this->addSql('DROP TABLE public.tr_customer_cus CASCADE;');

        $this->addSql('DROP INDEX IDX_9425A57F7987212D;');
        $this->addSql('DROP INDEX IDX_9425A57FBC4EE2B0;');
        $this->addSql('DROP TABLE public.tj_customer_application_cap CASCADE;');

        $this->addSql('DROP INDEX IDX_8DAB2D03BC4EE2B0;');
        $this->addSql('ALTER TABLE public.t_user_usr DROP COLUMN cus_id;');
    }
}
