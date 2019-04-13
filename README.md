# laravel-csv-import
COMMAND-LINE AND API BASED CSV IMPORT TOOL WITH AN ADMIN USER INTERFACE

This package allows you to import CSV files a database.
## Installation
This package can be used in Laravel 5.6 or higher. 
You can install the package via composer:

```
composer require imediasun/laravel-csv-import
```
manually add the service provider in your config/app.php file:

```
'providers' => [
    // ...
    Imediasun\Widgets\WidgetServiceProvider::class,
];
```
You can publish the migration with:

```
php artisan vendor:publish --provider="Imediasun\Widgets\WidgetServiceProvider" --tag="migrations"
```




