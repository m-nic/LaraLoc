# Lara Loc

This is a simple package designed to enable multilanguage selection for route groups and additionally for models.

1. Register the service provider before the RouteServiceProvider::class in `config/app.php` 
```php
    [
        // ...,
        mNic\LaraLoc\LaraLocServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ];
```

2. Publish vendor migration:
```shell script
php artisan vendor:publish --tag="laraloc.migration"
```
or publish all (migration + 2 lang selector views)
```shell script
php artisan vendor:publish --provider="mNic\LaraLoc\LaraLocServiceProvider"
```

3. Update the `config/app.php`

```php
[
    // ... 
    'locale' => 'en',

    // set the default locale
    'fallback_locale' => 'en',

    // add the available local array
    'available_locale' => [
        'en',
        'ro',
        'fr'
    ],
    // ... 
];
```


4. Run the migration:
```shell script
php artisan migrate
```

5. Use the helper methods to wrap your routes and groups as such:

The `enableTranslationRoutes` helper sets the locale for both the application, and the models. 
```php
enableTranslationRoutes(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/home', 'HomeController@index')->name('home');

    Auth::routes();
});
```

And `enableTranslationModelRoutes` to wrap routes where you want to change only the model language. Useful for admin interfaces.
```php
Route::middleware(['auth'])->group(function () {

    enableTranslationModelRoutes(function () {

        Route::prefix('contacts')->group(function () {
            Route::get('/', 'ContactsController@list');
            // ...
        });
    });
});
```

6. Enable translations in models
Add the `Translatable` trait and implement the method `getTranslatableFieldNames`

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use mNic\LaraLoc\Concerns\Translatable;

class Contact extends Model
{
    // Add the trait
    use Translatable;

    protected $table = 'contacts';
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'bio',
        'more_details',
    ];

    // implement the method. Provide translatable fields.
    function getTranslatableFieldNames()
    {
        return [
            'bio',
            'more_details',
        ];
    }
}

```

7. Use the language selector views:

Use this view to display buttons that change the language for the entire app.
```blade
@include('laraloc::locale-selector')
```

Use this view to change the language for a model in an admin interface for example.
This will allow to translate the models for the routes defined in `enableTranslationModelRoutes` group.   
```blade
@include('laraloc::model-locale-selector')
```

If needed publish vendor `.blade` files to customize the look of the buttons:
```shell script
php artisan vendor:publish --tag="laraloc.views"
```

See blade files:
resources/views/vendor/mnic/locale-selector.blade.php
resources/views/vendor/mnic/model-locale-selector.blade.php



