<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220528123001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hero CHANGE weapon weapon INT DEFAULT NULL, CHANGE special_bag special_bag INT DEFAULT NULL, CHANGE backpack backpack INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hero CHANGE weapon weapon LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE special_bag special_bag LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE backpack backpack LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }
}
