<?php

namespace Arrilot\Tests\DataAnonymization;

use Arrilot\DataAnonymization\Anonymizer;
use Arrilot\DataAnonymization\Blueprint;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use StdClass;

class AnonymizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tear down.
     */
    public function tearDown()
    {
        m::close();
    }

    public function testRun()
    {
        $database = m::mock('Arrilot\DataAnonymization\Database\DatabaseInterface');
        $database->shouldReceive('getRows')->twice()->andReturn([
            ['id' => 1, 'name' => 'Jane', 'email' => 'jane@gmail.com'],
            ['id' => 2, 'name' => 'Jack', 'email' => 'jack@gmail.com'],
        ]);
        $database->shouldReceive('updateByPrimary')->once()->with('users', ['id' => 1], 'email', 'john@example.com');
        $database->shouldReceive('updateByPrimary')->once()->with('users', ['id' => 2], 'email', 'john@example.com');
        $database->shouldReceive('updateByPrimary')->once()->with('users', ['id' => 1], 'name', 'john-0');
        $database->shouldReceive('updateByPrimary')->once()->with('users', ['id' => 2], 'name', 'john-1');

        $anonymizer = new Anonymizer($database);

        $anonymizer->table('users', function (Blueprint $table) {
            $table->primary('id');
            $table->column('email')->replaceWith('john@example.com');
            $table->column('name')->replaceWith('john-#row#');
        });

        $anonymizer->run();
    }

    public function testSettingGenerator()
    {
        $database = m::mock('Arrilot\DataAnonymization\Database\DatabaseInterface');
        $anonymizer = new Anonymizer($database);
        $this->assertNull($anonymizer->getGenerator());

        $anonymizer->setGenerator(new StdClass());
        $this->assertInstanceOf('StdClass', $anonymizer->getGenerator());
    }
}
