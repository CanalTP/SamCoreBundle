<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version006 extends AbstractMigration
{
    const VERSION = '0.0.6';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql('DROP INDEX cus_email_idx;');
        $this->addSql('ALTER TABLE tr_customer_cus ALTER COLUMN cus_email_canonical DROP not NULL;');
        $this->addSql('ALTER TABLE tr_customer_cus ALTER COLUMN cus_email DROP not NULL;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('CREATE UNIQUE INDEX cus_email_idx ON tr_customer_cus USING btree (cus_email_canonical COLLATE pg_catalog."default");');
        $this->addSql('ALTER TABLE tr_customer_cus ALTER COLUMN cus_email_canonical SET not NULL;');
        $this->addSql('ALTER TABLE tr_customer_cus ALTER COLUMN cus_email SET not NULL;');
    }
}
