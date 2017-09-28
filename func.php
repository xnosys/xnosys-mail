<?php
	
	return function () {
		
		$mailgun = function ($email, $credentials) {
			// email: from, to, cc, bcc, subject, text, html
			$curl = curl_init();
			curl_setopt_array(
				$curl,
				array(
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_HEADER => true,
					CURLOPT_HTTPHEADER => array('Expect:'),
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_NOBODY => true,
					CURLOPT_URL => 'https://api:'.$credentials['key'].'@api.mailgun.net/v2/'.$credentials['username'].'/messages',
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $email
				)
			);
			$status = explode(' ', curl_exec($curl))[1];
			curl_close($curl);
			if (!((strlen($status) > 0) && (intval($status) === 200))) {
				return array('error: '.basename(__DIR__).'_'.basename(__FILE__, '.php').':'.__LINE__);
			}
			return array(null, true);
		};
		
		$sendmail = function ($email) {
			// email: name, from, to, subject, text
			if (!mail($email['to'], $email['subject'], wordwrap($email['text'], 70, "\r\n"), 'From: '.(strlen($email['name']) ? $email['name'].' ' : '').'<'.$email['from'].'>')) {
				return array('error: '.basename(__DIR__).'_'.basename(__FILE__, '.php').':'.__LINE__);
			}
			return array(null, true);
		};
		
		$send = function ($email, $service, $credentials) use ($sendmail, $mailgun) {
			switch (true) {
				case ($service === 'mailgun'):
					list($error, $result) = $mailgun($email, $credentials);
					if (!!$error) { return array($error); }
					return array(null, $result);
				default:
					list($error, $result) = $sendmail($email);
					if (!!$error) { return array($error); }
					return array(null, $result);
			}
		};
		
		return array(
			'send' => $send
		);
		
	};
	
?>