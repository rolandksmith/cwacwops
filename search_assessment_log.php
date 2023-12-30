function search_assessment_log_func() {

// modified 25Oct22 by Roland to accomodate timezone table format

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
	$scoreConversion			= array('50'=>'0-49%',
										'75'=>'50-89%',
										'90'=>'90+%');

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-search-audio-assessment-log/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$advisor_call_sign			= '';

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign		 = $str_value;
				$inp_callsign		 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "advisor_call_sign") {
				$advisor_call_sign		 = $str_value;
				$advisor_call_sign		 = strtoupper(filter_var($advisor_call_sign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'verbose') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
				}
			}
		}
	}
	
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
	<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
<tr><td>Verbose Debugging?</td>
	<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
		<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}
	
	
	$content = "<style type='text/css'>
fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
background:#efefef;padding:2px;border:solid 1px #d3dd3;}

legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}

label {font:'Times New Roman', sans-serif;font-weight:bold;line-height:normal;
text-align:right;margin-right:10px;position:relative;display:block;float:left;width:150px;}

textarea.formInputText {font:'Times New Roman', sans-serif;color:#666;
background:#fee;padding:2px;border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}

textarea.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

textarea.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}

input.formInputText {color:#666;background:#fee;padding:2px;
border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}

input.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

input.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}

input.formInputFile {color:#666;background:#fee;padding:2px;border:
solid 1px #f66;margin-right:5px;margin-bottom:5px;height:20px;}

input.formInputFile:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

select.formSelect {color:#666;background:#fee;padding:2px;
border:solid 1px #f66;margin-right:5px;margin-bottom:5px;cursor:pointer;}

select.formSelect:hover {color:#333;background:#ccffff;border:solid 1px #006600;}

input.formInputButton {vertical-align:middle;font-weight:bolder;
text-align:center;color:#300;background:#f99;padding:1px;border:solid 1px #f66;
cursor:pointer;position:relative;float:left;}

input.formInputButton:hover {color:#f8f400;}

input.formInputButton:active {color:#00ffff;}

tr {color:#333;background:#eee;}

table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;}

th {color:#ffff;background-color:#000;padding:5px;font-size:small;}

td {padding:5px;font-size:small;}

th:first-child,
td:first-child {
 padding-left: 10px;
}

th:last-child,
td:last-child {
	padding-right: 5px;
}
</style>";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment2";
	} else {
		$extMode					= 'pd';
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Click Submit to Start the Process</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Enter Callsign to be searched</td>
								<td><input type='text' class='formInputText' size='25' maxlenth='25' name='inp_callsign'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 2 with inp_callsign: $inp_callsign<br />";
		}
		if ($advisor_call_sign != '') {
			$userName				= $advisor_call_sign;
		}
		$content					.= "<h3>Self Assessment Information for $inp_callsign</h3>";
		$sql						= "select * from $audioAssessmentTableName where call_sign='$inp_callsign' order by assessment_date DESC";
		$wpw1_cwa_audio_assessment	= $wpdb->get_results($sql);
		if ($wpw1_cwa_audio_assessment === FALSE) {
			if ($doDebug) {
				echo "Reading wpw1_cwa_audio_assessment table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numAARows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numAARows rows from wpw1_cwa_audio_assessment table<br />";
			}
			if ($numAARows > 0) {
				if (in_array($userName,$validTestmode)) {
					$content								.= "<table>
																<tr><th>Date</th>
																	<th>Reason</th>
																	<th>Score</th>
																	<th>Explanation</th>
																	<th>Program</th>
																	<th>Clip Nmbr</th>
																	<th>Clip Name</th></tr>";
				} else {
					$content								.= "<table>
																<tr><th>Date</th>
																	<th>Reason</th>
																	<th>Score</th>
																	<th>Explanation</th>
																	<th>Program</th></tr>";
				}
				$myCount	= 0;
				foreach ($wpw1_cwa_audio_assessment as $assessmentRow) {
					$assessment_ID						= $assessmentRow->record_id;
					$assessment_call_sign				= strtoupper($assessmentRow->call_sign);
					$assessment_assessment_date			= $assessmentRow->assessment_date;
					$assessment_level					= $assessmentRow->assessment_level;
					$assessment_clip_name				= $assessmentRow->assessment_clip_name;
					$assessment_clip					= $assessmentRow->assessment_clip;
					$assessment_score					= $assessmentRow->assessment_score;
					$assessment_notes					= $assessmentRow->assessment_notes;
					$assessment_program					= $assessmentRow->assessment_program;


					if (array_key_exists($assessment_score,$scoreConversion)) {
						$assessmentScore					= $scoreConversion[$assessment_score];
					} else {
						$assessmentScore					= $assessment_score;
					}
					if (in_array($userName,$validTestmode)) {
						$content							.= "<tr><td style='vertical-align:top;'>$assessment_assessment_date</td>
																	<td style='vertical-align:top;'>$assessment_level</td>
																	<td style='text-align:center;vertical-align:top;'>$assessmentScore</td>
																	<td style='vertical-align:top;'>$assessment_notes</td>
																	<td style='vertical-align:top;'>$assessment_program</td>
																	<td style='text-align:center;vertical-align:top;'>$assessment_clip</td>
																	<td style='vertical-align:top;'>$assessment_clip_name</td></tr>";
					} else {
						$content							.= "<tr><td style='vertical-align:top;'>$assessment_assessment_date</td>
																	<td style='vertical-align:top;'>$assessment_level</td>
																	<td style='text-align:center;vertical-align:top;'>$assessmentScore</td>
																	<td style='vertical-align:top;'>$assessment_notes</td>
																	<td style='vertical-align:top;'>$assessment_program</td></tr>";			
					}
					$myCount++;
				}
				$content		.= "</table>";
			} else {
				if ($doDebug) {
					echo "No records found in wpw1_cwa_audio_assessment table<br />";
				}
			}
		}
	
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("Search Audio Assessment Log|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('search_assessment_log', 'search_assessment_log_func');
