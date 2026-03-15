<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint on user email';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM "user" a USING "user" b WHERE a.id > b.id AND a.email = b.email');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
    }
}
