<?php

namespace Arrilot\DataAnonymization;

use Arrilot\DataAnonymization\Database\DatabaseInterface;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Arr;

class Anonymizer
{
    /**
     * Database interactions object.
     *
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * Faker generator instance object.
     *
     * @var Generator
     */
    protected $faker;

    /**
     * Blueprints for tables.
     *
     * @var array
     */
    protected $blueprints = [];

    /**
     * Constructor.
     *
     * @param DatabaseInterface $database
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
        $this->faker = Factory::create();
    }

    /**
     * Perform data anonymization.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->blueprints as $table => $blueprint) {
            $this->applyBlueprint($blueprint);
        }
    }

    /**
     * Describe a table with a given callback.
     *
     * @param string   $name
     * @param callable $callback
     *
     * @return void
     */
    public function table($name, callable $callback)
    {
        $blueprint = new Blueprint($name, $callback);

        $this->blueprints[$name] = $blueprint->build();
    }

    /**
     * Apply blueprint to the database.
     *
     * @param Blueprint $blueprint
     *
     * @return void
     */
    protected function applyBlueprint(Blueprint $blueprint)
    {
        foreach ($blueprint->columns as $column) {
            $this->updateColumn($blueprint->table, $blueprint->primary, $column);
        }
    }

    /**
     * Update all needed values of a give column.
     *
     * @param string $table
     * @param array  $primary
     * @param array  $column
     */
    protected function updateColumn($table, $primary, $column)
    {
        $rows = $this->database->getRows(
            $table,
            $this->mergeColumns($primary, $column['name']),
            $column['where']
        );

        if (!$rows) {
            return;
        }

        foreach ($rows as $row) {
            $this->database->updateByPrimary(
                $table,
                Arr::only($row, $primary),
                $column['name'],
                $this->calculateNewValue($column['replace'])
            );
        }
    }

    /**
     * Calculate new value for each row.
     *
     * @param string|callable $replace
     *
     * @return mixed
     */
    protected function calculateNewValue($replace)
    {
        return is_callable($replace) ? call_user_func($replace, $this->faker) : $replace;
    }

    /**
     * Merge columns for select.
     *
     * @param array  $primary
     * @param string $columnName
     *
     * @return array
     */
    protected function mergeColumns($primary, $columnName)
    {
        return array_merge($primary, [
            $columnName,
        ]);
    }
}
