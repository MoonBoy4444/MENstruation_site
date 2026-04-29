<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260429061322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse (id INT IDENTITY NOT NULL, type_addr NVARCHAR(50) NOT NULL, rue_addr NVARCHAR(200) NOT NULL, ville_addr NVARCHAR(100) NOT NULL, cp_addr NVARCHAR(10) NOT NULL, pays_addr NVARCHAR(100) NOT NULL, client_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C35F081619EB6921 ON adresse (client_id)');
        $this->addSql('CREATE TABLE avis (id INT IDENTITY NOT NULL, titre_avis NVARCHAR(200) NOT NULL, msg_avis VARCHAR(MAX) NOT NULL, note_avis SMALLINT NOT NULL, date_avis DATETIME2(6) NOT NULL, client_id INT NOT NULL, produit_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_8F91ABF019EB6921 ON avis (client_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0F347EFB ON avis (produit_id)');
        $this->addSql('CREATE TABLE client (id INT IDENTITY NOT NULL, nom_cli NVARCHAR(100) NOT NULL, prenom_cli NVARCHAR(100) NOT NULL, date_naissance_cli DATE, mail_cli NVARCHAR(180) NOT NULL, password NVARCHAR(255) NOT NULL, tel_cli NVARCHAR(20), roles VARCHAR(MAX) NOT NULL, type_client_id INT, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C744045554ED7E56 ON client (mail_cli) WHERE mail_cli IS NOT NULL');
        $this->addSql('CREATE INDEX IDX_C7440455AD2D2831 ON client (type_client_id)');
        $this->addSql('CREATE TABLE client_favoris (client_id INT NOT NULL, produit_id INT NOT NULL, PRIMARY KEY (client_id, produit_id))');
        $this->addSql('CREATE INDEX IDX_DB0E804F19EB6921 ON client_favoris (client_id)');
        $this->addSql('CREATE INDEX IDX_DB0E804FF347EFB ON client_favoris (produit_id)');
        $this->addSql('CREATE TABLE commande (id INT IDENTITY NOT NULL, statut_cde NVARCHAR(50) NOT NULL, montant_cde NUMERIC(10, 2) NOT NULL, est_paye_cde BIT NOT NULL, date_commande DATETIME2(6) NOT NULL, client_id INT NOT NULL, livraison_id INT, paiement_id INT, adresse_livraison_id INT, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6EEAA67D19EB6921 ON commande (client_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D8E54FB25 ON commande (livraison_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D2A4C4478 ON commande (paiement_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DBE2F0A35 ON commande (adresse_livraison_id)');
        $this->addSql('CREATE TABLE ligne_commande (id INT IDENTITY NOT NULL, quantite INT NOT NULL, reduction NUMERIC(8, 2) NOT NULL, commande_id INT NOT NULL, produit_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_3170B74B82EA2E54 ON ligne_commande (commande_id)');
        $this->addSql('CREATE INDEX IDX_3170B74BF347EFB ON ligne_commande (produit_id)');
        $this->addSql('CREATE TABLE livraison (id INT IDENTITY NOT NULL, nom_livr NVARCHAR(100) NOT NULL, choix_livr NVARCHAR(100), delai_livr INT, frais_livr NUMERIC(8, 2), date_livr DATE, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE paiement (id INT IDENTITY NOT NULL, libelle_pay NVARCHAR(100) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE produit (id INT IDENTITY NOT NULL, nom_prod NVARCHAR(200) NOT NULL, prix_prod NUMERIC(10, 2) NOT NULL, stock_prod INT NOT NULL, image_prod NVARCHAR(255), taille_prod NVARCHAR(50), ref_prod NVARCHAR(100) NOT NULL, desc_prod VARCHAR(MAX), type_produit_id INT, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29A5EC27A2C75010 ON produit (ref_prod) WHERE ref_prod IS NOT NULL');
        $this->addSql('CREATE INDEX IDX_29A5EC271237A8DE ON produit (type_produit_id)');
        $this->addSql('CREATE TABLE type_client (id INT IDENTITY NOT NULL, nom_type_client NVARCHAR(100) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE type_produit (id INT IDENTITY NOT NULL, nom_type_prod NVARCHAR(100) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT IDENTITY NOT NULL, body VARCHAR(MAX) NOT NULL, headers VARCHAR(MAX) NOT NULL, queue_name NVARCHAR(190) NOT NULL, created_at DATETIME2(6) NOT NULL, available_at DATETIME2(6) NOT NULL, delivered_at DATETIME2(6), PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE adresse ADD CONSTRAINT FK_C35F081619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF019EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455AD2D2831 FOREIGN KEY (type_client_id) REFERENCES type_client (id)');
        $this->addSql('ALTER TABLE client_favoris ADD CONSTRAINT FK_DB0E804F19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE client_favoris ADD CONSTRAINT FK_DB0E804FF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D8E54FB25 FOREIGN KEY (livraison_id) REFERENCES livraison (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D2A4C4478 FOREIGN KEY (paiement_id) REFERENCES paiement (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DBE2F0A35 FOREIGN KEY (adresse_livraison_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC271237A8DE FOREIGN KEY (type_produit_id) REFERENCES type_produit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA db_accessadmin');
        $this->addSql('CREATE SCHEMA db_backupoperator');
        $this->addSql('CREATE SCHEMA db_datareader');
        $this->addSql('CREATE SCHEMA db_datawriter');
        $this->addSql('CREATE SCHEMA db_ddladmin');
        $this->addSql('CREATE SCHEMA db_denydatareader');
        $this->addSql('CREATE SCHEMA db_denydatawriter');
        $this->addSql('CREATE SCHEMA db_owner');
        $this->addSql('CREATE SCHEMA db_securityadmin');
        $this->addSql('ALTER TABLE adresse DROP CONSTRAINT FK_C35F081619EB6921');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF019EB6921');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF0F347EFB');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455AD2D2831');
        $this->addSql('ALTER TABLE client_favoris DROP CONSTRAINT FK_DB0E804F19EB6921');
        $this->addSql('ALTER TABLE client_favoris DROP CONSTRAINT FK_DB0E804FF347EFB');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67D8E54FB25');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67D2A4C4478');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67DBE2F0A35');
        $this->addSql('ALTER TABLE ligne_commande DROP CONSTRAINT FK_3170B74B82EA2E54');
        $this->addSql('ALTER TABLE ligne_commande DROP CONSTRAINT FK_3170B74BF347EFB');
        $this->addSql('ALTER TABLE produit DROP CONSTRAINT FK_29A5EC271237A8DE');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_favoris');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE type_client');
        $this->addSql('DROP TABLE type_produit');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
