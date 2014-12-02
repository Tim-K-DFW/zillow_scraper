<?php
	require("../../includes/config.php");
		
							// BY-STATE TOTALS, 3 columns for each date
	
	// dates and batches for the file headers
	$batches = query('real_estate_z2', "SELECT batch, date_format(time, '%Y-%m-%d') FROM results_by_state GROUP BY batch ORDER BY batch ASC");
	
	// by-state broker count
	$stats_raw = query('real_estate_z2', "SELECT batch, time, state, premier_agents, all_agents, premier_share FROM results_by_state ORDER BY state ASC, batch ASC");
	
	// national broker count
	$totals = query('real_estate_z2', "SELECT batch, SUM( premier_agents ) AS  \"national_premier\", SUM( all_agents ) AS  \"national_all\", (SUM( premier_agents ) / SUM( all_agents )) AS  \"%_of_premier\" FROM results_by_state GROUP BY batch ORDER BY batch ASC");
	
	// first line of header -- date for every batch in ascending order, every 3 "columns"
	$header1 = array('');
	foreach ($batches as $batch) {
		$header1[] = $batch["date_format(time, '%Y-%m-%d')"];
		$header1[] = '';
		$header1[] = '';
	}
	$header1[count($header1) - 1] = "\n";
	
	// var_dump($header1);
			
	// second line of header -- state name once and 3 columns for each batch
	$header2 = array('state');
	foreach ($batches as $batch) {
		$header2[] = 'premier agents';
		$header2[] = 'all agents';
		$header2[] = 'premier as % of all';
	}
	$header2[] = "\n";
	
	// national totals before states
	$nation = array('US total');
	foreach ($totals as $total) {
		$nation[] = $total["national_premier"];
		$nation[] = $total["national_all"];
		$nation[] = $total["%_of_premier"];
	}
	$nation[] = "\n";
	
	// fill the output array
	$output = array();
	$count = 0;
	$state = $stats_raw[$count]['state'];
	$next_state[] = $state;
	while ($count <= count($stats_raw)) {
		// if still within same state, append data
		if ($stats_raw[$count]['state'] == $state) {
			$next_state[] = $stats_raw[$count]['premier_agents'];
			$next_state[] = $stats_raw[$count]['all_agents'];
			$next_state[] = $stats_raw[$count]['premier_share'];
			$count++;
		} else {										// once reached the next state...
			$output[] = $next_state;					// previous state is done, pass it to final output array
			unset($next_state);							// clean up current state array
			$state = $stats_raw[$count]['state'];		// store the new state from stats to control var
			$next_state[] = $state;						// start writing new current state array
		}
	}
	
	// WRITE TO OUTPUT FILES
	$fp2 = fopen("agent_count.csv", "w");
	fputcsv ($fp2, $header1);
	fputcsv ($fp2, $header2);
	fputcsv ($fp2, $nation);
	foreach($output as $row){
        fputcsv($fp2, $row);
	}
	fclose($fp2);

	unset($output);
	unset($nation);
	unset($totals);
	unset($next_state);
	
	
	// add loading from retention tables and putting it into csv files
	$mr_date = query('real_estate_z2', "SELECT MAX(date) as date FROM retention");
	$mr_date = $mr_date[0]['date'];
	$retention = query('real_estate_z2', "SELECT state, premier_agents, all_agents, premier_share, ave_length, cancelled_l_week, (CASE cancelled_l_month WHEN 0 THEN 'n/a' ELSE cancelled_l_month END) as cancelled_l_month, (CASE cancelled_3_months WHEN 0 THEN 'n/a' ELSE cancelled_3_months END) as cancelled_3_months, (CASE cancelled_6_months WHEN 0 THEN 'n/a' ELSE cancelled_6_months END) as cancelled_6_months FROM retention WHERE date = ? ORDER BY state", $mr_date);
	
	
	unset($header1);
	unset($header2);
	unset($header3);
	unset($header4);
	
	// echo $mr_date;
	
	$header1 = array('RETENTION STATISTICS');
	$header2 = array('as of week ending ' . substr($mr_date, 0, 10));
	$header3 = array('');
	$header4 = array('state', 'premier agents', 'all agents', '% of premier', 'average subscription length, % of total time', 'subscribers cancelled last week', 'subscribers cancelled last 4 weeks', 'subscribers cancelled last 13 weeks', 'subscribers who cancelled last 26 weeks');
		
	$fp2 = fopen("retention.csv", "w");
	fputcsv ($fp2, $header1);
	fputcsv ($fp2, $header2);
	fputcsv ($fp2, $header3);
	fputcsv ($fp2, $header4);
	foreach($retention as $row){
        fputcsv($fp2, $row);
	}
	fclose($fp2);
	

	echo ("<a href=\"brokers\\agent_count.csv\">Agent count - click to download</a><br>");
	echo ("<a href=\"brokers\\retention.csv\">Retention - click to download</a><br>");
?>
