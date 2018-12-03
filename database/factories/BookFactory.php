<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Book::class, function (Faker $faker) {
    // List of Author IDs to map to books (one author to many books)
    $authorIds = App\Author::all()->pluck('id')->toArray();

    return [
        'title'             => $faker->name,
        'publication_date'  => $faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
        'description'       => $faker->text(),
        'pages'             => $faker->numberBetween(1, 1000),
        'author_id'         => // Create a new Author on the fly if none can be loaded
            $authorIds ? $faker->randomElement($authorIds) : factory(App\Author::class)->create()->id,
    ];
});
