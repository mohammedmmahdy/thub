<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('generator_builder', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@builder')->name('io_generator_builder');

Route::get('field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@fieldTemplate')->name('io_field_template');

Route::get('relation_field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@relationFieldTemplate')->name('io_relation_field_template');

Route::post('generator_builder/generate', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generate')->name('io_generator_builder_generate');

Route::post('generator_builder/rollback', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@rollback')->name('io_generator_builder_rollback');

Route::post(
    'generator_builder/generate-from-file',
    '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generateFromFile'
)->name('io_generator_builder_generate_from_file');

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/

Route::group(
    [
        'prefix' => 'adminPanel',
        'namespace' => 'App\Http\Controllers\AdminPanel',
        'as' => 'adminPanel.'
    ],
    function () {

        Route::group(['middleware' => ['guest']], function () {

            Route::get('/login', 'AuthController@login')->name('login');
            Route::post('/login', 'AuthController@postLogin')->name('postLogin');
        });


        Route::group(['middleware' => ['auth:admin', 'permissionHandler']], function () {

            Route::get('logout', 'AuthController@logout')->name('logout');

            Route::get('/', 'DashboardController@dashboard')->name('dashboard');

            Route::resource('roles', 'RolesController');
            Route::get('updatePermissions', 'RolesController@updatePermissions')->name('roles.updatePermissions');


            Route::resource('admins', AdminController::class);
            Route::resource('metas', MetaController::class);

            Route::group(['prefix' => 'companies', 'as' => 'companies.'], function () {
                Route::get('/', 'CompanyController@index')->name('index');
                Route::get('/{company}', 'CompanyController@show')->name('show');
                Route::patch('/approve/{company}', 'CompanyController@approve')->name('approve');
                Route::patch('/reject/{company}', 'CompanyController@reject')->name('reject');
                Route::patch('/deactivate/{company}', 'CompanyController@deactivate')->name('deactivate');
            });

            Route::group(['prefix' => 'drivers', 'as' => 'drivers.'], function () {
                Route::get('/', 'DriverController@index')->name('index');
                Route::get('/{driver}', 'DriverController@show')->name('show');
                Route::patch('/approve/{driver}', 'DriverController@approve')->name('approve');
                Route::patch('/reject/{driver}', 'DriverController@reject')->name('reject');
                Route::patch('/deactivate/{driver}', 'DriverController@deactivate')->name('deactivate');
            });

            Route::prefix('customers')->as('customers.')->group(function () {
                Route::get('/', 'CustomerController@index')->name('index');
                Route::get('/{customer}', 'CustomerController@show')->name('show');
            });

            // Route::resource('customers', CustomerController::class);
            // Route::patch('customers/approve/{customer}', 'CustomerController@approve')->name('customers.approve');

            Route::resource('services', ServiceController::class);
            Route::resource('socialLinks', SocialLinkController::class);
            Route::post('ckeditor/upload', 'CkeditorController@upload')->name('ckeditor.upload');
            Route::resource('information', InformationController::class);
            Route::resource('sliders', SliderController::class);
            Route::resource('contacts', ContactController::class);
            Route::get('newsletters', 'NewsletterController@index')->name('newsletters.index');
            Route::resource('blogs', BlogController::class);
            Route::resource('faqCategories', FaqCategoryController::class);
            Route::resource('faqs', FaqController::class);


            Route::resource('appFeatures', AppFeatureController::class);
            Route::resource('vehicles', VehicleController::class);
            Route::patch('vehicles/approve/{vehicle}', 'VehicleController@approve')->name('vehicles.approve');

            Route::resource('brands', BrandController::class);
            Route::resource('trips', TripController::class);

            Route::resource('brands.vehicleModels', VehicleModelController::class)->shallow();
            Route::resource('vehicleTypes', VehicleTypeController::class);
            Route::resource('colors', ColorController::class);

            // Pages CRUD
            Route::resource('pages', 'PageController');
            Route::resource('pages.paragraphs', 'ParagraphController')->shallow();
            Route::resource('pages.images', 'imagesController')->shallow();

            Route::resource('options', OptionController::class);
            Route::resource('rewards', RewardController::class);
            Route::resource('notifications', NotificationController::class);

            Route::resource('reasons', ReasonController::class);
            Route::resource('categories', CategoryController::class);

        });
    }
);

///////////////////////////////////////////////////////////////////////////
///								End admin panel routes 					///
///////////////////////////////////////////////////////////////////////////

