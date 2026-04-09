<?php

namespace App\Testing\Database;

use Illuminate\Database\Schema\MySqlBuilder;

class TestingMySqlBuilder extends MySqlBuilder
{
    public function dropAllTables()
    {
        $tables = $this->getTableListing($this->getCurrentSchemaListing());

        if (empty($tables)) {
            return;
        }

        $this->disableForeignKeyConstraints();

        try {
            foreach ($this->grammar->escapeNames($tables) as $table) {
                $this->connection->statement("drop table if exists {$table}");
            }
        } finally {
            $this->enableForeignKeyConstraints();
        }
    }
}
