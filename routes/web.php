<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Artisan;

Route::get('clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    echo "Compiled views cleared!<br>Application cache cleared!<br>Route cache cleared!<br>Configuration cache cleared!<br>Caches cleared successfully!";
});

//Social Authentication for frontend
Route::get('auth/facebook', [SocialAuthController::class, 'redirect']);
Route::get('auth/facebook/callback', [SocialAuthController::class, 'callback']);
Route::get('auth/{provider}', [SocialAuthController::class, 'redirectToProvider']);
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);



Route::get('/', function () {
    return redirect('dashboard');
});

//==== Admin route==//
Route::group(['namespace' => '\App\Http\Controllers\Admin'], function () {
    Route::post('unlock-screen', ['uses' => 'AdminController@postUnlockScreen', 'as' => 'postUnlockScreen']);
    Route::get('lock-screen', ['uses' => 'AdminController@getLockscreen', 'as' => 'getLockScreen']);
    Route::post('forgot', ['uses' => 'AdminController@postForgot', 'as' => 'postForgot']);
    Route::get('forgot', ['uses' => 'AdminController@getForgot', 'as' => 'getForgot']);
    Route::post('register', ['uses' => 'AdminController@postRegister', 'as' => 'postRegister']);
    Route::get('register', ['uses' => 'AdminController@getRegister', 'as' => 'getRegister']);
    Route::get('logout', ['uses' => 'AdminController@getLogout', 'as' => 'getLogout']);
    Route::post('login', ['uses' => 'AdminController@postLogin', 'as' => 'postLogin']);
    Route::get('login', ['uses' => 'AdminController@getLogin', 'as' => 'getLogin']);

    //Ajax Request
    Route::group(['prefix' => 'ajax'], function () {
        Route::get('get-cities/{country_id}', 'AjaxRequestController@getCities');
        Route::get('get-buildings/{city_id}', 'AjaxRequestController@getBuildings');
        Route::get('get-companies/{country_id}/{city_id}', 'AjaxRequestController@getCompanies');
        Route::get('get-food-items/{cat_id}', 'AjaxRequestController@getFooditems');
        Route::post('getfoodItems', 'AjaxRequestController@getFooditemList');
    });
});

Route::group(['middleware' => ['admin.auth'], 'namespace' => '\App\Http\Controllers\Admin'], function () {
    Route::get('/', ['uses' => 'AdminController@getIndex', 'as' => 'getIndex']);
    Route::get('/dashboard', ['uses' => 'AdminController@getIndex', 'as' => 'getAdminDashboard']);
    Route::get('/dashboard', ['uses' => 'AdminController@getIndex', 'as' => 'getAdminProfile']);
    //Menu Management
    Route::get('menu-management', ['uses' => 'MenusController@getIndex', 'as' => 'getMenus']);
    Route::post('menu-management/add-save-menu', ['uses' => 'MenusController@postAddSave', 'as' => 'postAddMenu']);
    Route::get('menu-management/edit/{id}', ['uses' => 'MenusController@editMenu', 'as' => 'getEditMenu']);
    Route::get('menu-management/delete/{id}', ['uses' => 'MenusController@deleteMenu', 'as' => 'deleteMenu']);
    Route::post('menu-management/edit-save-menu/{id}', ['uses' => 'MenusController@postUpdateSave', 'as' => 'postUpdateMenu']);

    //Admin Users
    Route::get('admin-users', ['uses' => 'AdminUsersController@getIndex', 'as' => 'getAdminUsers']);
    Route::get('admin-users/add', ['uses' => 'AdminUsersController@getAdd', 'as' => 'getAddAdminUser']);
    Route::post('admin-users/add-save', ['uses' => 'AdminUsersController@postAddSave', 'as' => 'postAddSaveAdminUser']);
    Route::get('admin-users/edit/{id}', ['uses' => 'AdminUsersController@getEdit', 'as' => 'getEditAdminUser']);
    Route::post('admin-users/update-save/{id}', ['uses' => 'AdminUsersController@postUpdateSave', 'as' => 'postUpdateSaveAdminUser']);
    Route::get('admin-users/delete/{id}', ['uses' => 'AdminUsersController@getDelete', 'as' => 'getDeleteAdminUser']);

    //Privileges
    Route::get('privileges', ['uses' => 'MenusController@getPrivilege', 'as' => 'getPrivilege']);
    Route::get('privileges/add', ['uses' => 'MenusController@getAddPrivilege', 'as' => 'getAddPrivilege']);
    Route::post('privileges/add-save-privilege', ['uses' => 'MenusController@postAddPrivilege', 'as' => 'postAddPrivilege']);
    Route::get('privileges/edit/{id}', ['uses' => 'MenusController@getEditPrivilege', 'as' => 'getEditPrivilege']);
    Route::get('privileges/delete/{id}', ['uses' => 'MenusController@getDeletePrivilege', 'as' => 'getDeletePrivilege']);
    Route::post('privileges/edit-save-privilege/{id}', ['uses' => 'MenusController@postUpdatePrivilege', 'as' => 'postUpdatePrivilege']);

    //Settings
    Route::get('general-settings', ['uses' => 'SettingsController@getGeneralSettings', 'as' => 'getGeneralSettings']);
    Route::post('general-settings/save-general-settings', ['uses' => 'SettingsController@postSaveGeneralSettings', 'as' => 'postSaveGeneralSettings']);
    Route::get('email-settings', ['uses' => 'SettingsController@getEmailSettings', 'as' => 'getEmailSettings']);
    Route::post('email-settings/save', ['uses' => 'SettingsController@postSaveEmailSettings', 'as' => 'postSaveEmailSettings']);
    Route::get('homepage-settings', ['uses' => 'SettingsController@getHomeSettings', 'as' => 'getHomeSettings']);
    Route::post('homepage-settings/save', ['uses' => 'SettingsController@postSaveHomeSettings', 'as' => 'postSaveHomeSettings']);

    //Download & Delete File
    Route::get('download-file', 'AdminController@download_file');
    Route::get('delete-image', 'AdminController@delete_file');

    //admin profile
    Route::get('profile', ['uses' => 'ProfileController@getProfileData', 'as' => 'getProfileData']);
    Route::post('save-profile', ['uses' => 'ProfileController@postSaveProfile', 'as' => 'postSaveProfile']);

    //Notification
    Route::get('notifications', ['uses' => 'NotificationController@getIndex'])->name('admin.notification');
    Route::get('notifications/read/{id}', ['uses' => 'NotificationController@readNotification'])->name('admin.read_notification');
    Route::get('notifications/delete/{id}', ['uses' => 'NotificationController@deleteNotification'])->name('admin.delete_notification');
    Route::post('notifications/action-selected', ['uses' => 'NotificationController@postActionSelected'])->name('admin.action_selected_notification');

    //Enquiry
    Route::get('manage-enquiry', ['uses' => 'ManageEnquiry@getIndex', 'as' => 'getManageEnquiry']);
    Route::get('manage-enquiry/delete/{id}', ['uses' => 'ManageEnquiry@getDelete', 'as' => 'deleteEnquiry']);
    Route::post('manage-enquiry/action-selected', ['uses' => 'ManageEnquiry@postActionSelected']);

    //CMS Management
    Route::get('manage-cms', ['uses' => 'ManageCMSController@getIndex', 'as' => 'getManageCMS']);
    Route::get('manage-cms/add', ['uses' => 'ManageCMSController@getAdd', 'as' => 'getAddCms']);
    Route::post('manage-cms/add-save-cms', ['uses' => 'ManageCMSController@postAddSave', 'as' => 'postAddCms']);
    Route::get('manage-cms/detail/{id}', ['uses' => 'ManageCMSController@getDetail', 'as' => 'getDetailCms']);
    Route::get('manage-cms/edit/{id}', ['uses' => 'ManageCMSController@getEdit', 'as' => 'getEditCms']);
    Route::post('manage-cms/edit-save-cms/{id}', ['uses' => 'ManageCMSController@postUpdateSave', 'as' => 'postUpdateCms']);
    Route::get('manage-cms/delete/{id}', ['uses' => 'ManageCMSController@getDelete', 'as' => 'deleteCms']);
    Route::post('manage-cms/action-selected', ['uses' => 'ManageCMSController@postActionSelected', 'as' => 'actionSelectedCms']);

    //Manage Email templates
    Route::get('email-templates', ['uses' => 'ManageEmailTemplates@getIndex', 'as' => 'getIndexEmailTemplate']);
    Route::get('email-templates/add', ['uses' => 'ManageEmailTemplates@getAdd', 'as' => 'addEmailTemplate']);
    Route::post('add-save-email-templates', ['uses' => 'ManageEmailTemplates@postAddSave', 'as' => 'postAddEmailTemplate']);
    Route::get('email-templates/detail/{id}', ['uses' => 'ManageEmailTemplates@getDetail', 'as' => 'getDetailEmailTemplate']);
    Route::get('email-templates/edit/{id}', ['uses' => 'ManageEmailTemplates@getEdit', 'as' => 'getEditEmailTemplate']);
    Route::post('edit-save-email-templates/{id}', ['uses' => 'ManageEmailTemplates@postUpdateSave', 'as' => 'postUpdateEmailTemplate']);
    Route::get('email-templates/delete/{id}', ['uses' => 'ManageEmailTemplates@deleteDuration', 'as' => 'deleteManageEmailTemplate']);
    Route::post('email-templates/action-selected', ['uses' => 'ManageEmailTemplates@postActionSelected', 'as' => 'actionSelectedManageEmailTemplate']);
    Route::get('email-templates/action-selected', ['uses' => 'ManageEmailTemplates@postActionSelected', 'as' => 'actionSelectedManageEmailTemplate']);


    //Country Management
    Route::get('manage-country', ['uses' => 'ManageCountry@getIndex', 'as' => 'getManageCountry']);
    Route::get('manage-country/add', ['uses' => 'ManageCountry@getAdd', 'as' => 'getAddCountry']);
    Route::post('manage-country/add-save', ['uses' => 'ManageCountry@postAddSave', 'as' => 'postAddCountry']);
    Route::get('manage-country/detail/{id}', ['uses' => 'ManageCountry@getDetail', 'as' => 'getDetailCountry']);
    Route::get('manage-country/edit/{id}', ['uses' => 'ManageCountry@getEdit', 'as' => 'getEditCountry']);
    Route::post('manage-country/edit-save/{id}', ['uses' => 'ManageCountry@postUpdateSave', 'as' => 'postUpdateCountry']);
    Route::get('manage-country/delete/{id}', ['uses' => 'ManageCountry@getDelete', 'as' => 'deleteCountry']);
    Route::post('manage-country/action-selected', ['uses' => 'ManageCountry@postActionSelected', 'as' => 'actionSelectedCountry']);



    //User Management
    Route::get('manage-users', ['uses' => 'ManageUserController@getIndex', 'as' => 'getManageUser']);
    Route::get('manage-users/update-status/{id}/{status}', ['uses' => 'ManageUserController@updateProfileStatus', 'as' => 'updateProfileStatus']);
    Route::get('manage-users/add', ['uses' => 'ManageUserController@getAdd', 'as' => 'getAddUser']);
    Route::get('manage-users/review/{id}', ['uses' => 'ManageUserController@getUserReview', 'as' => 'getUserReview']);
    Route::get('manage-users/review/delete/{id}', ['uses' => 'ManageUserController@deleteReview', 'as' => 'deleteReview']);
    Route::post('manage-users/add-save', ['uses' => 'ManageUserController@postAddSave', 'as' => 'postAddUser']);
    Route::get('manage-users/detail/{id}', ['uses' => 'ManageUserController@getDetail', 'as' => 'getDetailUser']);
    Route::get('manage-users/edit/{id}', ['uses' => 'ManageUserController@getEdit', 'as' => 'getEditUser']);
    Route::post('manage-users/edit-save/{id}', ['uses' => 'ManageUserController@postUpdateSave', 'as' => 'postUpdateUser']);
    Route::get('manage-users/images/{id}', ['uses' => 'ManageUserController@getUserImages', 'as' => 'getUserImages']);
    Route::get('delete-User-image/{id}', ['uses' => 'ManageUserController@deleteUserImage', 'as' => 'deleteUserImage']);
    Route::post('upload-User-image/{id}', ['uses' => 'ManageUserController@uploadUserImage', 'as' => 'uploadUserImage']);
    Route::get('manage-users/delete/{id}', ['uses' => 'ManageUserController@getDelete', 'as' => 'deleteUser']);
    Route::post('manage-users/action-selected', ['uses' => 'ManageUserController@postActionSelected', 'as' => 'actionSelectedUser']);

    //driver Ride
    Route::get('manage-ride', ['uses' => 'ManageRideController@getIndex', 'as' => 'getManageRide']);
    Route::get('manage-ride/edit/{id}', ['uses' => 'ManageRideController@getEdit', 'as' => 'getEditRide']);
    Route::post('manage-ride/save/{id}', ['uses' => 'ManageRideController@postUpdateSave', 'as' => 'postUpdateRide']);
    Route::get('manage-ride/detail/{id}', ['uses' => 'ManageRideController@getDetail', 'as' => 'getManageRideDetails']);

    //report Ride
    Route::get('report', ['uses' => 'ReportController@getIndex', 'as' => 'getManageReport']);

    Route::get('settlements', ['uses' => 'SettlementController@index', 'as' => 'admin.settlements.index']);
    Route::post('settlements/customers/{customer}/refund', ['uses' => 'SettlementController@refundCustomer', 'as' => 'admin.settlements.refund-customer']);
    Route::post('settlements/drivers/{driver}/pay', ['uses' => 'SettlementController@payDriver', 'as' => 'admin.settlements.pay-driver']);
    Route::post('settlements/{driverPayout}/settle', ['uses' => 'SettlementController@settle', 'as' => 'admin.settlements.settle']);

    Route::get('update-payout-trip/{id}', ['uses' => 'ReportController@updatePayoutTrip', 'as' => 'updatePayoutTrip']);
    Route::get('update-refunds-trip/{id}', ['uses' => 'ReportController@updateRefundsTrip', 'as' => 'updateRefundsTrip']);

    Route::get('update-payout-ride/{id}', ['uses' => 'ReportController@updatePayoutRide', 'as' => 'updatePayoutRide']);
    Route::get('update-refunds-ride/{id}', ['uses' => 'ReportController@updateRefundsRide', 'as' => 'updateRefundsRide']);

    Route::get('safety-incident', ['uses' => 'PaymentController@getIndex', 'as' => 'getSafetyIncident']);

    //User trip
    Route::get('manage-trip', ['uses' => 'ManageTripRequestController@getIndex', 'as' => 'getManageTrip']);
    Route::get('manage-trip/detail/{id}', ['uses' => 'ManageTripRequestController@getDetail', 'as' => 'getManageTripDetails']);
    Route::get('manage-trip/bid-details/{id}', ['uses' => 'ManageTripRequestController@getBidRequest', 'as' => 'getManageBidDetails']);

    //Reports
    Route::get('sales-reports', ['uses' => 'ReportController@salesReport', 'as' => 'adminSalesReport']);
    Route::get('customer-reports', ['uses' => 'ReportController@customerReports', 'as' => 'adminCustomerReport']);
    Route::get('traffic-reports', ['uses' => 'ReportController@trafficReports', 'as' => 'adminTrafficReport']);
    Route::get('payment-reports', ['uses' => 'ReportController@paymentReports', 'as' => 'adminPaymentReport']);
});
