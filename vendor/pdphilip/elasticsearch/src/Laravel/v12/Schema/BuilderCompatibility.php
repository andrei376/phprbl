<?php

namespace PDPhilip\Elasticsearch\Laravel\v12\Schema;

use Closure;
use PDPhilip\Elasticsearch\Schema\Blueprint;

trait BuilderCompatibility
{
    /**
     * {@inheritDoc}
     */
    protected function createBlueprint($table, ?Closure $callback = null): Blueprint
    {
        return new Blueprint($this->connection, $table, $callback);
    }

    public function getTableListing($schema = null, $schemaQualified = true)
    {
        return array_column($this->getTables(), 'name');
    }
}
