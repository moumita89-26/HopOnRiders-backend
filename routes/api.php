<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Mobile apis
Route::group(['namespace' => '\App\Http\Controllers\Api'], function () {

    Route::get('autoCancelRide', 'CronControllers@autoCancelRide');
    Route::get('autoCancellTrip', 'CronControllers@autoCancellTrip');
    Route::get('suspendDriverIfRequired', 'CronControllers@suspendDriverIfRequired');

    // user apis
    Route::post('register', 'UserController@register');
    Route::post('login', 'UserController@login');
    Route::post('updateProfile', 'UserController@updateProfile');
    Route::post('updateProfileImage', 'UserController@updateProfileImage');
    Route::post('userDetails', 'UserController@userDetails');
    Route::post('changePassword', 'UserController@changePassword');
    Route::post('deleteAccount', 'UserController@deleteAccount');
    Route::post('sendMobileOTP', 'UserController@sendMobileOTP');
    Route::post('verifyOTP', 'UserController@verifyOTP');
    Route::post('sendEmailOTP', 'UserController@sendEmailOTP');
    Route::post('verifyEmailOTP', 'UserController@verifyEmailOTP');
    Route::post('updateCartInfo', 'UserController@updateCartInfo');
    Route::post('forgotPassword', 'UserController@forgotPassword');
    Route::post('updateOnlineStatus', 'UserController@updateOnlineStatus');
    Route::post('driverCarInformation', 'UserController@driverCarInformation');

    // Home apis 
    Route::post('home', 'HomeController@home');
    Route::post('slugPage', 'HomeController@slugPage');

    // ride routes
    Route::post('createRide', 'RideController@createRide');
    Route::post('editRide', 'RideController@editRide');
    Route::post('rideList', 'RideController@rideList');
    Route::post('rideDetails', 'RideController@rideDetails');
    Route::post('searchRide', 'RideController@searchRide');
    Route::post('activeUpcoming', 'RideController@activeUpcoming');
    Route::post('activeUpcomingTwo', 'RideController@activeUpcomingTwo');
    Route::post('activeUpcomingTest', 'RideController@activeUpcomingTest');
    Route::post('updateStatus', 'RideController@updateStatus');
    Route::post('searchRideUser', 'RideController@searchRideUser');
    Route::post('updateRideDrop', 'RideController@updateRideDrop');
    Route::post('updateTripDrop', 'RideController@updateTripDrop');
    Route::post('myEarning', 'RideController@myEarning');
    Route::post('myTotalEarnings', 'RideController@myTotalEarnings');

    // trip request 
    Route::post('requestTrip', 'TripRequestController@requestTrip');
    Route::post('requestedTripDetails', 'TripRequestController@requestedTripDetails');
    Route::post('requestedTripList', 'TripRequestController@requestedTripList');
    Route::post('editRequestedTrip', 'TripRequestController@editRequestedTrip');

    // Coupon
    Route::post('couponList', 'CouponController@couponList');
    Route::post('applyCoupon', 'CouponController@applyCoupon');

    // common api
    Route::post('cartTypeList', 'CommonController@cartTypeList');
    Route::post('cartDetails', 'CommonController@cartDetails');
    Route::post('driverDetails', 'CommonController@driverDetails');
    Route::post('countryCode', 'CommonController@countryCode');
    Route::post('SendSms', 'CommonController@SendSms');
    Route::post('SOSSendSms', 'CommonController@SOSSendSms');
    Route::post('MoneyCollection', 'CommonController@MoneyCollection');
    Route::post('collectMoney', 'CommonController@collectMoney');
    Route::post('lookupMnoAccount', 'CommonController@lookupMnoAccount');
    Route::post('statusMoney', 'CommonController@statusMoney');
    Route::post('statusCallback', 'CommonController@statusCallback');
    Route::post('driverSettlements', 'DriverSettlementController@index');
    Route::post('customerRefunds', 'CustomerRefundController@index');
    Route::post('customerDirectDriverPayment', 'CustomerDirectPaymentController@store');
    Route::post('getCharges', 'CommonController@getCharges');

    // Bid & Booking
    Route::post('bidTrip', 'TripBidController@bidTrip');
    Route::post('bidTripAccept', 'TripBidController@bidTripAccept');
    Route::post('bidTripReject', 'TripBidController@bidTripReject');
    Route::post('tripStatusUpdate', 'TripBidController@tripStatusUpdate');

    Route::get('tripRideStatusUpdateCron', 'TripBidController@tripRideStatusUpdateCron');
    // 59 23 * * * /usr/bin/curl -X GET https://developersattesting.com/p4/HopOnRiders/public/api/tripRideStatusUpdateCron >> /var/log/tripRideStatusUpdateCron.log post ok

    // booking
    Route::post('bookRide', 'BookingController@bookRide');
    Route::post('verifyStartPin', 'BookingController@verifyStartPin');
    Route::post('verifyEndPin', 'BookingController@verifyEndPin');
    Route::post('verifyTripStartPin', 'BookingController@verifyTripStartPin');
    Route::post('verifyTripEndPin', 'BookingController@verifyTripEndPin');

    // reiview
    Route::post('submitReview', 'RatingController@submitReview');

    // notification
    Route::post('notificationList', 'NotificationController@notificationList');
    Route::post('notificationRead', 'NotificationController@notificationRead');
    Route::post('notificationCount', 'NotificationController@notificationCount');
    Route::post('sendNotification', 'NotificationController@sendNotification');
});
