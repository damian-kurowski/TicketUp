<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250421151533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE "order" (id SERIAL NOT NULL, user_id INT DEFAULT NULL, customer_email VARCHAR(255) NOT NULL, customer_first_name VARCHAR(255) DEFAULT NULL, customer_last_name VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F5299398A76ED395 ON "order" (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "order".created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_item (id SERIAL NOT NULL, related_order_id INT NOT NULL, ticket_type_id INT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, total_price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_52EA1F092B1C2395 ON order_item (related_order_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_52EA1F09C980D5C1 ON order_item (ticket_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ticket (id SERIAL NOT NULL, order_item_id INT NOT NULL, ticket_type_id INT NOT NULL, code VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, generated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, scanned_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_97A0ADA377153098 ON ticket (code)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_97A0ADA3E415FB15 ON ticket (order_item_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_97A0ADA3C980D5C1 ON ticket (ticket_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN ticket.generated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN ticket.scanned_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F092B1C2395 FOREIGN KEY (related_order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09C980D5C1 FOREIGN KEY (ticket_type_id) REFERENCES ticket_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3C980D5C1 FOREIGN KEY (ticket_type_id) REFERENCES ticket_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "order" DROP CONSTRAINT FK_F5299398A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP CONSTRAINT FK_52EA1F092B1C2395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP CONSTRAINT FK_52EA1F09C980D5C1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA3E415FB15
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA3C980D5C1
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "order"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ticket
        SQL);
    }
}
