<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250122101157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE organization_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE organization_type_id_seq CASCADE');
        $this->addSql('CREATE TABLE company (id SERIAL NOT NULL, status_id INT NOT NULL, type_id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, phone_number VARCHAR(10) NOT NULL, email VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, siret VARCHAR(255) NOT NULL, tva_intra VARCHAR(255) NOT NULL, sales_revenue DOUBLE PRECISION DEFAULT NULL, effectif INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FBF094F6BF700BD ON company (status_id)');
        $this->addSql('CREATE INDEX IDX_4FBF094FC54C8C93 ON company (type_id)');
        $this->addSql('CREATE TABLE company_type (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F6BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FC54C8C93 FOREIGN KEY (type_id) REFERENCES company_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT fk_c1ee637c6bf700bd');
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT fk_c1ee637cc54c8c93');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE organization_type');
        $this->addSql('ALTER TABLE status ALTER involved_table SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE organization_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE organization_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE organization (id SERIAL NOT NULL, status_id INT NOT NULL, type_id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, phone_number VARCHAR(10) NOT NULL, email VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, siret VARCHAR(255) NOT NULL, tva_intra VARCHAR(255) NOT NULL, sales_revenue DOUBLE PRECISION DEFAULT NULL, effectif INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_c1ee637cc54c8c93 ON organization (type_id)');
        $this->addSql('CREATE INDEX idx_c1ee637c6bf700bd ON organization (status_id)');
        $this->addSql('CREATE TABLE organization_type (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT fk_c1ee637c6bf700bd FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT fk_c1ee637cc54c8c93 FOREIGN KEY (type_id) REFERENCES organization_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company DROP CONSTRAINT FK_4FBF094F6BF700BD');
        $this->addSql('ALTER TABLE company DROP CONSTRAINT FK_4FBF094FC54C8C93');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE company_type');
        $this->addSql('ALTER TABLE status ALTER involved_table DROP NOT NULL');
    }
}
