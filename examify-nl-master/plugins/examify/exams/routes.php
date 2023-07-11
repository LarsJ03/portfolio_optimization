<?php
	use Examify\Exams\Models\Orders as Orders;
	use Examify\Exams\Models\Exams as Exams;

	Route::name('webhooks.mollie')->post('exawebhooks/mollie', '\Examify\Exams\Controllers\MollieWebhookController@handle');

	Route::name('redirects.mollie')->get('redirect-payments/{name}', function ($name) {
		return redirect('/mijn-account#' . $name);
	});

  // important!!! USE MIDDLEWARE('web') otherwise the user is not recognized...
	Route::get('/stream/{psid}', '\Examify\Exams\Controllers\StreamController@timerLeft')->middleware('web');

	Route::post('/practice-start', '\Examify\Exams\Controllers\PracticeSessions@new')->middleware('web');
	Route::post('/trial-start', '\Examify\Exams\Controllers\PracticeSessions@new_trial')->middleware('web');

	Route::get('/portal/select-school/{schoolid}', '\Examify\Exams\Controllers\SchoolSelector@change')->middleware('web');
?>