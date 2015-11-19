<?php

namespace Arrilot\Tests\DataAnonymization;

use Arrilot\DataAnonymization\Anonymizer;
use Arrilot\DataAnonymization\Blueprint;
use Mockery as m;
use PHPUnit_Framework_TestCase;

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
        $database->shouldReceive('updateByPrimary')->times(4);

        $anonymizer = new Anonymizer($database);

        $anonymizer->table('users', function (Blueprint $table) {
            $table->primary('id');
            $table->column('email')->replaceWith('john@example.com');
            $table->column('name')->replaceWith('john');
        });

        $anonymizer->run();
    }
}
