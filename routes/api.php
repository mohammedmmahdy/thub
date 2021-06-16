<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('test', 'MainController@test');

// Authentication
// Route::post('forgotPassword', '\App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail');



//////////////////////////////////////////////////////////////////////////////
//////////////////////////////// Start Page //////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

Route::get('pages/{id}', 'MainController@pages');
Route::get('informations', 'MainController@informations');
Route::get('metas', 'MainController@metas');
Route::get('blogs', 'MainController@blogs');
Route::get('blog/{id}', 'MainController@blog');
Route::get('faqs', 'MainController@faqs');
Route::get('colors', 'MainController@colors');
Route::get('categories', 'MainController@categories');
Route::post('send-contact', 'MainController@send_contact_message');
Route::post('newsletter', 'MainController@newsletter');
Route::get('landing-page', 'MainController@landing_page');

// Auth
Route::post('customer/login', 'AuthController@login_or_register_customer');
Route::post('customer/verify-code', 'AuthController@verify_code_customer');

Route::post('driver/login', 'AuthController@login_or_register_driver');
Route::post('driver/verify-code', 'AuthController@verify_code_driver');

Route::get('driver-rates/{driverId}', 'DriverController@rates');
Route::get('driver-rate/{driverRate}', 'DriverController@rate');
Route::get('customer-rates/{customerId}', 'CustomerController@rates');
Route::get('customer-rate/{customerRate}', 'CustomerController@rate');

Route::get('cancel-trip-reasons', 'MainController@cancelTripReasons');
Route::get('cancel-request-reasons', 'MainController@cancelRequestReasons');

//////////////////////////////////////////////////////////////////////////////
///////////////////////////////// End Page ///////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => ['auth:api.customer']], function () {

    Route::post('logout', 'CustomerController@logout')->name('users.logout');

    Route::post('update-customer-information', 'CustomerController@update_information');
    Route::get('customer-wallet', 'CustomerController@wallet');
    Route::post('customer-add-or-update-rate', 'CustomerController@addOrUpdateRate');
});

//////////////////////////////////////////////////////////////////////////////
/////////////////////////////// End Customer /////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////
////////////////////////////// Start Driver //////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => ['auth:api.driver']], function () {

    Route::post('driver-update-information', 'DriverController@update_information');

    Route::group(['middleware' => ['customeAuthProcess:driver']], function () {

        Route::get('driver-wallet', 'DriverController@wallet');

        Route::get('driver-notifications', 'DriverController@notifications');
        Route::get('driver-notifications/{notification}', 'DriverController@notification');
        Route::post('driver-update-location', 'DriverController@update_my_location');

        Route::post('driver-add-or-update-rate', 'DriverController@addOrUpdateRate');
    });
});

//////////////////////////////////////////////////////////////////////////////
/////////////////////////////// End Driver ///////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
