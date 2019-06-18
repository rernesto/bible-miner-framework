<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190618194433 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE raw_vocabulary (id INT AUTO_INCREMENT NOT NULL, language_id INT NOT NULL, word VARCHAR(64) NOT NULL, INDEX IDX_C158ED6082F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bible_verse (id INT AUTO_INCREMENT NOT NULL, bible_version_id INT NOT NULL, reference VARCHAR(16) NOT NULL, verse_text LONGTEXT NOT NULL, verse_tokens LONGTEXT NOT NULL, INDEX IDX_8B9F659B6C6DE495 (bible_version_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bible_stem_vsm (id INT AUTO_INCREMENT NOT NULL, verse_id INT NOT NULL, vocabulary_id INT NOT NULL, INDEX IDX_C68C2696BBF309FA (verse_id), INDEX IDX_C68C2696AD0E05F6 (vocabulary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bible_raw_vsm (id INT AUTO_INCREMENT NOT NULL, verse_id INT NOT NULL, vocabulary_id INT NOT NULL, tf_idf_value DOUBLE PRECISION NOT NULL, INDEX IDX_8A1F8C6CBBF309FA (verse_id), INDEX IDX_8A1F8C6CAD0E05F6 (vocabulary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, short_name VARCHAR(2) NOT NULL, name VARCHAR(16) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stem_vocabulary (id INT AUTO_INCREMENT NOT NULL, language_id INT NOT NULL, word VARCHAR(32) NOT NULL, INDEX IDX_E45032882F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bible_version (id INT AUTO_INCREMENT NOT NULL, language_id INT NOT NULL, short_name VARCHAR(16) NOT NULL, name VARCHAR(64) NOT NULL, INDEX IDX_AFC19D3582F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE raw_vocabulary ADD CONSTRAINT FK_C158ED6082F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE bible_verse ADD CONSTRAINT FK_8B9F659B6C6DE495 FOREIGN KEY (bible_version_id) REFERENCES bible_version (id)');
        $this->addSql('ALTER TABLE bible_stem_vsm ADD CONSTRAINT FK_C68C2696BBF309FA FOREIGN KEY (verse_id) REFERENCES bible_verse (id)');
        $this->addSql('ALTER TABLE bible_stem_vsm ADD CONSTRAINT FK_C68C2696AD0E05F6 FOREIGN KEY (vocabulary_id) REFERENCES stem_vocabulary (id)');
        $this->addSql('ALTER TABLE bible_raw_vsm ADD CONSTRAINT FK_8A1F8C6CBBF309FA FOREIGN KEY (verse_id) REFERENCES bible_verse (id)');
        $this->addSql('ALTER TABLE bible_raw_vsm ADD CONSTRAINT FK_8A1F8C6CAD0E05F6 FOREIGN KEY (vocabulary_id) REFERENCES raw_vocabulary (id)');
        $this->addSql('ALTER TABLE stem_vocabulary ADD CONSTRAINT FK_E45032882F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE bible_version ADD CONSTRAINT FK_AFC19D3582F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bible_raw_vsm DROP FOREIGN KEY FK_8A1F8C6CAD0E05F6');
        $this->addSql('ALTER TABLE bible_stem_vsm DROP FOREIGN KEY FK_C68C2696BBF309FA');
        $this->addSql('ALTER TABLE bible_raw_vsm DROP FOREIGN KEY FK_8A1F8C6CBBF309FA');
        $this->addSql('ALTER TABLE raw_vocabulary DROP FOREIGN KEY FK_C158ED6082F1BAF4');
        $this->addSql('ALTER TABLE stem_vocabulary DROP FOREIGN KEY FK_E45032882F1BAF4');
        $this->addSql('ALTER TABLE bible_version DROP FOREIGN KEY FK_AFC19D3582F1BAF4');
        $this->addSql('ALTER TABLE bible_stem_vsm DROP FOREIGN KEY FK_C68C2696AD0E05F6');
        $this->addSql('ALTER TABLE bible_verse DROP FOREIGN KEY FK_8B9F659B6C6DE495');
        $this->addSql('DROP TABLE raw_vocabulary');
        $this->addSql('DROP TABLE bible_verse');
        $this->addSql('DROP TABLE bible_stem_vsm');
        $this->addSql('DROP TABLE bible_raw_vsm');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE stem_vocabulary');
        $this->addSql('DROP TABLE bible_version');
    }
}
