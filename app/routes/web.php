<?php

use App\helper\helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Home\HomeAuthController;
use App\Http\Controllers\web\loginController;
use App\Http\Controllers\web\dashboardController;
use App\Http\Controllers\web\generalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\web\OrderController;
use App\Http\Controllers\web\SupportController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cleared!";
});
Route::get('/notification-test-send', function() {
    return helper::sendNotification(1,"Title","Message");
});

Route::get('/', function () {
    return Redirect::to('/admin');
});

Route::controller(generalController::class)->group(function () {
    Route::post('/get/country','getCountry');
    Route::post('/get/states','getState');
    Route::post('/get/districts','getDistrict');
    Route::post('/get/taluks','getTaluk');
    Route::post('/get/city','getCity');
    Route::post('/get/postal-code','getPostalCode');
    Route::post('/get/gender','getGender');
    Route::POST('/get/tax', 'getTax');
    Route::POST('/get/uom', 'getUOM');
    Route::post('/get/bank-type','getBankType');
    Route::post('/get/bank','getBank');
    Route::post('/get/bank-branch','getBankBranch');
    Route::post('/get/bank-account-type','getBankAccountType');
    Route::POST('/get/customer-groups', 'getCustomerGroups');
    Route::POST('/get/product-grades', 'getProductGrades');
    Route::POST('/get/products', 'getProducts');
    Route::POST('/get/financial-years', 'getFinancialYear');

    Route::post('/tmp/upload-image','tmpUploadImage');

    Route::POST('/country/create-form','getNewCountry');
    Route::POST('/country/create','createCountry');

    Route::POST('/states/create-form','getNewState');
    Route::POST('/states/create','createState');

    Route::POST('/districts/create-form','getNewDistrict');
    Route::POST('/districts/create','createDistrict');

    Route::POST('/taluks/create-form','getNewTaluk');
    Route::POST('/taluks/create','createTaluk');

    Route::POST('/city/create-form','getNewCity');
    Route::POST('/city/create','createCity');

    Route::POST('/postal-code/create-form','getNewPostalCode');
    Route::POST('/postal-code/create','createPostalCode');

    Route::POST('/gender/create-form','getNewGender');
    Route::POST('/gender/create','createGender');


    Route::post('/get/vehicle-type','getVehicleType');
    Route::post('/get/vehicle-brand','getVehicleBrand');
    Route::post('/get/vehicle-model','getVehicleModel');

    Route::POST('/vehicle-type/create-form','getNewVehicleType');
    Route::POST('/vehicle-type/create','createVehicleType');

    Route::POST('/vehicle-brand/create-form','getNewVehicleBrand');
    Route::POST('/vehicle-brand/create','createVehicleBrand');

    Route::POST('/vehicle-model/create-form','getNewVehicleModel');
    Route::POST('/vehicle-model/create','createVehicleModel');

    Route::POST('/tax/create-form','getNewTax');
    Route::POST('/uom/create-form','getNewUOM');

    Route::POST('/bank-type/create-form','getNewBankType');
    Route::POST('/bank-type/create','createBankType');

    Route::POST('/bank/create-form','getNewBank');
    Route::POST('/bank/create','createBank');

    Route::POST('/bank-branch/create-form','getNewBankBranch');
    Route::POST('/bank-branch/create','createBankBranch');

    Route::POST('/bank-account-type/create-form','getNewBankAccountType');
    Route::POST('/bank-account-type/create','createBankAccountType');

    Route::POST('address-form','getNewAddress');

    Route::middleware('auth')->group(function () {
        Route::post('/theme/update','themeUpdate');
        Route::POST('/financial-year/update-active','updateActiveFinancialYear');
    });
});
Route::controller(HomeAuthController::class)->group(function () {
    Route::get('/customer-register', 'Register');
    Route::get('/customer-profile', 'Profile');
    Route::post('/save', 'Save');
    Route::post('/update', 'Update');
    Route::get('/customer-home', 'Home');

    Route::post('/get/cart','getCart');
    Route::post('/add-cart','AddCart')->name('add-cart');
    Route::post('/update-cart','UpdateCart');
    Route::post('/delete-cart','DeleteCart');
    Route::get('/checkout', 'Checkout');

    Route::post('/place-order','PlaceOrder');
});
Route::controller(SocialController::class)->group(function () {
    Route::get('/social/auth/{provider}', 'redirect');
    Route::get('/social/callback/{provider}', 'callback');
});
Route::group(['prefix'=>'admin'],function (){
    Route::controller(loginController::class)->group(function () {
        Route::post('/auth/login', 'login');
    });
    Route::middleware('auth')->group(function () {
        Route::get('/', [dashboardController::class, 'dashboard']);
        Route::get('/dashboard', [dashboardController::class, 'dashboard']);
        Route::post('/dashboard/get/dashboard-stats', [dashboardController::class, 'getDashboardStats'])->name('admin.dashboard.get.dashboard-stats');
        Route::post('/dashboard/get/recent/quote-enquiry', [dashboardController::class, 'getRecentQuoteEnquiry'])->name('admin.dashboard.get.recent.quote-enquiry');
        Route::post('/dashboard/get/recent/orders', [dashboardController::class, 'getRecentOrders'])->name('admin.dashboard.get.recent.orders');
        Route::post('/dashboard/get/orders/stats', [dashboardController::class, 'getOrderStats'])->name('admin.dashboard.get.orders.stats');
        Route::post('/dashboard/get/payments/stats', [dashboardController::class, 'getPaymentStats'])->name('admin.dashboard.get.payments.stats');
        Route::get('/dashboard/get/upcoming/payments', [dashboardController::class, 'getUpcomingPayments'])->name('admin.dashboard.get.upcoming.payments');
        Route::POST('/dashboard/get/circle/stats/customer', [dashboardController::class, 'getCustomerCircleStats'])->name('admin.dashboard.get.circle.stats.customer');
        Route::POST('/dashboard/get/circle/stats/orders', [dashboardController::class, 'getOrdersCircleStats'])->name('admin.dashboard.get.circle.stats.orders');
        Route::POST('/dashboard/get/circle/stats/shipment', [dashboardController::class, 'getShipmentCircleStats'])->name('admin.dashboard.get.circle.stats.shipment');
        Route::group(['prefix'=>'settings'],function (){
            require __DIR__.'/web/settings.php';
        });
        Route::group(['prefix'=>'support'],function (){
            Route::controller(SupportController::class)->group(function () {
                Route::get('/', 'SupportView');
                Route::post('/data', 'TableView');
                Route::post('/get/details', 'getDetails');
                Route::post('/new-ticket', 'NewTicket');
                Route::post('/new-ticket/save', 'SaveTicket');
                Route::post('/details/save', 'SupportDetailsSave');
                Route::post('/delete/{SID}', 'DeleteSupport');
                Route::post('/activate/{SID}', 'ActivateSupport');
                Route::post('/deactivate/{SID}', 'DeactivateSupport');
                Route::get('/details/{SID}', 'SupportDetailsView');
            });
        });
        Route::group(['prefix'=>'orders'],function (){
                Route::get('/', [OrderController::class, 'OrderView'])->name('admin.order.index');
                Route::post('/data', [OrderController::class, 'TableView']);
                Route::get('/edit/{ID}', [OrderController::class, 'Edit'])->name('admin.order.edit');
                Route::POST('/edit/{ID}', [OrderController::class, 'Update']);
                Route::post('/get/order', [OrderController::class, 'getDetails']);
        });

        Route::group(['prefix'=>'busy'],function () {
                Route::get('/', [OrderController::class, 'BusyOrderView'])->name('admin.busy.index');
                Route::get('/data', [Controller::class, 'BusyTableView'])->name('admin.busy.view');
                Route::get('/show/{ID}', [OrderController::class, 'busyShow'])->name('admin.busy.show');
//                Route::post('/get/order', [OrderController::class, 'getDetails']);
        });
        Route::group(['prefix'=>'master'],function (){
            require __DIR__.'/web/masters/master.php';
        });
        Route::group(['prefix'=>'transaction'],function (){
            require __DIR__.'/web/transaction.php';
        });
        Route::group(['prefix'=>'home'],function (){
            require __DIR__.'/web/home.php';
        });
        Route::group(['prefix'=>'users-and-permissions'],function (){
            require __DIR__.'/web/users.php';
        });

    });
});
require __DIR__.'/auth.php';

Route::get('/about/{slug}', function ($slug) {
    $page = DB::Table('tbl_page_content')->where('slug', $slug)->select('PageName', 'PageContent')->first();
    if ($page) {
        return view('about', compact('page'));
    } else {
        return view('errors.404');
    }
});
Route::get('/cmail', [\App\Http\Controllers\Controller::class, 'order_confirmation_mail']);
Route::get('/smail', [\App\Http\Controllers\Controller::class, 'order_shipment_mail']);
Route::get('/generate-packing-label/{OrderID}', [OrderController::class, 'generatePackingLabel'])->name('generatePackingLabel');
