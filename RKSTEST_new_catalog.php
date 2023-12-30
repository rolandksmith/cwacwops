function rkstest_new_catalog_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
/*
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
*/
	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
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

	$strPass					= "1";
	$theURL						= "$siteURL/rkstest-new-catalog/";
	$jobname					= "RKSTEST New Catalog V$versionNumber";
	
	$run_date							= "";
	$student_semester 					= "";
	$student_level 						= "";
	$student_no_catalog 				= "";
	$student_catalog_options			= "";
	$student_flexible					= "";
	$student_first_class_choice_utc 	= "";
	$student_second_class_choice_utc	= "";
	$student_third_class_choice_utc 	= "";
	$student_timezone_offset			= "";
	$student_first_class_choice 			= "";
	$student_second_class_choice			= "";
	$student_third_class_choice 			= "";
	$inp_flex								= "not returned";

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
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
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
			if ($str_key 		== "run_date") {
				$run_date	 = $str_value;
				$run_date	 = filter_var($run_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_semester") {
				$student_semester	 = $str_value;
				$student_semester	 = filter_var($student_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_level") {
				$student_level	 = $str_value;
				$student_level	 = filter_var($student_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_no_catalog") {
				$student_no_catalog	 = $str_value;
				$student_no_catalog	 = filter_var($student_no_catalog,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_catalog_options") {
				$student_catalog_options	 = $str_value;
				$student_catalog_options	 = filter_var($student_catalog_options,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_available") {
				$inp_available	 = $str_value;
				$inp_available	 = filter_var($inp_available,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "schedAvail") {
				$schedAvail	 = $str_value;
				$schedAvail	 = filter_var($schedAvail,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_flexible") {
				$student_flexible	 = $str_value;
				$student_flexible	 = filter_var($student_flexible,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_first_class_choice_utc") {
				$student_first_class_choice_utc	 = $str_value;
				$student_first_class_choice_utc	 = filter_var($student_first_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_second_class_choice_utc") {
				$student_second_class_choice_utc	 = $str_value;
				$student_second_class_choice_utc	 = filter_var($student_second_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_third_class_choice_utc") {
				$student_third_class_choice_utc	 = $str_value;
				$student_third_class_choice_utc	 = filter_var($student_third_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_first_class_choice") {
				$student_first_class_choice	 = $str_value;
				$student_first_class_choice	 = filter_var($student_first_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_second_class_choice") {
				$student_second_class_choice	 = $str_value;
				$student_second_class_choice	 = filter_var($student_second_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_third_class_choice") {
				$student_third_class_choice	 = $str_value;
				$student_third_class_choice	 = filter_var($student_third_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_timezone_offset") {
				$student_timezone_offset	 = $str_value;
				$student_timezone_offset	 = filter_var($student_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "result_option") {
				$result_option	 = $str_value;
				$result_option	 = filter_var($result_option,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_sked1") {
				$inp_sked1	 = $str_value;
				$inp_sked1	 = filter_var($inp_sked1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_sked2") {
				$inp_sked2	 = $str_value;
				$inp_sked2	 = filter_var($inp_sked2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_sked3") {
				$inp_sked3	 = $str_value;
				$inp_sked3	 = filter_var($inp_sked3,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_flex") {
				$inp_flex	 = $str_value;
				$inp_flex	 = filter_var($inp_flex,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_sked_times") {
				$inp_sked_times	 = $str_value;
//				$inp_sked_times	 = filter_var($inp_sked_times,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "inp_sked_times:<br /><pre>";
					print_r($inp_sked_times);
					echo "</pre><br />";
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
		$TableName					= "wpw1_cwa_";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_";
	}

	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>Enter the parameters</p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td>Run Date</td>
	<td><input type='text' class='formInputText' name='run_date' size='20' maxlength='20' required></td></tr>
<tr><td>Student Semester</td>
	<td><input type='text' class='formInputText' name='student_semester' size='30' maxlength='30' value='$student_semester' required></td></tr>
<tr><td>Student Level</td>
	<td><input type='text' class='formInputText' name='student_level' size='15' maxlength='15' value='$student_level' required></td></tr>
<tr><td>Student Timezone Offset</td>
	<td><input type='text' class='formInputText' name='student_timezone_offset' size='10' maxlength='10' value='$student_timezone_offset' required></td></tr>
<tr><td>Student No Catalog</td>
	<td><input type='text' class='formInputText' name='student_no_catalog' size='5' maxlength='5' value='$student_no_catalog'></td></tr>
<tr><td>Student Catalog Options</td>
	<td><input type='text' class='formInputText' name='student_catalog_options' size='50' maxlength='50' value='$student_catalog_options'></td></tr>
<tr><td>Student Flexible</td>
	<td><input type='text' class='formInputText' name='student_flexible' size='5' maxlength='5' value='$student_flexible'></td></tr>
<tr><td>Student First Class Choice UTC</td>
	<td><input type='text' class='formInputText' name='student_first_class_choice_utc' size='30' maxlength='30' value='$student_first_class_choice_utc'></td></tr>
<tr><td>Student Second Class Choice UTC</td>
	<td><input type='text' class='formInputText' name='student_second_class_choice_utc' size='30' maxlength='30' value='$student_second_class_choice_utc'></td></tr>
<tr><td>Student Third Class Choice UTC</td>
	<td><input type='text' class='formInputText' name='student_third_class_choice_utc' size='30' maxlength='30' value='$student_third_class_choice_utc'></td></tr>
<tr><td>Student First Class Choice (local)</td>
	<td>$student_first_class_choice</td></tr>
<tr><td>Student Second Class Choice (local)</td>
	<td>$student_second_class_choice</td></tr>
<tr><td>Student Third Class Choice (local)</td>
	<td>$student_third_class_choice</td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}	
		$inp_data			= array('run_date'=>$run_date,
									'student_semester'=>$student_semester, 
									'student_level'=>$student_level, 
									'student_no_catalog'=>$student_no_catalog, 
									'student_catalog_options'=>$student_catalog_options,
									'student_flexible'=>$student_flexible,  
									'student_first_class_choice_utc'=>$student_first_class_choice_utc, 
									'student_second_class_choice_utc'=>$student_second_class_choice_utc, 
									'student_third_class_choice_utc'=>$student_third_class_choice_utc, 
									'student_timezone_offset'=>$student_timezone_offset,
									'doDebug'=>$doDebug,
									'testMode'=>$testMode);
		if ($doDebug) {
			echo "sending inp_data:<br /><pre>";
			print_r($inp_data);
			echo "</pre><br />";
		}
		$result				= generate_catalog_for_student($inp_data);
//		if ($doDebug) {
//			echo "result:<br /><pre>";
//			print_r($result);
//			echo "</pre><br />";
//		}
		if ($doDebug) {
			echo "returned from generate_catalog_for_student<br /><br />";
		}
		if ($result[0] === FALSE) {
			echo "generate_catalog_for_student returned FALSE. Reason: $result[1]<br />";
		} else {
			$result_option	= $result[0];
			$result_catalog	= $result[1];
			$date1			= $result[2];
			$date2			= $result[3];
			$date3			= $result[4];
			$schedAvail		= $result[5];
			$option_message	= "";
			if ($result_option == 'option') {
				$option_message	= "<p>You are signing up for a $student_level Level class in 
									the $student_semester semester. The catalog of available 
									classes will not be available until about 45 days before 
									the start of the semester. Please indicate when you will be 
									available to take a $student_level Level class by 
									selecting from the list below:</p>";
			} elseif ($result_option == 'catalog') {
				$result_option		= $result[0];
				$option_message		= "<p>The Class Catalog for $student_level Level classes is now available. 
										Select up to three class schedule options from the table below and 
										number them 1, 2, and 3.
										CW Acadamy will try to assign you to one of the class options in the order you 
										specify. Whether or not you are assigned to a class will depend on the 
										number of students selecting that class schedule and the number of 
										available seats in the classes held at that time.</p>";
			} elseif ($result_option == 'avail') {
				$result_option		= $result[0];
				if ($doDebug) {
					echo "result_option of $result_option<br />";
				}
				$option_message 	= "<p>Students have already been assigned to advisor classes. There may possibly 
										be classes with available seats. If so, they are listed below. If a class 
										schedule listed below will work for you, select the class and submit the 
										selection</p>";
			}
			$content		.= "<h3>$jobname</h3>
								$option_message
								<form method='post' action='$theURL' 
								name='classselection' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='4'>
								<input type='hidden' name='result_option' value='$result_option'>
								<input type='hidden' name='student_semester' value='$student_semester'>
								<input type='hidden' name='student_level' value='$student_level'> 
								<input type='hidden' name='student_no_catalog' value='$student_no_catalog'> 
								<input type='hidden' name='student_catalog_options' value='$student_catalog_options'>
								<input type='hidden' name='student_flexible' value='$student_flexible'>  
								<input type='hidden' name='student_first_class_choice_utc' value='$student_first_class_choice_utc'> 
								<input type='hidden' name='student_second_class_choice_utc' value='$student_second_class_choice_utc'> 
								<input type='hidden' name='student_third_class_choice_utc' value='$student_third_class_choice_utc'> 
								<input type='hidden' name='student_timezone_offset' value='$student_timezone_offset'>
								<input type='hidden' name='schedAvail' value='$schedAvail'>
								$result[1]<br clear='all' />";
			if ($result_option == 'option') {
				$content	.= "<input class='formInputButton' type='submit' onclick=\"return validate_checkboxes(this.form);\" value='Submit' />";
			} else {			
				$content	.= "<input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Submit' />";
//				$content	.= "<input class='formInputButton' type='submit'  value='Submit' />";
			}
				$content	.= "</form></p>";
		}





	} elseif ("4" == $strPass) {
$doDebug = TRUE;
		if ($doDebug) {
			echo "<br />at pass 4 with result_option of $result_option<br />";
		}
		$content										.= "<h3>$jobname</h3>";
		if ($result_option == 'option') {
			$student_no_catalog						= 'Y';
			$content									.= "The catalog is not yet available.<br />
															set student_no_catalog to Y<br />";
			if (count($inp_sked_times) > 0) {
				$student_catalog_options 			= "";
				$firstTime								= TRUE;
				foreach($inp_sked_times as $thisValue) {
					if ($firstTime) {
						$firstTime						= FALSE;
						$student_catalog_options	= $thisValue;
					} else {
						$student_catalog_options	= "$student_catalog_options,$thisValue";
					}
				}
				$myInt									= strpos($student_catalog_options,'ANY');
				if ($myInt !== FALSE) {
					$student_flexible				= 'Y';
					$content							.= "student_flexible set to Y<br />";
					$student_catalog_options		= 'ANY';
					$content							.= "student_catalog_options set to ANY<br />";
				} else {
					$content								.= "student_catalog_options set to $student_catalog_options<br />";
				}
			
			}
		} elseif ($result_option == 'catalog') {
			if ($doDebug) {
				echo "handling the catalog option<br />
				inp_sked1: $inp_sked1<br />
				inp_sked2: $inp_sked2<br />
				inp_sked3: $inp_sked3<br />
				inp_flex: $inp_flex<br />";
			}
			return $content;
/*			
			$student_no_catalog 		= 'N';
			$content					.= "Set student_no_catalog to N<br />
											Would also set response to Y<br />";
			$ii 						= -1;
			$schedArray					= explode("@",$schedAvail);
			if ($doDebug) {
				echo "schedArray:<br /><pre>";
				print_r($schedArray);
				echo "</pre><br />";
			}
			$student_flexible						= '';
			foreach($inp_sked_times as $thisValue) {
				$ii++;
				if ($doDebug) {
					echo "$ii: got an inp_sked_times: $thisValue and ii is $ii<br />";
				}
				if($thisValue == '1') {
					$myStr							= $schedArray[$ii];
					$myArray						= explode("|",$myStr);
					$student_first_class_choice		= $myArray[0];
					$student_first_class_choice_utc	= $myArray[1];
					$content		.= "set student_first_class_choice to $student_first_class_choice<br />
										set student_first_class_choice_utc to $student_first_class_choice_utc<br />";
				} elseif ($thisValue == '2') {
					$myStr							= $schedArray[$ii];
					$myArray						= explode("|",$myStr);
					$student_second_class_choice		= $myArray[0];
					$student_second_class_choice_utc	= $myArray[1];
					$content		.= "set student_second_class_choice to $student_second_class_choice<br />
										set student_second_class_choice_utc to $student_second_class_choice_utc<br />";
				
				} elseif ($thisValue == '3') {
					$myStr							= $schedArray[$ii];
					$myArray						= explode("|",$myStr);
					$student_third_class_choice		= $myArray[0];
					$student_third_class_choice_utc	= $myArray[1];
					$content		.= "set student_third_class_choice to $student_third_class_choice<br />
										set student_third_class_choice_utc to $student_third_class_choice_utc<br />";
				
				} elseif ($thisValue == 'ANY') {
					$student_flexible	= 'Y';
				}
			}
			$content		.= "set student_flexible to $student_flexible<br />";
*/		
		} elseif ($result_option == 'avail') {
			if ($doDebug) {
				echo "<br />handling result_option avail<br />";
			}
			if ($inp_available == 'None') {
				$student_first_class_choice			= '';
				$student_first_class_choice_utc		= '';
				$content							.= "Set student_first_class_choice to $student_first_class_choice<br />
														Set student_first_class_choice_utc to $student_first_class_choice_utc<br />";
				$student_second_class_choice		= '';
				$student_second_class_choice_utc	= '';
				$student_third_class_choice			= '';
				$student_third_class_choice_utc		= '';
				$content							.= "Set second and third class choices to None<br />";
				$student_flexible					= "N";
				$content							.= "Set student_flexible to N<br />";
				$student_no_catalog					= 'N';
				$content							.= "Set student_no_catalog to N<br />
														will also set response to Y if not set already<br />";		
			} else {
				$myArray							= explode("|",$inp_available);
				$student_first_class_choice			= $myArray[0];
				$student_first_class_choice_utc		= $myArray[1];
				$content							.= "Set student_first_class_choice to $student_first_class_choice<br />
														Set student_first_class_choice_utc to $student_first_class_choice_utc<br />";
				$student_second_class_choice		= 'None';
				$student_second_class_choice_utc	= 'None';
				$student_third_class_choice			= 'None';
				$student_third_class_choice_utc		= 'None';
				$content							.= "Set second and third class choices to None<br />";
				$student_flexible					= "N";
				$content							.= "Set student_flexible to N<br />";
				$student_no_catalog					= 'N';
				$content							.= "Set student_no_catalog to N<br />
														will also set response to Y if not set already<br />";		
			}
		}
		$content			.= "<br />
								<form method='post' action='$theURL' 
								name='doitagain' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='1'>
								<input type='hidden' name='result_option' value='$result_option'>
								<input type='hidden' name='student_semester' value='$student_semester'>
								<input type='hidden' name='student_level' value='$student_level'> 
								<input type='hidden' name='student_no_catalog' value='$student_no_catalog'> 
								<input type='hidden' name='student_catalog_options' value='$student_catalog_options'>
								<input type='hidden' name='student_flexible' value='$student_flexible'>  
								<input type='hidden' name='student_first_class_choice_utc' value='$student_first_class_choice_utc'> 
								<input type='hidden' name='student_second_class_choice_utc' value='$student_second_class_choice_utc'> 
								<input type='hidden' name='student_third_class_choice_utc' value='$student_third_class_choice_utc'> 
								<input type='hidden' name='student_first_class_choice' value='$student_first_class_choice'> 
								<input type='hidden' name='student_second_class_choice' value='$student_second_class_choice'> 
								<input type='hidden' name='student_third_class_choice' value='$student_third_class_choice'> 
								<input type='hidden' name='student_timezone_offset' value='$student_timezone_offset'>
								<input class='formInputButton' type='submit'  value='Click to Do It Again' />
		}						</form>";

	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('rkstest_new_catalog', 'rkstest_new_catalog_func');
