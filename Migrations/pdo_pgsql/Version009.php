<?php

namespace CanalTP\SamCoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version009 extends AbstractMigration
{
    const VERSION = '0.0.9';

    public function getName()
    {
        return self::VERSION;
    }

    public function postUp(Schema $schema)
    {
        $statement = $this->connection->prepare("SELECT cus.cus_id, cus.cus_name_canonical FROM tr_customer_cus AS cus");
        $statement->execute();
        $customers = $statement->fetchAll();

        foreach ($customers as $customer) {
            $statement = $this->connection->prepare("UPDATE tr_customer_cus SET cus_identifier = :cus_identifier WHERE cus_id = :cus_id");
            $statement->bindValue('cus_identifier', $customer['cus_name_canonical']);
            $statement->bindValue('cus_id', $customer['cus_id']);
            $statement->execute();
        }
    }

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE tr_customer_cus ADD cus_identifier VARCHAR(255);');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE tr_customer_cus DROP cus_identifier;');
    }
}
