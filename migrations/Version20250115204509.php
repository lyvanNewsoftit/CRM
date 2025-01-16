<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115204509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE organisation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE organisation_type_id_seq CASCADE');
        $this->addSql('CREATE TABLE organization_type (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE organisation DROP CONSTRAINT fk_e6e132b4c54c8c93');
        $this->addSql('DROP TABLE organisation_type');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT fk_c1ee637c6bf700bd');
        $this->addSql('DROP INDEX idx_c1ee637c6bf700bd');
        $this->addSql('ALTER TABLE organization DROP status_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE organisation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE organisation_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE organisation_type (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE organisation (id SERIAL NOT NULL, type_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e6e132b4c54c8c93 ON organisation (type_id)');
        $this->addSql('ALTER TABLE organisation ADD CONSTRAINT fk_e6e132b4c54c8c93 FOREIGN KEY (type_id) REFERENCES organisation_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE organization_type');
        $this->addSql('ALTER TABLE organization ADD status_id INT NOT NULL');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT fk_c1ee637c6bf700bd FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c1ee637c6bf700bd ON organization (status_id)');
    }
}
