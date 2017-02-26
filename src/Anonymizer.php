<?php

namespace Arrilot\DataAnonymization;

use Arrilot\DataAnonymization\Database\DatabaseInterface;
use Exception;
use Faker\Factory;

class Anonymizer
{
    /**
     * Database interactions object.
     *
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * Generator object (e.g faker).
     *
     * @var mixed
     */
    protected $generator;

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
     * @param mixed             $generator
     */
    public function __construct(DatabaseInterface $database, $generator = null)
    {
        $this->database = $database;
    }

    /**
     * Setter for generator.
     *
     * @param mixed $generator
     *
     * @return $this
     */
    public function setGenerator($generator)
    {
        $this->generator = $generator;
        return $this;
    }

    /**
     * Getter for generator.
     *
     * @return mixed
     */
    public function getGenerator()
    {
        if (is_null($this->generator) && class_exists('\Faker\Factory')) {
            $this->generator = Factory::create();
        }
        return $this->generator;
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

        if (empty($rows)) {
            return;
        }

        foreach ($rows as $rowNum => $row) {
            $this->database->updateByPrimary(
                $table,
                Helpers::arrayOnly($row, $primary),
                $column['name'],
                $this->calculateNewValue($column['replace'], $rowNum)
            );
        }
    }

    /**
     * Calculate new value for each row.
     *
     * @param string|callable $replace
     * @param int             $rowNum
     *
     * @return string
     */
    protected function calculateNewValue($replace, $rowNum)
    {
        $value = $this->handlePossibleClosure($replace);

        return $this->replacePlaceholders($value, $rowNum);
    }

    /**
     * Replace placeholders.
     *
     * @param mixed $value
     * @param int   $rowNum
     *
     * @return mixed
     */
    protected function replacePlaceholders($value, $rowNum)
    {
        if (!is_string($value)) {
            return $value;
        }

        return str_replace('#row#', $rowNum, $value);
    }

    /**
     * @param $replace
     *
     * @return mixed
     */
    protected function handlePossibleClosure($replace)
    {
        if (!is_callable($replace)) {
            return $replace;
        }

        if ($this->generator === null) {
            throw new Exception('You forgot to set a generator');
        }

        return call_user_func($replace, $this->generator);
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
