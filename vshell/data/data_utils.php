<?php

/* Given a timestamp calculate how long ago, in seconds, that timestamp was.
 * Returns a human readable version of that difference
 */
function calculate_duration($beginning)
{
        $now = time();
        $duration = ($now - $beginning);
        //$retval = date('d\d-H\h-i\m-s\s', $duration);
        $retval = coarse_time_calculation($duration);
        return $retval;
}

function coarse_time_calculation($duration)
{
    $seconds_per_minute = 60;
    $seconds_per_hour = $seconds_per_minute * $seconds_per_minute;
    $seconds_per_day = 24*$seconds_per_hour;

    $remaining_duration = $duration;
    $days = (int)($remaining_duration/$seconds_per_day);
    $remaining_duration -= $days*$seconds_per_day;
    $hours = (int)($remaining_duration/$seconds_per_hour);
    $remaining_duration -= $hours*$seconds_per_hour;
    $minutes = (int)($remaining_duration/$seconds_per_minute);
    $remaining_duration -= $minutes*$seconds_per_minute;
    $seconds = (int)$remaining_duration;

    $retval = '';
    if ($days > 0) { $retval .= sprintf('%d%s', $days,'d-'); }
    if ($hours > 0 || $days > 0) { $retval .= sprintf('%d%s', $hours, 'h-'); }
    if ($minutes > 0 || $days > 0 || $hours > 0) { $retval .= sprintf('%d%s', $minutes, 'm-'); }
    if ($seconds > 0 || $minutes > 0 || $days > 0 || $hours > 0) { $retval .= sprintf('%d%s', $seconds,'s'); }
    return $retval;
}

/* 	
*	@author Ethan Galstad
*	function modified from Ethan Galstad's original 'grab_request_var' function Nagios XI project
*	basic cleaner function for request variables 
*/
function grab_request_var($varname,$default="")
{	
	$v=$default;
	if(isset($_REQUEST[$varname])) 
	{
		if(is_array($_REQUEST[$varname]))
		{
			@array_walk($_REQUEST[$varname],'htmlentities',ENT_QUOTES); 
			$v=$request[$varname];
		}
		else
			$v=htmlentities($_REQUEST[$varname],ENT_QUOTES);

	}
	//echo "VAR $varname = $v<BR>";
	return $v;
	}

// gets value from array using default
// @author Ethan Galstad
//
function grab_array_var($arr,$varname,$default=""){
	global $request;
	
	$v=$default;
	if(is_array($arr)){
		if(array_key_exists($varname,$arr))
			$v=$arr[$varname];
		}
	return $v;
}
	
	
	
/*
*	dumps a formatted array to the browser
*	@author Mike Guthrie
*/	
function dump($array)
{
	print "<pre>".print_r($array,true)."</pre>"; 
}


/**
*	splits line of status file into key value pair and trims strings
*	@param string $line the line being processed by the parser
*	@return mixed $array string $key, string $value
*/
function get_key_value($line) {
	$strings = explode('=', $line,2);			
	$key = trim($strings[0]);
	$value = trim($strings[1]);
	return array($key,$value); 

}


/** Given the raw data for a collected host process it into usable information
 * Maps host states from integers into "standard" nagios values
 * Assigns to each collected service a hostID
 */
function process_host_status_keys($rawdata)
{

	static $hostindex = 1;
	$processed_data = get_standard_values($rawdata, array('host_name', 'plugin_output', 'scheduled_downtime_depth', 'problem_has_been_acknowledged'));
	
	$processed_data['hostID'] = 'Host'.$hostindex++;
	
	$host_states = array( 0 => 'UP', 1 => 'DOWN', 2 => 'UNREACHABLE', 3 => 'UNKNOWN' );
	if($rawdata['current_state'] == 0 && $rawdata['last_check'] == 0)//added conditions for pending state -MG
	{ 
		$processed_data['current_state'] = 'PENDING'; 
		$processed_data['plugin_output']="No data received yet";
		$processed_data['duration']="N/A";
		$processed_data['attempt']="N/A";
		$processed_data['last_check']="N/A";
	} 
	else { $processed_data['current_state'] = state_map($rawdata['current_state'], $host_states); }
 
	return $processed_data;
}

/* Given the raw data for a collected service process it into usable information
 * Maps service states from integers into "standard" nagios values
 * Assigns to each collected service a serviceID
 */
function process_service_status_keys($rawdata)
{

	static $serviceindex = 0;
	$processed_data = get_standard_values($rawdata, array('host_name', 'plugin_output', 'scheduled_downtime_depth', 'service_description', 'problem_has_been_acknowledged'));
	
	$processed_data['serviceID'] = 'service'.$serviceindex++;
	//print "$serviceindex<br />";
	$service_states = array( 0 => 'OK', 1 => 'WARNING', 2 => 'CRITICAL', 3 => 'UNKNOWN' );
	if($rawdata['current_state'] == 0 && $rawdata['last_check'] == 0)//added conditions for pending state -MG
	{ 
		$processed_data['current_state'] = 'PENDING'; 
		$processed_data['plugin_output']="No data received yet";
		$processed_data['duration']="N/A";
		$processed_data['attempt']="N/A";
		$processed_data['last_check']="N/A";
	}
	else { $processed_data['current_state'] = state_map($rawdata['current_state'], $service_states); }
	//print_r($processed_data);
	//print "<br /><br />";
	return $processed_data;
}

/* given some raw data return an array of shared ("standard values") and 
 *  keys which need to be copied verbatim into the output
 */
function get_standard_values($rawdata, $identical_keys)
{
	$standard_values = array();
	
	foreach($identical_keys as $key) { 
		$standard_values[$key] = $rawdata[$key];
	}
	
	$standard_values['attempt'] = $rawdata['current_attempt'].' / '.$rawdata['max_attempts'];
	$standard_values['duration'] = calculate_duration($rawdata['last_state_change']);
	$standard_values['last_check'] = date('M d H:i\:s\s Y', $rawdata['last_check']);
	
	return $standard_values;
}

/* Given an integer state and an associative array mapping integer states into
 *   human readable values, return the associated value to that state.  If no
 *   appropriate value is provided return 'UNKNOWN'
 */
function state_map($cur_state, $states)
{
	return array_key_exists($cur_state, $states) ? $states[$cur_state] : 'UNKNOWN';
}

?>