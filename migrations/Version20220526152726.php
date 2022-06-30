<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220526152726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hero (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, combat_skill INT NOT NULL, endurance INT NOT NULL, end_max INT NOT NULL, gold INT DEFAULT NULL, page INT DEFAULT NULL, kai_dis LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', weapon LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', special_bag LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', backpack LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_51CE6E869D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kai_dis (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hero ADD CONSTRAINT FK_51CE6E869D86650F FOREIGN KEY (user_id_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE hero');
        $this->addSql('DROP TABLE kai_dis');
    }
}
