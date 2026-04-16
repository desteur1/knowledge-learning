<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416173248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cursus (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, theme_id INT NOT NULL, INDEX IDX_255A0C359027487 (theme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, video_url VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, cursus_id INT NOT NULL, INDEX IDX_F87474F340AEF4B9 (cursus_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE cursus ADD CONSTRAINT FK_255A0C359027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F340AEF4B9 FOREIGN KEY (cursus_id) REFERENCES cursus (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cursus DROP FOREIGN KEY FK_255A0C359027487');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F340AEF4B9');
        $this->addSql('DROP TABLE cursus');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE theme');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D60322AC');
    }
}
