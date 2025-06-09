<?php
class Sms
{
    private static $login = 'oem@example.com';
    private static $key   = 'password_key';
    private static $from  = 'OEM';
    private static $log_file = '/var/www/spsend/logs/sms.log';

    public static function send($number, $text)
    {
        $log = date('Y-m-d H:i:s');
	$result = array();
	if (!empty($number)) {
	    $numbers = explode(',', $number);
	    foreach ($numbers as $row) {
		$row = preg_replace('/[^0-9]/', '', $row);
		if (!empty($row)) {
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		    curl_setopt($ch, CURLOPT_USERPWD, self::$login . ':' . self::$key);
		    curl_setopt($ch, CURLOPT_URL, 'https://gate.smsaero.ru/v2/sms/send?number=' . urlencode($row) . '&text=' . urlencode($text) . '&sign=' . self::$from);
		    $res = curl_exec($ch);
		    curl_close($ch);
		    $log .= $res;
		    $result[$row]=$res;
		    file_put_contents(self::$log_file, $log . "\r\n\r\n", FILE_APPEND);
		}
	    }
	} else { return; }
	return $result;
    }
}
