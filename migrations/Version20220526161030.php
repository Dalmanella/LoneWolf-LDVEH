<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220526161030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ennemy (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, combat_skill INT NOT NULL, endurance INT NOT NULL, mindforce TINYINT(1) DEFAULT NULL, mindshield TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE illustration (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(20) NOT NULL, src VARCHAR(255) NOT NULL, alt LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE objet (id INT AUTO_INCREMENT NOT NULL, id_illu_id INT DEFAULT NULL, obj_name VARCHAR(80) NOT NULL, obj_description LONGTEXT NOT NULL, w_tag SMALLINT DEFAULT NULL, i_tag SMALLINT DEFAULT NULL, effect SMALLINT DEFAULT NULL, obj_type VARCHAR(30) NOT NULL, place VARCHAR(30) NOT NULL, INDEX IDX_46CD4C38BBCA66D9 (id_illu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE objet ADD CONSTRAINT FK_46CD4C38BBCA66D9 FOREIGN KEY (id_illu_id) REFERENCES illustration (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE objet DROP FOREIGN KEY FK_46CD4C38BBCA66D9');
        $this->addSql('DROP TABLE ennemy');
        $this->addSql('DROP TABLE illustration');
        $this->addSql('DROP TABLE objet');
    }
}
