<?php

namespace Arrilot\Tests\DataAnonymization;

use Arrilot\DataAnonymization\Blueprint;
use PHPUnit_Framework_TestCase;
use StdClass;

class BlueprintTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Blueprint::setDefaultPrimary('id');
    }

    /**
     * Helper to call a callback with Faker.
     *
     * @param callable $callback
     *
     * @return mixed
     */
    protected function callGeneratorCallback(callable $callback)
    {
        return call_user_func($callback, new StdClass());
    }

    public function testPrimary()
    {
        // No primary
        $blueprint = new Blueprint('users', function (Blueprint $table) {
            // No primary is specified.
        });
        $result = $blueprint->build();
        $this->assertSame(['id'], $result->primary);

        // Override
        $blueprint = new Blueprint('users', function (Blueprint $table) {
            $table->primary('Id');
        });
        $result = $blueprint->build();
        $this->assertSame(['Id'], $result->primary);

        // Composite
        $blueprint = new Blueprint('users', function (Blueprint $table) {
            $table->primary(['id', 'category_id']);
        });
        $result = $blueprint->build();
        $this->assertSame(['id', 'category_id'], $result->primary);
    }

    public function testSettingDefaultPrimary()
    {
        // String
        Blueprint::setDefaultPrimary('category_id');
        $blueprint = new Blueprint('users', function (Blueprint $table) {
            // No primary is specified.
        });
        $result = $blueprint->build();
        $this->assertSame(['category_id'], $result->primary);

        // Array
        Blueprint::setDefaultPrimary(['id', 'category_id']);
        $blueprint = new Blueprint('users', function (Blueprint $table) {
            // No primary is specified.
        });
        $result = $blueprint->build();
        $this->assertSame(['id', 'category_id'], $result->primary);
    }

    public function testReplaceWithStaticData()
    {
        $blueprint = new Blueprint('users', function (Blueprint $table) {
            $table->column('email')->replaceWith('john@example.com');
        });

        $result = $blueprint->build();

        $this->assertSame('users', $result->table);
        $this->assertSame(['id'], $result->primary);
        $this->assertCount(1, $result->columns);
        $this->assertSame('email', $result->columns[0]['name']);
        $this->assertSame('john@example.com', $result->columns[0]['replace']);
        $this->assertNull($result->columns[0]['where']);
    }

    public function testReplaceWithConstraints()
    {
        $blueprint = new Blueprint('users', function (Blueprint $table) {
            $table->column('email')->where('id != 1')->replaceWith('john@example.com');
        });

        $result = $blueprint->build();

        $this->assertSame('users', $result->table);
        $this->assertSame(['id'], $result->primary);
        $this->assertCount(1, $result->columns);
        $this->assertSame('email', $result->columns[0]['name']);
        $this->assertSame('john@example.com', $result->columns[0]['replace']);
        $this->assertSame('id != 1', $result->columns[0]['where']);
    }

    public function testReplaceWithDynamicData()
    {
        $blueprint = new Blueprint('users', function (Blueprint $table) {
            $table->column('email')->replaceWith(function ($generator) {
                return 'some dynamic data';
            });
        });

        $result = $blueprint->build();

        $this->assertSame('users', $result->table);
        $this->assertSame(['id'], $result->primary);
        $this->assertCount(1, $result->columns);
        $this->assertSame('email', $result->columns[0]['name']);
        $this->assertInstanceOf('Closure', $result->columns[0]['replace']);
        $this->assertSame('some dynamic data', $this->callGeneratorCallback($result->columns[0]['replace']));
        $this->assertNull($result->columns[0]['where']);
    }
}
