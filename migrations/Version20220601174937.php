<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220601174937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE backpack (id INT AUTO_INCREMENT NOT NULL, hero_id_id INT NOT NULL, UNIQUE INDEX UNIQ_C358569E07B9190 (hero_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE backpack_objet (backpack_id INT NOT NULL, objet_id INT NOT NULL, INDEX IDX_54E7302331009DBE (backpack_id), INDEX IDX_54E73023F520CF5A (objet_id), PRIMARY KEY(backpack_id, objet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE backpack ADD CONSTRAINT FK_C358569E07B9190 FOREIGN KEY (hero_id_id) REFERENCES hero (id)');
        $this->addSql('ALTER TABLE backpack_objet ADD CONSTRAINT FK_54E7302331009DBE FOREIGN KEY (backpack_id) REFERENCES backpack (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE backpack_objet ADD CONSTRAINT FK_54E73023F520CF5A FOREIGN KEY (objet_id) REFERENCES objet (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE backpack_objet DROP FOREIGN KEY FK_54E7302331009DBE');
        $this->addSql('DROP TABLE backpack');
        $this->addSql('DROP TABLE backpack_objet');
    }
}
