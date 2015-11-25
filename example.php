#!/usr/bin/env php
<?php

use Arrilot\DataAnonymization\Anonymizer;
use Arrilot\DataAnonymization\Blueprint;
use Arrilot\DataAnonymization\Database\SqlDatabase;

require './vendor/autoload.php';

$dsn = 'mysql:dbname=test;host=127.0.0.1';
$user = 'testuser';
$password = 'test';

$database = new SqlDatabase($dsn, $user, $password);
$anonymizer = new Anonymizer($database);

// Describe `users` table.
$anonymizer->table('users', function (Blueprint $table) {
    // Specify a primary key of the table. An array should be passed in for composite key.
    // This step can be skipped if you have `id` as a primary key.
    // You can change default primary key for all tables with `Blueprint::setDefaultPrimary('ID')`
    $table->primary('id');

    // Replace with static data.
    $table->column('email1')->replaceWith('john@example.com');

    // Use #row# template to get "email_0@example.com", "email_1@example.com", "email_2@example.com"
    $table->column('email2')->replaceWith('email_#row#@example.com');

    // To replace with dynamic data a $generator is needed.
    // Any generator object can be set like that - `$anonymizer->setGenerator($generator);`
    // A simpler way is just to do `require fzaninotto/Faker` and it will be set automatically.
    $table->column('email3')->replaceWith(function ($generator) {
        return $generator->email;
    });

    // Use `where` to leave some data untouched.
    // If you don't list a column here, it will be left untouched too.
    $table->column('email4')->where('ID != 1')->replaceWith(function ($generator) {
        return $generator->unique()->email;
    });
});

$anonymizer->run();

echo 'Anonymization has been completed!';
