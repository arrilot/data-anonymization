[![Latest Stable Version](https://poser.pugx.org/arrilot/data-anonymization/v/stable.svg)](https://packagist.org/packages/arrilot/data-anonymization/)
[![Total Downloads](https://img.shields.io/packagist/dt/arrilot/data-anonymization.svg?style=flat)](https://packagist.org/packages/Arrilot/data-anonymization)
[![Build Status](https://img.shields.io/travis/arrilot/data-anonymization/master.svg?style=flat)](https://travis-ci.org/arrilot/data-anonymization)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/arrilot/data-anonymization/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/arrilot/data-anonymization/)

# Data anonymization

* This simple framework-agnostic package helps you to replace any sensitive production data in your development databases.

## Installation

```composer require arrilot/data-anonymization```

If you use Laravel framework, it's recomended to use a bridge package instead - https://github.com/arrilot/laravel-data-anonymization

## Usage

Workflow:

1. Create a php executable.

2. Define how you want to anonymize your data in this file using fluent api (see example below).

3. Make sure it is not accessible throw the web and etc.

4. Run it every time you want.

Here is an example file that illustrates api really well:

```php

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
    // You can change default primary key for all tables with `Blueprint::setDefaultPrimary('ID')`
    $table->primary('id');

    // Replace with static data.
    $table->column('email')->replaceWith('john@example.com');

    // Replace with dynamic data using fzaninotto/Faker.
    $table->column('email2')->replaceWith(function (Faker $faker) {
        return $faker->email;
    });

    // Use `where` to leave some data untouched.
    // If you don't list a column here, it will be left untouched too.
    $table->column('email3')->where('ID != 1')->replaceWith(function (Faker $faker) {
        return $faker->unique()->email;
    });
});

$anonymizer->run();

echo 'Anonymization has been completed!';

```
