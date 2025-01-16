<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115204658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organization ADD status_id INT NOT NULL');
        $this->addSql('ALTER TABLE organization ADD type_id INT NOT NULL');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637C6BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637CC54C8C93 FOREIGN KEY (type_id) REFERENCES organization_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C1EE637C6BF700BD ON organization (status_id)');
        $this->addSql('CREATE INDEX IDX_C1EE637CC54C8C93 ON organization (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT FK_C1EE637C6BF700BD');
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT FK_C1EE637CC54C8C93');
        $this->addSql('DROP INDEX IDX_C1EE637C6BF700BD');
        $this->addSql('DROP INDEX IDX_C1EE637CC54C8C93');
        $this->addSql('ALTER TABLE organization DROP status_id');
        $this->addSql('ALTER TABLE organization DROP type_id');
    }
}
