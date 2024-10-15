<?php

return [
	'driver'      => env('FCM_PROTOCOL', 'http'),
	'log_enabled' => true,

	'http' => [
		'server_key'       => 'AAAAASF5QPs:APA91bHNNhMaGojGsRoRm2mldBOZlEptpAbfKubp8T49P8_p1IwB7GLj7we3-YMvrbmOClIU6AgVKaqx2y3XIgObKJbVw5CG0swk8pn4e_R1QUNlNAKBPKy_iPOnL_2SmjZIBUJzcbzt',//env('FCM_SERVER_KEY', 'Your FCM server key'),
		'sender_id'        => '4856561915',//env('FCM_SENDER_ID', 'Your sender id'),
		'server_send_url'  => 'https://fcm.googleapis.com/fcm/send',
		'server_group_url' => 'https://android.googleapis.com/gcm/notification',
		'timeout'          => 30.0, // in second
	]
];
