<?php

class broker
{
	public static function run()
	{
		$token = __DIR__.'/secret/token.conf';

		if(!is_file($token))
		{
			self::boboom('Token file not found');
		}

		if(isset($_REQUEST['token']) && $_REQUEST['token'] == trim(file_get_contents($token)))
		{
			// it's ok
		}
		else
		{
			self::boboom();
		}

		$pem_file = __DIR__.'/secret/nic.pem';

		if(!is_file($pem_file))
		{
			self::boboom('PEM file not found');
		}

		self::send($pem_file, self::my_data());
	}



	public static function send($_pem, $_data = null)
	{
		$ch = curl_init();

		if ($ch === false)
		{
			self::boboom('Curl failed to initialize');
		}

		// set some settings of curl
		$apiURL = "https://epp.nic.ir/submit";

		//The name of a file containing a PEM formatted certificate.
		curl_setopt($ch, CURLOPT_SSLCERT, $_pem);

		//The contents of the "User-Agent: "
		// curl_setopt($ch, CURLOPT_USERAGENT, "Jibres-irnic");
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Jibres irnic/1.2; +https://jibres.com/bot)");

		curl_setopt($ch, CURLOPT_URL, $apiURL);
		// turn on some setting
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		// turn off some setting
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// timeout setting
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);

		$result = curl_exec($ch);
		$mycode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// error on result
		if ($result === false)
		{
			self::boboom(curl_error($ch). ':'. curl_errno($ch), true);
		}
		// empty result
		if (empty($result) || is_null($result) || !$result)
		{
			self::boboom('Empty server response', true);
		}
		curl_close($ch);

		// show result with jsonBoom
		self::jsonBoom($result, true);
	}


	public static function my_data()
	{
		// get all
		$allData = $_REQUEST;

		// send all
		return isset($allData['xml']) ? $allData['xml'] : $allData;
	}


	public static function boboom($_string = null, $_nic_error = false)
	{
		if($_nic_error)
		{
			@header("HTTP/1.1 504 Gateway Timeout", true, 504);
		}
		else
		{
			@header("HTTP/1.1 418 I\'m a teapot", true, 418);
		}
		// change header
		exit($_string);
	}

	public static function jsonBoom($_result = null)
	{
		if(is_array($_result))
		{
			$_result = json_encode($_result, JSON_UNESCAPED_UNICODE);
		}

		if(substr($_result, 0, 1) === "{")
		{
			@header("Content-Type: application/json; charset=utf-8");
		}
		echo $_result;
		exit();
	}
}

\broker::run();

?>
