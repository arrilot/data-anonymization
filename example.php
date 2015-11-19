#!/usr/bin/env php
<?php

use Arrilot\DataAnonymization\Anonymizer;
use Arrilot\DataAnonymization\Blueprint;
use Arrilot\DataAnonymization\Database\SqlDatabase;
use Faker\Generator as Faker;

require './vendor/autoload.php';

$dsn = 'mysql:dbname=test;host=127.0.0.1';
$user = 'testuser';
$password = 'test';

$database = new SqlDatabase($dsn, $user, $password);
$anonymizer = new Anonymizer($database);

// Describe `users` table.
$anonymizer->table('users', function (Blueprint $table) {
    // Specify a primary key of the table. For composite key an array should be passed in.
    // This step can be skipped if you have `id` as a primary key.
    $table->primary('id');

    // Replace with static data.
    $table->column('email')->replaceWith('john@example.com');

    // Replace with dynamic data.
    $table->column('email2')->replaceWith(function (Faker $faker) {
        return $faker->email;
    });

    // Use some constraints.
    $table->column('email3')->where('ID != 1')->replaceWith(function (Faker $faker) {
        return $faker->unique()->email;
    });
});

$anonymizer->run();

echo 'Anonymization has been completed!';
