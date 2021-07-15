<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjUtil extends pjToolkit
{
	static public function getReferer()
	{
		if (isset($_GET['_escaped_fragment_']))
		{
			if (isset($_SERVER['REDIRECT_URL']))
			{
				return $_SERVER['REDIRECT_URL'];
			}
		}
		
		if (isset($_SERVER['HTTP_REFERER']))
		{
			$pos = strpos($_SERVER['HTTP_REFERER'], "#");
			if ($pos !== FALSE)
			{
				return substr($_SERVER['HTTP_REFERER'], 0, $pos);
			}
			return $_SERVER['HTTP_REFERER'];
		}
	}
	static public function hoursToSeconds($hour)
	{
    	$parse = array();
		if (!preg_match ('#^(?<hours>[\d]{2}):(?<mins>[\d]{2}):(?<secs>[\d]{2})$#',$hour,$parse))
		{
			throw new RuntimeException ("Hour Format not valid");
		}
		return (int) $parse['hours'] * 3600 + (int) $parse['mins'] * 60 + (int) $parse['secs'];
	}
	
	static public function getClientIp()
	{
		if (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			return $_SERVER['HTTP_CLIENT_IP'];
		} else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if(isset($_SERVER['HTTP_X_FORWARDED'])) {
			return $_SERVER['HTTP_X_FORWARDED'];
		} else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_FORWARDED_FOR'];
		} else if(isset($_SERVER['HTTP_FORWARDED'])) {
			return $_SERVER['HTTP_FORWARDED'];
		} else if(isset($_SERVER['REMOTE_ADDR'])) {
			return $_SERVER['REMOTE_ADDR'];
		}

		return 'UNKNOWN';
	}
	
	static public function textToHtml($content)
	{
        $content = preg_replace('/\r\n|\n/', '<br />', $content);
        $logo = PJ_INSTALL_URL.'app/web/img/mazhandu-logo-active.png';

		return '<html><head><title></title></head><body><div style="margin-bottom:10px;"><img src="'.$logo.'"/></div><div style="margin-bottom:20px;font-family:Poppins,Arial sans-serif;font-weight:400;font-size:50px;color:#0785f2;">Mazhandu Family Bus Services</div><div>'.$content.'</div></body></html>';
	}
	
	public static function getTitles(){
		$arr = array();
		$arr[] = 'mr';
		$arr[] = 'mrs';
		$arr[] = 'ms';
		$arr[] = 'dr';
		$arr[] = 'prof';
		$arr[] = 'rev';
		$arr[] = 'other';
		return $arr;
	}
	
	public static function sqlWeekDays()
	{
		$arr = array();
		$arr[1] = 2;
		$arr[2] = 3;
		$arr[3] = 4;
		$arr[4] = 5;
		$arr[5] = 6;
		$arr[6] = 7;
		$arr[7] = 1;
		return $arr;
	}
	public static function phpWeekDays()
	{
		$arr = array();
		$arr[1] = 7;
		$arr[2] = 1;
		$arr[3] = 2;
		$arr[4] = 3;
		$arr[5] = 4;
		$arr[6] = 5;
		$arr[7] = 6;
		return $arr;
	}
	public static function getWeekDays()
	{
		$arr = array();
		$arr[1] = 'monday';
		$arr[2] = 'tuesday';
		$arr[3] = 'wednesday';
		$arr[4] = 'thursday';
		$arr[5] = 'friday';
		$arr[6] = 'saturday';
		$arr[7] = 'sunday';
		return $arr;
	}
	
	public static function calSeconds($start_time, $end_time, $day_num=null)
	{
		$startTimestamp = strtotime($start_time);
        $endTimestamp = strtotime($end_time);
        if ($day_num) {
            $endTimestamp += $day_num * 86400; //add 24 hrs to arrival time for each next day
        } else if($startTimestamp > $endTimestamp) {
            $endTimestamp += 86400; 
        }
        
		return $seconds = $endTimestamp - $startTimestamp;
	}
	
	public static function calDuration($start_time, $end_time)
	{
		$result = array();
		
		list($hours, $minutes, $seconds) = explode(':', $start_time);
		$startTimestamp = mktime($hours, $minutes, $seconds);
		
		list($hours, $minutes, $seconds) = explode(':', $end_time);
		$endTimestamp = mktime($hours, $minutes, $seconds);
		
		$seconds = $endTimestamp - $startTimestamp;
		$minutes = ($seconds / 60) % 60;
		$hours = floor($seconds / (60 * 60));
		
		return compact("seconds", "hours", "minutes");
	}
	
	public static function calDays($start_date, $end_date)
	{
		$startTimeStamp = strtotime($start_date);
		$endTimeStamp = strtotime($end_date);
		
		$timeDiff = abs($endTimeStamp - $startTimeStamp);
		$numberDays = $timeDiff/86400;
		
		$numberDays = intval($numberDays);
		return $numberDays;
	}
	
	public static function checkDateFormat($date)
	{
		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date))
	    {
	        return true;
	    }else{
	        return false;
	    }
	}
	
	public static function getMaxUploadedSize()
	{
		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$memory_limit = (int)(ini_get('memory_limit'));
		$upload_mb = min($max_upload, $max_post, $memory_limit);
		return $upload_mb;
	}
	
	static public function getPostMaxSize()
	{
		$post_max_size = ini_get('post_max_size');
		switch (substr($post_max_size, -1))
		{
			case 'G':
				$post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
				break;
			case 'M':
				$post_max_size = (int) $post_max_size * 1024 * 1024;
				break;
			case 'K':
				$post_max_size = (int) $post_max_size * 1024;
				break;
		}
		return $post_max_size;
	}
	
	static public function getWeekRange($week_start)
	{
		$week_arr = array(0=>'sunday',
						  1=>'monday',
						  2=>'tuesday',
						  3=>'wednesday',
						  4=>'thursday',
						  5=>'friday',
						  6=>'saturday');
		$ts = strtotime(date('Y-m-d'));
		if(date('w') != $week_start)
		{
	    	$start = (date('w', $ts) == 0) ? $ts : strtotime('last ' . $week_arr[$week_start], $ts);
		}else{
			$start = $ts;
		}
	    $week_start = ($week_start == 0 ? 6 : $week_start -1);
	    return array(date('Y-m-d', $start), date('Y-m-d', strtotime('next ' . $week_arr[$week_start], $start)));
	}
	
	public static function changeLangField($i18n_arr, $new_field, $old_field)
	{
		foreach($i18n_arr as $locale => $content)
		{
			$content[$new_field] =  $content[$old_field];
			unset($content[$old_field]);
			$i18n_arr[$locale] = $content;
		}
		return $i18n_arr;
	}
	public static function toMomemtJS($format)
	{
		$f = str_replace(
				array('Y', 'm', 'n', 'd', 'j'),
				array('yyyy', 'mm', 'm', 'dd', 'd'),
				$format
		);
	
		return $f;
	}
}
?>