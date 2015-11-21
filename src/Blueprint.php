<?php

namespace Arrilot\DataAnonymization;

class Blueprint
{
    /**
     * Default primary key for all blueprints.
     *
     * @var array
     */
    protected static $defaultPrimary = ['id'];

    /**
     * Table to blueprint.
     *
     * @var string
     */
    public $table;

    /**
     * Array of columns.
     *
     * @var array
     */
    public $columns = [];

    /**
     * Table primary key.
     *
     * @var array
     */
    public $primary;

    /**
     * Current column.
     *
     * @var array
     */
    protected $currentColumn = [];

    /**
     * Callback that builds blueprint.
     *
     * @var callable
     */
    protected $callback;

    /**
     * Blueprint constructor.
     *
     * @param string        $table
     * @param callable|null $callback
     */
    public function __construct($table, callable $callback)
    {
        $this->table = $table;
        $this->callback = $callback;
    }

    /**
     * Setter for default primary key.
     *
     * @param string|array $key
     */
    public static function setDefaultPrimary($key)
    {
        self::$defaultPrimary = (array) $key;
    }

    /**
     * Add a column to blueprint.
     *
     * @param string $name
     *
     * @return $this
     */
    public function column($name)
    {
        $this->currentColumn = [
            'name'    => $name,
            'where'   => null,
            'replace' => null,
        ];

        return $this;
    }

    /**
     * Add where to the current column.
     *
     * @param string $rawSql
     *
     * @return $this
     */
    public function where($rawSql)
    {
        $this->currentColumn['where'] = $rawSql;

        return $this;
    }

    /**
     * Set how data should be replaced.
     *
     * @param callable|string $callback
     *
     * @return void
     */
    public function replaceWith($callback)
    {
        $this->currentColumn['replace'] = $callback;

        $this->columns[] = $this->currentColumn;
    }

    /**
     * Build the current blueprint.
     *
     * @return $this
     */
    public function build()
    {
        $callback = $this->callback;

        $callback($this);

        if (is_null($this->primary)) {
            $this->primary = self::$defaultPrimary;
        }

        return $this;
    }

    /**
     * Setter for a primary key.
     *
     * @param string|array $key
     *
     * @return $this
     */
    public function primary($key)
    {
        $this->primary = (array) $key;

        return $this;
    }
}
