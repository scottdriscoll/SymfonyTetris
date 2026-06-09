<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141022194856 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');
        
        $this->addSql('CREATE TABLE game_score (id INTEGER NOT NULL, time_played DATETIME NOT NULL, score INTEGER NOT NULL, opponent_name VARCHAR(255) DEFAULT NULL, opponent_score INTEGER DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX time_played_idx ON game_score (time_played)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');
        
        $this->addSql('DROP TABLE game_score');
    }
}
