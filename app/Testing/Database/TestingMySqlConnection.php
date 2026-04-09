<?php

namespace App\Testing\Database;

use Illuminate\Database\MySqlConnection;

class TestingMySqlConnection extends MySqlConnection
{
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new TestingMySqlBuilder($this);
    }
}
