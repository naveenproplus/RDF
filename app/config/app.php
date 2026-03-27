<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */
    'db_log' => env('DB_LOG'),

    'db_general' => env('DB_GENERAL'),
    'db_main' => env('DB_DATABASE'),
    'db_tmp' => env('DB_TMP'),
    'db_prefix' => env('DB_PREFIX'),
    'db_support' => env('DB_SUPPORT'),

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    'firebase_project_id' => env('FIREBASE_PROJECT_ID', ''),
    'firebase_server_key' => env('FIREBASE_SERVER_KEY', ''),

    'FIREBASE_API_KEY' => env('FIREBASE_API_KEY', ''),
    'FIREBASE_AUTH_DOMAIN' => env('FIREBASE_AUTH_DOMAIN', ''),
    'FIREBASE_DATABASE_URL' => env('FIREBASE_DATABASE_URL', ''),
    'FIREBASE_PROJECT_ID' => env('FIREBASE_PROJECT_ID', ''),
    'FIREBASE_STORAGE_BUCKET' => env('FIREBASE_STORAGE_BUCKET', ''),
    'FIREBASE_SENDER_ID' => env('FIREBASE_SENDER_ID', ''),
    'FIREBASE_APP_ID' => env('FIREBASE_APP_ID', ''),
    'FIREBASE_MEASUREMENT_ID' => env('FIREBASE_MEASUREMENT_ID', ''),

// TEXT LOCAL
    'TEXT_LOCAL_API_KEY' => env('TEXT_LOCAL_API_KEY', ''),
    'TEXT_LOCAL_SENDER_NAME' => env('TEXT_LOCAL_SENDER_NAME', ''),

//  PhonePe Credentials
    'PHONEPE_MERCHANT_ID' => env('PHONEPE_MERCHANT_ID', 'PGTESTPAYUAT'),
    'PHONEPE_SALT_KEY' => env('PHONEPE_SALT_KEY', '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399'),

//  Busy Billing
    'BUSY_URL' => env('BUSY_URL', 'http://localhost:981'),
    'BUSY_USERNAME' => env('BUSY_USERNAME', 'a'),
    'BUSY_PASSWORD' => env('BUSY_PASSWORD', 'a'),

//  Google Review
    'GOOGLE_REVIEW_URL' => env('GOOGLE_REVIEW_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Kolkata',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store'  => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\SocialiteServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        //helpers
        'Helper' => App\helper\helper::class,
        'dynamicField' => helper\dynamicField::class,
        'Product' => helper\product::class,
        //enums
        'cruds' => App\enums\cruds::class,
        'docTypes' => App\enums\docTypes::class,
        'activeMenuNames' => App\enums\activeMenuNames::class,
        //models
        'DocNum' => App\Models\DocNum::class,
        'general' => App\Models\general::class,
        'SSP' => App\Models\ServerSideProcess::class,
        'ValidDB' => App\Rules\ValidDB::class,
        'ValidUnique' => App\Rules\ValidUnique::class,
        //controller
        'logs' => App\Http\Controllers\web\logController::class,
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

];
