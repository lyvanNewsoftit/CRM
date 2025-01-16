<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115203344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organisation (id SERIAL NOT NULL, type_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E6E132B4C54C8C93 ON organisation (type_id)');
        $this->addSql('CREATE TABLE organisation_type (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE organisation ADD CONSTRAINT FK_E6E132B4C54C8C93 FOREIGN KEY (type_id) REFERENCES organisation_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE organisation DROP CONSTRAINT FK_E6E132B4C54C8C93');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE organisation_type');
    }
}
