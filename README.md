# laravel-csv-import
COMMAND-LINE AND API BASED CSV IMPORT TOOL WITH AN ADMIN USER INTERFACE

This package allows you to import CSV files a database.
<h3>Installation</h3>
This package can be used in Laravel 5.6 or higher. 
You can install the package via composer:
<code>composer require imediasun/laravel-csv-import</code>
manually add the service provider in your config/app.php file:
<code>
'providers' => [
    // ...
    Imediasun\Widgets\WidgetServiceProvider::class,
];
</code>
You can publish the migration with:
<code>
php artisan vendor:publish --provider="Imediasun\Widgets\WidgetServiceProvider" --tag="migrations"
</code>



