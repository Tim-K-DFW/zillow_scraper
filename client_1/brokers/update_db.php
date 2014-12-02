#!/usr/local/bin/php -q
<?php
require("/home/nhtven1/non-descript.net/includes/functions.php");


function shorten($long) {
	//	get rid of "http:..." part
	$short = substr($long, 32, strlen($long) - 32);
	// remove "real-estate-agents" part
	$short = preg_replace('@\/real-estate-agents\/@', '/', $short);
	return $short;
}

// function to record TOTAL agents on a state level -- will be called once per state
function state_total($page) {
	global $regex_total, $regex_state_name;
	preg_match($regex_state_name, $page, $matches);						// determine current state
	$name = $matches[1];
	$source = file_get_contents($page);
	preg_match_all ($regex_total, $source, $matches);
	$total = filter_var($matches[1][0], FILTER_SANITIZE_NUMBER_INT);	// determine state total
	$output = array('name' => $name, 'total' => $total);
	return $output;														// return name and total as array
}

/* function scrapes brokers from current page and possible next pages, 
appending broker info to $brokers and dump.txt  */
function scan_page($current_page, $level) {
	global $page_count, $brokers, $batch, $date, $broker_count, $regex_hood_name, $regex_broker, $regex_next_page, $fp, $regex_total, $regex_state_name;
	preg_match($regex_hood_name, $current_page, $matches);			// determine current hood name
	$current_hood = $matches[1];
	preg_match($regex_state_name, $current_page, $matches);			// determine current state
	$current_state = $matches[1];
	$next_page = FALSE;
	do {
		$source = file_get_contents($current_page);
		echo "Processing " . shorten($current_page) . ".....";
		$page_count++;
		if (preg_match_all ($regex_total, $source, $matches)) {						// find total agent count
			$scope_total = filter_var($matches[1][0], FILTER_SANITIZE_NUMBER_INT);
		}	else {
			echo 'Failed to parse scope total....';
			$scope_total = 0;
		}
		if (preg_match_all($regex_broker, $source, $matches, FALSE ,0)) {
			$added = 0;
			foreach ($matches[1] as $broker) {
				$local_area = substr($source, strpos($source, 'Profile picture for ' . $broker), 1300);
				$prem_pos = strpos($local_area, 'premiere-badge');					// find only premier agents
				if ($prem_pos != FALSE) {
					$brokers[] = html_entity_decode($broker);
					$added++;
					$broker_count++;
					$str = $batch . ";" . date('Y-m-d H:i:s') . ";" . $current_hood . ";" . $current_state . ";";
					$str .= $level . ";" . $brokers[count($brokers)-1] . ";";
					$str .= scope_page($current_page) . ";" . $scope_total . ";";
					$str .= $current_state . $brokers[count($brokers)-1] . "\n";  		// state+name for duplicate elimination
					fwrite($fp, $str);													// append broker's info to DUMP
				} 
			}
			echo $added;
		} else
			echo "0";
		echo " brokers found.\n";
	
		//check if there is next page
		if (preg_match_all($regex_next_page, $source, $matches2, FALSE ,0)) {
			$current_page = $matches2[1][0];						// there is only one "next page" link
			$next_page = TRUE;
		} else {												
			$next_page = FALSE;
		}
		sleep(rand(4,6));
	}	while ($next_page);
}

function scope_page($current_page) {
	if (preg_match_all ('@\/?p=(\d+)@', $current_page, $matches))
			$page = $matches[1][0];
		else
			$page = 1;
	return $page;
}

// phpinfo();

set_time_limit (108000);

// SWITCH COMMENTS FOR FULL SCAN
$current_page = 'http://www.zillow.com/directory/real-estate-agents/';
// $current_page = 'http://www.zillow.com/directory/PR/real-estate-agents/';

$regex_state_link = '@<a href=\"([^\"]+)\" data-za-category=\"none\" data-za-action=\"Breadcrumb click\" class=\"\" data-za-label=\"State\"@';
$regex_city_link = '@<a href=\"([^\"]+)\" data-za-category=\"none\" data-za-action=\"Breadcrumb click\" class=\"\" data-za-label=\"City\"@';
$regex_hood_link = '@<a href=\"([^\"]+)\" data-za-category=\"none\" data-za-action=\"Breadcrumb click\" class=\"\" data-za-label=\"Neighborhood\"@';
$regex_hood_name = '@directory\/([^\/]+)\/@';
$regex_state_name = '@([A-Z]{2})\/real-estate-agents@';
$regex_next_page = '@href=\"([^\"]+)\" class=\"on\">Next page@';
$regex_broker = '@Profile picture for ([^\"]+)@';
$regex_total = '@result-count\">(\d*,?\d*,?\d+)*@';

$brokers = array();
$states = array();
$broker_count = 0;
$hood_count = 0;
$city_count = 0;
$state_count = 0;
$page_count = 0;

date_default_timezone_set('America/New_York');
// $date = date('Y-m-d H:i:s');
$batch = time();

$path = "/home/nhtven1/non-descript.net/client_1/brokers/";			// because cron job is run from root directory
$fp = fopen($path . "dump.txt", "w");
$fp_state = fopen($path . "states.txt", "w");

// fill array with state links  
 $source = file_get_contents($current_page);
if (preg_match_all($regex_state_link, $source, $matches, FALSE ,0)) {
		echo "Let's get going! Found " . count($matches[1]) . " states:\n";
		for ($i = 0; $i < count($matches[1]); $i++) {
			$states[] = html_entity_decode($matches[1][$i]);
			echo (($i+1) . ". " . shorten($matches[1][$i]) . "\n");
		}
	}
else
	echo ("Failed to parse states. Check your shit.\n");
	
// DISABLE temporary - for state-level testing only
// unset($states);
// $states[] = $current_page;

foreach($states as $state) {
// fill array of city links for given state
$cities = array();
$current_page = $state;
echo "Moving to the next state! ";
$source = file_get_contents($current_page);
if (preg_match_all($regex_city_link, $source, $matches, FALSE ,0)) {
		echo "Found " . count($matches[1]) . " cities in this state:\n";
		for ($i = 0; $i < count($matches[1]); $i++) {
			$cities[] = html_entity_decode($matches[1][$i]);
			echo (($i+1) . ". " . shorten($matches[1][$i]) . "\n");
		}
	}
else
	echo ("Failed to parse cities. Check your shit.\n");

// add to state total
$current_total = state_total($current_page);
$str = $batch . ";" . $current_total['name'] . ";" . $current_total['total'] . "\n";
fwrite($fp_state, $str);
	
// scrape state-level before going into cities - e.g. MP has no brokers below state level
scan_page($current_page, 'state');	
	
// loop through cities array
foreach ($cities as $city) {
	// fill array of hood links for given city
	$hoods = array();
	$current_page = $city;
	echo "Moving to the next city! ";
	$source = file_get_contents($current_page);
	if (preg_match_all($regex_hood_link, $source, $matches, FALSE ,0)) {
		echo "Found " . count($matches[1]) . " hoods in this city:\n";
			for ($i = 0; $i < count($matches[1]); $i++) {
				$hoods[] = html_entity_decode($matches[1][$i]);
				echo (($i+1) . ". " . shorten($matches[1][$i]) . "\n");
			}
	} else
		echo ("No hoods found here. Will only scrape brokers directly from city page.\n");
	
	// scrape brokers from top-level city page first
	scan_page($current_page, 'city');								// calls custom function	
		
	// scrape brokers from hoods now, looping through all hood links
	if (count($hoods) > 0) {
		foreach ($hoods as $hood) {
			$current_page = $hood;
			scan_page($current_page, 'hood');						// calls custom function
			$hood_count++;
		};
	};
	$city_count++;
	unset($hoods);												// empty hoods array
}

$state_count++;
unset($cities);
}

fclose($fp);
fclose($fp_state);

echo "Done!\n";
echo "States scanned: " . $state_count . "\n";
echo "Cities scanned: " . $city_count . "\n";
echo "Hoods scanned: " . $hood_count . "\n";
echo "Pages scanned: " . $page_count . "\n";
echo "Premier agents found: " . $broker_count . "\n";
echo "Time expended: " . (time() - $batch) . " sec.\n";

// copy data from dump files into database
$sql = "SELECT count(state) FROM results_by_state"; // needed for the script to run after query

$sql .= ";LOAD DATA LOCAL INFILE '/home/nhtven1/non-descript.net/client_1/brokers/dump.txt' REPLACE INTO TABLE main FIELDS TERMINATED BY ';' LINES TERMINATED BY '\\n' (batch, time, hood, state, level, name, scope_page, total_in_scope, state_plus_name)";

$sql .= ";LOAD DATA LOCAL INFILE '/home/nhtven1/non-descript.net/client_1/brokers/states.txt' REPLACE INTO TABLE states FIELDS TERMINATED BY ';' LINES TERMINATED BY '\\n' (batch, state, total)";

// aggregate premier agents by state
$sql .= ";INSERT results_by_state (batch, state, premier_agents) SELECT batch, state, COUNT(name) FROM main WHERE batch = ? GROUP BY batch, state";
$sql .= ";update results_by_state set time = now() + interval 3 hour where time = 0";

// add total agents by state from state table
$sql .= ";UPDATE results_by_state INNER JOIN states ON (states.batch = results_by_state.batch AND states.state = results_by_state.state) SET results_by_state.all_agents = states.total WHERE results_by_state.all_agents = 0";

// calculate share of premier agents
$sql .= ";update results_by_state set premier_share = premier_agents / all_agents WHERE premier_share = 0";

// set time field for all brokers to current time (actual time will stay in dump file for debugging)
$curr_time = date('Y-m-d H:i:s');
$sql .= ";update main set time = ? where batch = ?";

// update backup copy
$sql .= ";DELETE FROM main_backup; INSERT main_backup SELECT * FROM main";

$a = query('real_estate_z2', $sql, $batch, $curr_time, $batch);

$filename = $path . "dump" . date('Y-m-d') . ".txt";
rename($path . "dump.txt", $filename);
$filename = $path . "states" . date('Y-m-d') . ".txt";			
rename($path . "states.txt", $filename);

require "/home/nhtven1/non-descript.net/client_1/brokers/update_retention.php";

?>
