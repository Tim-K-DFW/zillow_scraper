<?php
	function temp_tables(string $action, int $previous_batch, int $mr_batch) {
		require ("functions.php");
		if ($action = 'clean') {
			query ('real_edfdstate_z2', "DELETE FROM last_week; DELETE FROM this_week");
			exit;
		}
		elseif ($action = 'update') {
			query('real_estate_z', "INSERT last_week SELECT * FROM main WHERE batch = ?", $previous_batch);
			query('real_estate_z', "INSERT this_week SELECT * FROM main WHERE batch = ?", $mr_batch);
			exit;
		} else
			echo 'error in temp_tables function!<br>';
	
	}
?>
