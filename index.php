<?php

class broker
{
	public static function run()
	{
		// allow to enable debug mode
		if(isset($_REQUEST['debug_mode']))
		{
			ini_set('display_startup_errors', 'On');
			ini_set('error_reporting'       , 'E_ALL | E_STRICT');
			ini_set('track_errors'          , 'On');
			ini_set('display_errors'        , 1);
			error_reporting(E_ALL);
		}

		$pem_file = __DIR__.'/secret/mypemfile.pem';

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
		curl_setopt($ch, CURLOPT_USERAGENT, "IRNIC_EPP_Client_Sample");

		curl_setopt($ch, CURLOPT_URL, $apiURL);
		// turn on some setting
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		// turn off some setting
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// timeout setting
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
		curl_setopt($ch, CURLOPT_TIMEOUT, 7);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);

		$result = curl_exec($ch);
		$mycode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// error on result
		if ($result === false)
		{
			self::boboom(curl_error($ch). ':'. curl_errno($ch));
		}
		// empty result
		if (empty($result) || is_null($result))
		{
			self::boboom('Empty server response');
		}
		curl_close($ch);

		// show result with jsonBoboom
		self::jsonBoboom($result);
	}


	public static function my_data()
	{
		// get all
		$allData = $_REQUEST;
		// remove method
		unset($allData['method']);
		// send all
		return isset($allData['xml']) ? $allData['xml'] : $allData;
	}


	public static function boboom($_string = null)
	{
		// change header
		exit($_string);
	}

	public static function jsonBoboom($_result = null)
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
		self::boboom();
	}
}

\broker::run();

?>
