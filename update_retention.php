<?php
	
	set_time_limit (108000);
	
	// disable when running from other script; enable only when running separately
	require "../../includes/functions.php";
	
	// update retention tables, executed after each DB update off-line b/c of long time
	// output stored into tables and retrieved by download.php as needed without any actual calculations
		
	// for calculations when new metric is added (i.e. how many weeks back to go)
	$offset = $argv[1];
	
	// get batch information
	$batches = query('real_estate_z2', "SELECT batch, date_format(time, '%Y-%m-%d') as date FROM results_by_state GROUP BY batch ORDER BY batch ASC");
		
	$mr_batch = $batches[count($batches)- 1 - $offset]['batch'];					// most recent batch #
	
	$last_week_batch = $batches[count($batches) - 2 - $offset]['batch'];
	$last_month_batch = $batches[count($batches) - 5 - $offset]['batch'];
	
	$st_batch = $batches[0]['batch'];									// very first batch #
	$weeks_elapsed = round(($mr_batch - $st_batch) / (60*60*24*7));		// since scanning started 5/03/2014
	
	echo "offset: " . $offset . "\n";
	echo "last week batch: " . $last_week_batch . "\n";
	echo "last month batch: " . $last_month_batch . "\n";
	echo "most recent batch: " . $mr_batch . "\n";
	echo "weeks elapsed: " . $weeks_elapsed . "\n";
	
		
	// MOST RECENT TOTALS AND AVERAGE SUBSCRIPTION LENGTH SECTION
	
	// number of weeks each agent showed up in results, nationwide and by state
	$ret_national = query('real_estate_z2', "SELECT AVG( a.length ) AS  'ave_length' FROM (SELECT state_plus_name, COUNT( batch ) AS length FROM main WHERE batch <= ? GROUP BY state_plus_name)a", $mr_batch);
	$ret_states = query('real_estate_z2', "SELECT state, AVG( a.length ) AS  'ave_length' FROM (SELECT state, state_plus_name, COUNT( batch ) AS length FROM main WHERE batch <= ? GROUP BY state_plus_name)a GROUP BY state ORDER BY state", $mr_batch);
	
	// broker count as of most recent batch, nationwide and by state
	$totals = query('real_estate_z2', "SELECT SUM( premier_agents ) AS  \"national_premier\", SUM( all_agents ) AS  \"national_all\", (SUM( premier_agents ) / SUM( all_agents )) AS  \"%_of_premier\" FROM results_by_state WHERE batch = ?", $mr_batch);
	$totals = $totals[0];
	$stats_raw = query('real_estate_z2', "SELECT state, premier_agents, all_agents, premier_share FROM results_by_state WHERE batch = ? ORDER BY state ASC", $mr_batch);
	
	// CANCELLATION SECTION
	
	//create temporary weekly tables
	$a = query('real_estate_z2', "SELECT COUNT(state) from main WHERE state=\"AL\" GROUP BY state;
	DELETE FROM last_month; DELETE FROM last_week; DELETE FROM this_week;
	INSERT last_month SELECT * FROM main WHERE batch = ?;
	INSERT last_week SELECT * FROM main WHERE batch = ?;
	INSERT this_week SELECT * FROM main WHERE batch = ?"
	, $last_month_batch, $last_week_batch, $mr_batch);

	echo "Temporary tables reset...\n";
	
	// state list for looping
	$states_list = query('real_estate_z2', "SELECT state FROM main WHERE batch = ? GROUP BY state ORDER BY state ASC", $last_week_batch);
	
	
	// get number of agents from last week who stayed until this week
	$stayed_weekly_total = 0;						// running total for nationwide, because query for all stalls
	foreach ($states_list as $state) {
		$next_state[] = $state['state'];						// add state name to the output
		$by_state = query ('real_estate_z2', "SELECT count(last_week.state_plus_name) as 'stayed' FROM last_week INNER JOIN this_week ON last_week.state_plus_name = this_week.state_plus_name WHERE last_week.state = ?", $state['state']);
		echo "Weekly retention - Done with state of " . $state['state'] . "......";												// to prevent timeout
		$next_state[] = $by_state[0]['stayed'];					// add # of retained agents to the state output
		$stayed_weekly_total += $by_state[0]['stayed'];				// add retained agents to national running total
		
		echo "Added " . $by_state[0]['stayed'] . " to national total; count now is " . $stayed_weekly_total . "\n";
		
		$stayed_weekly[] = $next_state;								// append this state to output array
		unset($next_state);										// clean up current state for next loop
	}
		
	$national[] = array('US total');
	$national[] = $stayed_weekly_total;
	$stayed_weekly[] = $national;							// append national total to state results
			
	// get number of ALL agents who started last week, nationwide and by state
	$started_lw = query('real_estate_z2', "SELECT state, COUNT(state_plus_name) as 'started' FROM last_week GROUP BY state ORDER BY state ASC");
	$started_lw_national= query('real_estate_z2', "SELECT COUNT(state_plus_name) as 'started' FROM last_week");
	
	
	
	// ------------------------------------------------
	
	// get number of agents from last month who stayed until this week
	unset($by_state);
	unset($national);
	$stayed_monthly_total = 0;						// running total for nationwide, because query for all stalls
	foreach ($states_list as $state) {
		$next_state[] = $state['state'];						// add state name to the output
		$by_state = query ('real_estate_z2', "SELECT count(last_month.state_plus_name) as 'stayed' FROM last_month INNER JOIN this_week ON last_month.state_plus_name = this_week.state_plus_name WHERE last_month.state = ?", $state['state']);
		echo "Monthly retention - Done with state of " . $state['state'] . "......";												// to prevent timeout
		$next_state[] = $by_state[0]['stayed'];					// add # of retained agents to the state output
		$stayed_monthly_total += $by_state[0]['stayed'];				// add retained agents to national running total
		
		echo "Added " . $by_state[0]['stayed'] . " to national total; count now is " . $stayed_monthly_total . "\n";
		
		$stayed_monthly[] = $next_state;								// append this state to output array
		unset($next_state);										// clean up current state for next loop
	}
	
	
	$national[] = array('US total');
	$national[] = $stayed_monthly_total;
	$stayed_monthly[] = $national;							// append national total to state results
			
	// get number of ALL agents who started last month, nationwide and by state
	$started_lm = query('real_estate_z2', "SELECT state, COUNT(state_plus_name) as 'started' FROM last_month GROUP BY state ORDER BY state ASC");
	$started_lm_national= query('real_estate_z2', "SELECT COUNT(state_plus_name) as 'started' FROM last_month");
	
	// ------------------------------------------------
	/*
	var_dump($stayed_weekly);
	echo "<br><br>";
	var_dump($stayed_monthly);
	echo "<br><br>";
	*/
	
	// clean up temporary weekly tables
	$a = query('real_estate_z2', "SELECT COUNT(state) FROM results_by_state; DELETE FROM this_week; DELETE FROM last_week; DELETE FROM last_month");
	
	
	
	echo "looks like national monthly retention will be:\n";
	echo "started last month: " . $started_lm_national[0]['started'] .  "\n";
	echo "stayed through mr week: " . $stayed_monthly[56][1] .  "\n";
	echo "so the ratio will be " . round((1 - $stayed_monthly[56][1] / $started_lm_national[0]['started']), 4) . "\n";
	
	
	
	// BUILD OUTPUT CSV FILE

	unset($nation);
	$nation = array('US total');
	foreach ($totals as $total) {						// build 1st 4 columns of "national" row
		if ($total > 1)
			$nation[] = $total;
		else
			$nation[] = round($total, 4);
	}
	
	// add retention statistics to "national" row
	$nation[] = round(($ret_national[0]['ave_length'] / ($weeks_elapsed + 1)), 4); 
	$nation[] = round((1 - $stayed_weekly[56][1] / $started_lw_national[0]['started']), 4);
	$nation[] = round((1 - $stayed_monthly[56][1] / $started_lm_national[0]['started']), 4);
	
	unset($output);
	// fill output array, looping through the queries results for each state
	for ($i = 0; $i < 56; $i++) {
		$next_state[] = $ret_states[$i]['state'];					// state name
		$next_state[] = $stats_raw[$i]['premier_agents'];			// # of premier agents
		$next_state[] = $stats_raw[$i]['all_agents'];				// # of all agents
		$next_state[] = round($stats_raw[$i]['premier_share'], 4);			// share of premier agents		
		$next_state[] = round($ret_states[$i]['ave_length'] / ($weeks_elapsed + 1), 4);	// average subscription length
		$next_state[] = round(1 - $stayed_weekly[$i][1] / $started_lw[$i]['started'], 4); // % of agents who cancelled subscription last week
		$next_state[] = round(1 - $stayed_monthly[$i][1] / $started_lm[$i]['started'], 4); // % of agents who cancelled subscription last week
		
		$output[] = $next_state;
		unset($next_state);
	}
		
	$fp2 = fopen("ret_table.txt", "w");
	fputcsv ($fp2, $nation);
	foreach($output as $row){
    	fputcsv($fp2, $row);
	}
	fclose($fp2);		 
	
	echo "Retention table written into file!\n";
	
	// update table
	
	// this one removes quotation marks from "US total" -- but I kept them for viewing convenience
	/* $a = query ('real_estate_z2', "SELECT count(state) FROM results_by_state; LOAD DATA LOCAL INFILE '/home/nhtven1/non-descript.net/client_1/brokers/ret_table.txt' REPLACE INTO TABLE retention FIELDS TERMINATED BY ',' LINES TERMINATED BY '\\n' (state, premier_agents, all_agents, premier_share, ave_length, cancelled_l_week, cancelled_l_month, cancelled_3_months, cancelled_6_months); update retention set date = now() + interval 3 hour where date = 0; UPDATE retention SET state = TRIM(BOTH '\"' FROM state)");   */
	
	/* $a = query ('real_estate_z2', "SELECT count(state) FROM results_by_state; 
	LOAD DATA LOCAL INFILE '/home/nhtven1/non-descript.net/client_1/brokers/ret_table.txt' IGNORE INTO TABLE retention FIELDS TERMINATED BY ',' LINES TERMINATED BY '\\n' (state, premier_agents, all_agents, premier_share, ave_length, cancelled_l_week, cancelled_l_month, cancelled_3_months, cancelled_6_months); 
	UPDATE retention SET date = now() + interval 3 hour WHERE date = 0;
	DELETE FROM retention_backup; 
	INSERT retention_backup SELECT * FROM retention"); */
	
	$a = query ('real_estate_z2', "SELECT count(state) FROM results_by_state; 
	LOAD DATA LOCAL INFILE '/home/nhtven1/non-descript.net/client_1/brokers/ret_table.txt' IGNORE INTO TABLE retention FIELDS TERMINATED BY ',' LINES TERMINATED BY '\\n' (state, premier_agents, all_agents, premier_share, ave_length, cancelled_l_week, cancelled_l_month, cancelled_3_months, cancelled_6_months); 
	UPDATE retention SET date = now() + interval 3 hour WHERE date = 0");
	
	echo "Retention table in DB updated!\n";
	
	// unlink("ret_table.txt");	
?>
