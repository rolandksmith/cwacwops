function removeServiceDuplicates() {
	global $wpdb;
	$sql = "select * from wpw1_cwa_advisor_service2 
			order by advisor, semester";
	$result = $wpdb->get_results($sql);
	if ($result === FALSE) {
		echo "selecting from wpw1_cwa_advisor_servic2 failed<br />";
	} else {
		$numRows = $wpdb->num_rows;
		echo "ran $sql<br />and retrieved $numRows rows<br />";
		if ($numRows > 0) {
			$prevAdvisor		= '';
			$prevSemester		= '';
			$duplicates			= 0;
			$advisorCount		= 0;
			foreach($result as $resultRow) {
				$record_id		= $resultRow->record_id;
				$advisor		= $resultRow->advisor;
				$semester		= $resultRow->semester;
				$classes		= $resultRow->classes;
				$date_written	= $resultRow->date_written;
				
				echo "<br />Processing advisor $advisor; semester $semester<br />";
				if ($advisor == $prevAdvisor && $semester == $prevSemester) {
					echo "have duplicate<br />";
					$duplicates++;
					$deleteResult = $wpdb->delete('wpw1_cwa_advisor_service2',
												array('record_id'=>$record_id),
												array('%d'));
					if ($deleteResult === FALSE) {
						$last_error = $wpdb->last_error;
						echo "Delete failed: $last_error<br />";
					} else {
						echo "record deleted<br />";
					}
				} else {
					$advisorCount++;
				}
				$prevAdvisor	= $advisor;
				$prevSemester	= $semester;
			}
		}
	}
	echo "$duplicates duplicates<br />
		  $advisorCount advisor / semester records<br />";
	return "all done";
}
add_shortcode('removeServiceDuplicates','removeServiceDuplicates');