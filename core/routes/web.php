<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function(){
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});


Route::get('cron', 'CronController@cron')->name('cron');

// Auto-Reply Engine API (Baileys Integration)
Route::get('api/autoreply/rules/{sessionId}', 'Admin\AutoReplyController@apiFetchRules')->name('api.autoreply.rules');
Route::post('api/autoreply/log-hit/{id}', 'Admin\AutoReplyController@apiLogHit')->name('api.autoreply.log_hit');

// WhatsApp Message Sending REST API Endpoints
Route::match(['GET', 'POST'], 'api/send-message', 'Api\MessageApiController@sendMessage')->name('api.send_message');
Route::match(['GET', 'POST'], 'api/v1/send-message', 'Api\MessageApiController@sendMessage')->name('api.v1.send_message');
Route::get('api/accounts', 'Api\MessageApiController@accounts')->name('api.accounts');

// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});


Route::controller('WebController')->group(function () {
    // Account Listing
    Route::get('/account-listing', 'accountListing')->name('account.listing');
    Route::get('/account-listing/{slug}/{id}', 'accountListingDetails')->name('account.listing.details');
});

Route::controller('SiteController')->group(function () {
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');
    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');
    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');

    Route::post('subscribe', 'subscribe')->name('subscribe');
    Route::get('blog/{slug}', 'blogDetails')->name('blog.details');
    Route::get('blogs', 'blogs')->name('blogs');
    Route::get('buy-accounts', 'buyAccounts')->name('buy.account');



    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');

    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode','maintenance')->withoutMiddleware('maintenance')->name('maintenance');
    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home');
});
