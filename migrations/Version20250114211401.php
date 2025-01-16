<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250114211401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organization (id SERIAL NOT NULL, status_id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, phone_number VARCHAR(10) NOT NULL, email VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, siret VARCHAR(255) NOT NULL, tva_intra VARCHAR(255) NOT NULL, sales_revenue DOUBLE PRECISION DEFAULT NULL, effectif INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C1EE637C6BF700BD ON organization (status_id)');
        $this->addSql('CREATE TABLE status (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637C6BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT FK_C1EE637C6BF700BD');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE status');
    }
}
