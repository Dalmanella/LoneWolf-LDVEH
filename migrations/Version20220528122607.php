<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220528122607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hero ADD kai_six TINYINT(1) DEFAULT NULL, ADD kai_track TINYINT(1) DEFAULT NULL, ADD kai_heal TINYINT(1) DEFAULT NULL, ADD kai_weapon TINYINT(1) DEFAULT NULL, ADD kai_mshield TINYINT(1) DEFAULT NULL, ADD kai_mblast TINYINT(1) NOT NULL, ADD kai_animal TINYINT(1) DEFAULT NULL, ADD kai_mo_m TINYINT(1) DEFAULT NULL, ADD kai_camou TINYINT(1) DEFAULT NULL, ADD kai_hunt TINYINT(1) DEFAULT NULL, DROP kai_dis');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hero ADD kai_dis LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', DROP kai_six, DROP kai_track, DROP kai_heal, DROP kai_weapon, DROP kai_mshield, DROP kai_mblast, DROP kai_animal, DROP kai_mo_m, DROP kai_camou, DROP kai_hunt');
    }
}
