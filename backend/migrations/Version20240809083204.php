<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240809083204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_client (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, client_id INT NOT NULL, reference VARCHAR(100) NOT NULL, INDEX IDX_BA71A99DE48FD905 (game_id), INDEX IDX_BA71A99D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game_client ADD CONSTRAINT FK_BA71A99DE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE game_client ADD CONSTRAINT FK_BA71A99D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql("INSERT INTO client (name) VALUES ('steam'), ('giantbomb'), ('gog'), ('youtube'), ('microsoft'), ('apple'), ('twitch'), ('android'), ('amazon_asin'), ('amazon_luna'), ('amazon_adg'), ('epic_game_store'), ('oculus'), ('utomik'), ('itch_io'), ('xbox_marketplace'), ('kartridge'), ('playstation_store_us'), ('focus_entertainment'), ('xbox_game_pass_ultimate_cloud'), ('gamejolt')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_client DROP FOREIGN KEY FK_BA71A99DE48FD905');
        $this->addSql('ALTER TABLE game_client DROP FOREIGN KEY FK_BA71A99D19EB6921');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE game_client');
    }
}
