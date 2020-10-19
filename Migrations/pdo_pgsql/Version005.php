<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version005 extends AbstractMigration
{
    const VERSION = '0.0.5';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql('DROP TABLE public.tj_customer_application_cap CASCADE;');
        $this->addSql('CREATE SEQUENCE public.tj_customer_application_cap_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->addSql('CREATE TABLE public.tj_customer_application_cap (cap_id INT NOT NULL, customer_id INT DEFAULT NULL, application_id INT DEFAULT NULL, cap_token VARCHAR(255) NOT NULL, cap_is_active BOOLEAN NOT NULL, cap_created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cap_updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(cap_id));');
        $this->addSql('ALTER TABLE public.tj_customer_application_cap ADD CONSTRAINT FK_9425A57F3E030ACD FOREIGN KEY (application_id) REFERENCES public.tr_application_app (app_id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->addSql('ALTER TABLE public.tj_customer_application_cap ADD CONSTRAINT FK_9425A57F19EB6921 FOREIGN KEY (customer_id) REFERENCES public.tr_customer_cus (cus_id) NOT DEFERRABLE INITIALLY IMMEDIATE;');

        $this->addSql('CREATE INDEX IDX_9425A57F19EB6921 ON public.tj_customer_application_cap (customer_id);');
        $this->addSql('CREATE INDEX IDX_9425A57F3E030ACD ON public.tj_customer_application_cap (application_id);');

        $this->addSql('CREATE SEQUENCE public.tj_customer_application_cap_cap_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');

        $this->addSql('ALTER TABLE public.tr_customer_cus ADD COLUMN cus_email VARCHAR(255) NOT NULL;');
        $this->addSql('ALTER TABLE public.tr_customer_cus ADD COLUMN cus_email_canonical VARCHAR(255) NOT NULL;');
        $this->addSql('CREATE UNIQUE INDEX cus_email_idx ON public.tr_customer_cus (cus_email_canonical);');
        $this->addSql('ALTER TABLE public.tr_customer_cus DROP COLUMN cus_navitia_token;');

        $this->addSql('ALTER TABLE public.t_user_usr ADD COLUMN usr_status INT DEFAULT 0;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP SEQUENCE public.tj_customer_application_cap_id_seq');
        $this->addSql('DROP SEQUENCE public.tj_customer_application_cap_cap_id_seq');

        $this->addSql('DROP INDEX public.IDX_9425A57F19EB6921');
        $this->addSql('DROP INDEX public.IDX_9425A57F3E030ACD');
        $this->addSql('ALTER TABLE public.tj_customer_application_cap DROP CONSTRAINT FK_9425A57F3E030ACD;');
        $this->addSql('ALTER TABLE public.tj_customer_application_cap DROP CONSTRAINT FK_9425A57F19EB6921;');
        $this->addSql('DROP TABLE public.tj_customer_application_cap CASCADE;');
        $this->addSql('CREATE TABLE public.tj_customer_application_cap (cus_id INT NOT NULL, app_id INT NOT NULL, PRIMARY KEY(cus_id, app_id));');
        $this->addSql('CREATE INDEX IDX_9425A57FBC4EE2B0 ON public.tj_customer_application_cap (cus_id);');
        $this->addSql('CREATE INDEX IDX_9425A57F7987212D ON public.tj_customer_application_cap (app_id);');
        $this->addSql('ALTER TABLE public.tj_customer_application_cap ADD CONSTRAINT FK_9425A57FBC4EE2B0 FOREIGN KEY (cus_id) REFERENCES public.tr_customer_cus (cus_id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
        $this->addSql('ALTER TABLE public.tj_customer_application_cap ADD CONSTRAINT FK_9425A57F7987212D FOREIGN KEY (app_id) REFERENCES public.tr_application_app (app_id) NOT DEFERRABLE INITIALLY IMMEDIATE;');


        $this->addSql('DROP INDEX cus_email_idx;');
        $this->addSql('ALTER TABLE public.tr_customer_cus DROP COLUMN cus_email;');
        $this->addSql('ALTER TABLE public.tr_customer_cus DROP COLUMN cus_email_canonical;');
        $this->addSql('ALTER TABLE public.tr_customer_cus ADD COLUMN cus_navitia_token VARCHAR(255) DEFAULT \'\';');

        $this->addSql('ALTER TABLE public.t_user_usr DROP COLUMN usr_status;');
    }
}
