<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('human_readable_date'))
{
	/**
	 * Lang
	 *
	 * Fetches a language variable and optionally outputs a form label
	 *
	 * @param	string	$line		The language line
	 * @param	string	$for		The "for" value (id of the form element)
	 * @param	array	$attributes	Any additional HTML attributes
	 * @return	string
	 */
	function human_readable_date($timespan)
	{
		date_default_timezone_set('Europe/London');

		/*$now = time();
		if($now - $timespan < 3600 * 168) {
			$ci =& get_instance();
    		$ci->load->helper('date');
			$retVal = timespan($timespan, $now). ' ago';
		}
		else {
			$retVal = date('d/m/Y - H:i:s', $timespan);
		}*/
		return time_elapsed_string($timespan);
	}
	
	function time_elapsed_string($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime();
		$ago->setTimestamp($datetime);
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}

		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}
}