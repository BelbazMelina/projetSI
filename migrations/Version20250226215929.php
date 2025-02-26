<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226215929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE molecule_idmolecule_seq CASCADE');
        $this->addSql('DROP SEQUENCE cadenas_idcadenas_seq CASCADE');
        $this->addSql('CREATE TABLE cadenas (id SERIAL NOT NULL, id_plante_id INT DEFAULT NULL, id_cadenas INT NOT NULL, mot_secret VARCHAR(50) NOT NULL, image VARCHAR(200) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73731F0F974EAACF ON cadenas (id_plante_id)');
        $this->addSql('CREATE TABLE partie (id SERIAL NOT NULL, id_plante_id INT DEFAULT NULL, id_partie INT NOT NULL, etat VARCHAR(50) NOT NULL, score INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_59B1F3D974EAACF ON partie (id_plante_id)');
        $this->addSql('CREATE TABLE plante (id SERIAL NOT NULL, id_plante INT NOT NULL, nom VARCHAR(50) NOT NULL, image VARCHAR(200) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE cadenas ADD CONSTRAINT FK_73731F0F974EAACF FOREIGN KEY (id_plante_id) REFERENCES plante (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3D974EAACF FOREIGN KEY (id_plante_id) REFERENCES plante (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE molecule DROP CONSTRAINT molecule_pkey');
        $this->addSql('ALTER TABLE molecule ADD id_plante_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE molecule ADD id_molecule INT NOT NULL');
        $this->addSql('ALTER TABLE molecule ADD image VARCHAR(200) NOT NULL');
        $this->addSql('ALTER TABLE molecule ADD information TEXT NOT NULL');
        $this->addSql('ALTER TABLE molecule ALTER formule_chimique SET NOT NULL');
        $this->addSql('ALTER TABLE molecule RENAME COLUMN idmolecule TO id');
        $this->addSql('ALTER TABLE molecule ADD CONSTRAINT FK_F2EF2239974EAACF FOREIGN KEY (id_plante_id) REFERENCES plante (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F2EF2239974EAACF ON molecule (id_plante_id)');
        $this->addSql('ALTER TABLE molecule ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE molecule DROP CONSTRAINT FK_F2EF2239974EAACF');
        $this->addSql('CREATE SEQUENCE molecule_idmolecule_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cadenas_idcadenas_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE cadenas DROP CONSTRAINT FK_73731F0F974EAACF');
        $this->addSql('ALTER TABLE partie DROP CONSTRAINT FK_59B1F3D974EAACF');
        $this->addSql('DROP TABLE cadenas');
        $this->addSql('DROP TABLE partie');
        $this->addSql('DROP TABLE plante');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP INDEX IDX_F2EF2239974EAACF');
        $this->addSql('DROP INDEX pk_molecule');
        $this->addSql('ALTER TABLE molecule DROP id_plante_id');
        $this->addSql('ALTER TABLE molecule DROP id_molecule');
        $this->addSql('ALTER TABLE molecule DROP image');
        $this->addSql('ALTER TABLE molecule DROP information');
        $this->addSql('ALTER TABLE molecule ALTER formule_chimique DROP NOT NULL');
        $this->addSql('ALTER TABLE molecule RENAME COLUMN id TO idmolecule');
        $this->addSql('ALTER TABLE molecule ADD PRIMARY KEY (idmolecule)');
    }
}
