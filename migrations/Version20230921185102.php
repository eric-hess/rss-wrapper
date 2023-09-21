<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230921185102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manipulator (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, wrapper_id BLOB NOT NULL --(DC2Type:uuid)
        , type VARCHAR(255) NOT NULL, field VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, "action" VARCHAR(255) NOT NULL, CONSTRAINT FK_F2AE05BCDBA5EC03 FOREIGN KEY (wrapper_id) REFERENCES wrapper (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F2AE05BCDBA5EC03 ON manipulator (wrapper_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE TABLE wrapper (id BLOB NOT NULL --(DC2Type:uuid)
        , user_id INTEGER NOT NULL, feed VARCHAR(255) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_CF5484D2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CF5484D2A76ED395 ON wrapper (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE manipulator');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE wrapper');
    }
}
