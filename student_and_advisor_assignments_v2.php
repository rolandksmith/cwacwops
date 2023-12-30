function student_and_advisor_assignments_v2_func() {

/*
 	Arrays:
 	AdvisorDataArray - has the info about the advisor to go in the heading
		advisorDataArray[advisor_call_sign]['last name']
										   ['first name']
										   ['state']
										   ['country']
										   ['email']
										   ['phone']
										   ['time zone']
 							
 	AdvisorClassArray - has the info about the advisor's class
 		[advisor_call_sign]	[seq] 	[level]
 									[class size]
 									[utc time]
 									[utc days]
 							
 	AdvisorStudentArray - has the info about which students are assigned to an advisor's class
 		advisor_call_sign|level|sequence [increment] = student call sign
 		
 	StudentDataArray - has info about the student
	studentDataArray[student_call_sign]['last name']
									   ['first name']
									   ['level']
									   ['time zone']
									   ['email']
									   ['phone']
									   ['messaging']
									   ['state']
									   ['response']
									   ['status']
									   ['country']
									   ['youth']
									   ['class_priority']
									   ['first class choice']
									   ['second class choice']
									   ['third class choice']
									   ['first class choice local']
									   ['second class choice local']
									   ['third class choice local']
									   ['pi']
									   ['excluded advisor']
									   ['assigned advisor']
									   ['assigned advisor class']

	StudentClassArray
		studentClassArray[] 	= student_level|thisAdvisor|student_assigned_advisor_class|student_call_sign

	UnassignedArray - has info about students who are unassigned to any advisor class
		an array for each of the four levels
		
		unassignedBeginnerArray
			[increment]	= class_priority|student_call_sign|time zone
			
		unassignedFundamentalArray
			[increment]	= class_priority|student_call_sign|time zone
			
		unassignedIntermediateArray
			[increment]	= class_priority|student_call_sign|time zone
			
		unassignedAdvancedArray
			[increment]	= class_priority|student_call_sign|time zone
			
	Slots Available Array - advisor classes with open slots
		slotsAvailableArray[] 	= "Level|LastName, FirstName (CallSign)|TimeZone|Sequence|Time Days|SlotsAvail		
		 							
	Advisor Small Class Array - advisor classes with open slots
		advisorSmallClass[]	= "LastName, FirstName (CallSign)|TimeZone|Level|Sequence|Time Days|NumberStudents"
		
	ArbitraryArray - students arbitrarily assigned
		arbitraryArray[]	= student_call_sign
 	
 	
 	modified 7Jan2020 by Roland to select semester to be displayed
	modified 12Mar2020 by Roland to eliminate duplicate records
	Modified 14Mar2020 by Roland to display advisors w/o students
	Modified 1Aug2020 by Roland to select either assigned or pre-assigned student
	modified 10Aug2020 by Roland significantly overhaul the logic
	modified 13Dec2020 by Roland to add the state to the advisor class display and 
		display the advisors with small classes
	modified 26Dec2020 by Roland to add class time to advisor class header
	modified 13Mar2021 by Roland to add large class report
	Extensively modified 9July2021 by Roland for the new formats and assignment process
	Modified 22Aug21 by Roland to
		sort advisor class slots available report by level
		do a lookback to previous semesters for Fundamental level students and indicate if the 
			student has taken a basic class previously and was promotable
		sort the unassigned student reports by class priority, add class priority to the 
			report
	Modified 29Oct2021 by Roland to select the semester rather than just show the current
		semester
	Modified 6Dec21 by Roland to add the arbitrary assignments information
	Modified 31Dec21 by Roland to move to table structure from pods
	Modified 29Oct22 by Roland for new timezone table format
	Modified 17Apr23 by Roland to fix action_log
	Modified 15July23 by Roland to use the consolidated tables

*/
	global $wpdb, $advisorClassArray, $doDebug;

	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$doDebug 					= FALSE;
	$testMode					= FALSE;
	$thisVersion				= '2';
	$inp_semester				= $initializationArray['nextSemester'];
	$userName					= $initializationArray['userName'];
	$siteURL					= $initializationArray['siteurl'];
	$strPass					= "1";
	$advisorDataArray			= array();
	$advisorClassArray			= array();
	$advisorStudentArray		= array();
	$studentDataArray			= array();
	$studentClassArray			= array();
	$unassignedArray			= array();
	$xStudentArray				= array();
	$CRArray					= array();
	$slotsAvailableArray		= array();
	$errorArray					= array();
	$holdArray					= array();
	$unassignedBeginnerArray	= array();
	$unassignedFundamentalArray		= array();
	$unassignedIntermediateArray	= array();
	$unassignedAdvancedArray	= array();
	$errorCount					= 0;
	$xStudentCount				= 0;
	$defaultClassSize			= $initializationArray['defaultClassSize'];
	$indexCount					= 0;
	$advisorSmallClass			= array();
	$advisorLargeClass			= array();
	$arbitraryArray				= array();
	$advisorStudentCount		= 0;
	$inp_rsave				 	= '';
	$inp_verified				= '';
	$thisIncrement				= 0;
	$totalClasses				= 0;
	$totalAssigned				= 0;
	$totalVerifiedStudents		= 0;
	$theURL						= "$siteURL/cwa-student-and-advisor-assignments-v2/";
	$studentUpdateURL			= "$siteURL/cwa-display-and-update-student-information/";	
	$studentHistoryURL		 	= "$siteURL/cwa-show-detailed-history-for-student/";	
	$studentManagementURL		= "$siteURL/cwa-student-management/";
	$pastSemesters				= $initializationArray['pastSemesters'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$pastSemesterArray			= explode("|",$pastSemesters);
	$inp_semesterlist			= '';
	$validTestmode				= $initializationArray['validTestmode'];
	$jobname					= "Student and Advisor Assignments V$thisVersion";
	
	$levelConvert				= array('Beginner'=>1,'Fundamental'=>2,'Intermediate'=>3,'Advanced'=>4);
	$levelBack					= array(1=>'Beginner',2=>'Fundamental',3=>'Intermediate',4=>'Advanced');

	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);

	/// this array is in 2-hour blocks
	$timeConvertArray			= array('0000'=>'00:00-2:00am',
										'0030'=>'00:00-2:00am',
										'0100'=>'1:00-3:00am',
										'0130'=>'1:00-3:00am',
										'0200'=>'2:00-4:00am',
										'0230'=>'2:00-4:00am',
										'0300'=>'3:00-5:00am',
										'0330'=>'3:00-5:00am',
										'0400'=>'4:00-6:00am',
										'0430'=>'4:00-6:00am',
										'0500'=>'5:00-7:00am',
										'0530'=>'5:00-7:00am',
										'0600'=>'6:00-8:00am',
										'0630'=>'6:00-8:00am',
										'0700'=>'7:00-9:00am',
										'0730'=>'7:00-9:00am',
										'0800'=>'8:00-10:00am',
										'0830'=>'8:00-10:00am',
										'0900'=>'9:00-11:00am',
										'0930'=>'9:00-11:00am',
										'1000'=>'10:00-12:00pm',
										'1030'=>'10:00-12:00pm',
										'1100'=>'11:00-1:00pm',
										'1130'=>'11:00-1:00pm',
										'1200'=>'Noon-2:00pm',
										'1230'=>'Noon-2:00pm',
										'1300'=>'1:00-3:00pm',
										'1330'=>'1:00-3:00pm',
										'1400'=>'2:00-4:00pm',
										'1430'=>'2:00-4:00pm',
										'1500'=>'3:00-5:00pm',
										'1530'=>'3:00-5:00pm',
										'1600'=>'4:00-6:00pm',
										'1630'=>'4:00-6:00pm',
										'1700'=>'5:00-7:00pm',
										'1730'=>'5:00-7:00pm',
										'1800'=>'6:00-8:00pm',
										'1830'=>'6:00-8:00pm',
										'1900'=>'7:00-9:00pm',
										'1930'=>'7:00-9:00pm',
										'2000'=>'8:00-10:00pm',
										'2030'=>'8:00-10:00pm',
										'2100'=>'9:00-12:00pm',
										'2130'=>'9:00-12:00pm',
										'2200'=>'10:00-00:00pm',
										'2230'=>'10:00-00:00pm',
										'2300'=>'11:00pm-01:00am',
										'2330'=>'11:00pm-01:00am');
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "inp_debug") {
				$inp_debug		 = $str_value;
				$inp_debug		 = filter_var($inp_debug,FILTER_UNSAFE_RAW);
				if ($inp_debug == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester		 = $str_value;
				$inp_semester		 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semesterlist") {
				$inp_semesterlist		 = $str_value;
				$inp_semesterlist		 = filter_var($inp_semesterlist,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_type") {
				$inp_type		 = $str_value;
				$inp_type		 = filter_var($inp_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "level") {
				$inp_level		 = $str_value;
				$inp_level		 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "timezone") {
				$inp_timezone		 = $str_value;
				$inp_timezone		 = filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_rsave") {
				$inp_rsave		 = $str_value;
				$inp_rsave		 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verified") {
				$inp_verified		 = $str_value;
				$inp_verified		 = filter_var($inp_verified,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
		}
	}
	if ($testMode) {
		$extMode				= 'tm';
		if ($doDebug) {
			echo "Function is under development. Using student2 and advisor2, not the production data.<br />";
		}
		$theStatement			= "<p>Running in TESTMODE using test data.</p>";
	} else {
		$extMode				= 'pd';
		$theStatement			= "";
	}


function getTheReason($strReasonCode) {
	if ($strReasonCode == 'H') {
		return "(H) Student not promotable but signed up for next class level";
	}
	if ($strReasonCode == 'Q') {
		return "(Q) Student not evaluated but signed up for next class level";
	}
	if ($strReasonCode == 'W') {
		return "(W) Student withdrew but signed up for next class level";
	}
	if ($strReasonCode == 'E') {
		return "(E) Advisor has not evaluated the student who has signed up for next class level";
	}
	if ($strReasonCode == 'A') {
		return "(A) Student hard-assigned to AC6AC";
	}
	if ($strReasonCode	== 'X') {
		return "(H) Student is being recycled for schedule issues";
	}
	return "($strReasonCode) unknown";
}	


function checkAdvisorClass($student_call_sign='',$assigned_advisor='',$student_assigned_advisor_class='',$student_level='') {

/* 	checks advisorClassArray to see if the assigned advisor and level exist in the array
	if found, returns TRUE
	Otherwise, if not found or data is missing, returns FALSE
*/

	global $advisorClassArray,$doDebug;

// $doDebug = TRUE;
	
	if ($doDebug) {
		echo "FUNCTION checkAdvisorClass with $student_call_sign, $assigned_advisor, $student_assigned_advisor_class, and $student_level<br />";
	}
	
	if ($student_call_sign == '') {
		if ($doDebug) {
			echo "in checkAdvisorClass: student call sign missing<br />";
		}
		return FALSE;
	}
	if ($assigned_advisor == '') {
		if ($doDebug) {
			echo "in checkAdvisorClass: assigned_advisor missing<br />";
		}
		return FALSE;
	}
	if ($student_assigned_advisor_class == '') {
		if ($doDebug) {
			echo "in checkAdvisorClass: assigned_advisor_class missing<br />";
		}
		return FALSE;
	}
	if ($student_level == '') {
		if ($doDebug) {
			echo "in checkAdvisorClass: student level missing<br />";
		}
		return FALSE;
	}
	
	if (isset($advisorClassArray[$assigned_advisor][$student_assigned_advisor_class]['level'])) {
		$thisLevel 	= $advisorClassArray[$assigned_advisor][$student_assigned_advisor_class]['level'];
		if ($student_level == $thisLevel) {
			if ($doDebug) {
				echo "found data at advisorClassArray[assigned_advisor][student_assigned_advisor_class][student_level]<br />";
			}
			return TRUE;
		} else {
			if ($doDebug) {
				echo "The level found at advisorClassArray[$assigned_advisor][$student_assigned_advisor_class]['level'] is $thisLevel which does not match $student_level<br />";
			}
			return FALSE;
		}
	} else {
		if ($doDebug) {
			echo "NO data found at advisorClassArray [$assigned_advisor][$student_assigned_advisor_class][$student_level]<br />";
		}
		return FALSE;
	}
}

	
// The content to be returned initially includes the special style information.
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

table{font:'Times New Roman', sans-serif;background-image:none;}

th {color:#ffff;background-color:#000;padding:5px;font-size:small;}

td {padding:5px;font-size:small;}

table {table-layout:auto;padding:5px;}

th:first-child,
td:first-child {
 padding-left: 10px;
}

th:last-child,
td:last-child {
	padding-right: 5px;
}
</style>";	


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting. Building semester option list<br />";
		}
		$optionList			= "";
		if ($currentSemester != 'Not in Session') {
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$currentSemester' checked='checked'> $currentSemester<br />";
		}
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$nextSemester'> $nextSemester<br />";		
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$semesterTwo'> $semesterTwo<br />";		
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$semesterThree'> $semesterThree<br />";
		$optionList		.= "----<br />";
		$myInt				= count($pastSemesterArray) - 1;
		for ($ii=$myInt;$ii>-1;$ii--) {
	 		$thisSemester		= $pastSemesterArray[$ii];
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$thisSemester'> $thisSemester<br />";
			if ($doDebug) {
				echo "Added $thisSemester to option list<br />";
			}
		}
		if ($doDebug) {
			echo "optionlist complete<br />";
		}
		
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
							<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
							<td><input type='radio' class='formInputButton' name='inp_debug' value='N' checked='checked'> Standard Output<br />
								<input type='radio' class='formInputButton' name='inp_debug' value='Y'> Turn on Debugging </td></tr>";
		} else {
			$testModeOption	= '';
		}
		$content 			.= "<h3>$jobname</h3>
								$theStatement
								<p>Select the semester of interest from the list below:</p>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px; vertical-align:top;'>Semester</td><td>
								$optionList
								</td></tr>
								<tr><td>Advisor Records to Include</td>
								<td><input type='radio' class='formInputButton' name='inp_type' value='assigned' checked='checked'>Assigned Advisors (use after students have been assigned)<br />
									<input type='radio' class='formInputButton' name='inp_type' value='pre-assigned'>Pre-assigned Advisors (use before students have been assigned)</td></tr>
								<tr><td>Student Records to Include</td>
								<td><input type='radio' class='formInputButton' name='inp_verified' value='verified' checked='checked'>Verified students (use after students verified attendance)<br />
									<input type='radio' class='formInputButton' name='inp_verified' value='all'>All students except R</td></tr>
								$testModeOption
								<tr><td>Save this report to the reports achive?</td>
								<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
									<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass 2 in version $thisVersion<br />";
		}
 		
 		$inp_semester		= $inp_semesterlist;
 		
		if ($testMode) {
			$advisorTableName	= 'wpw1_cwa_consolidated_advisor2';
			$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
			$studentTableName		= 'wpw1_cwa_consolidated_student2';
		} else {
			$advisorTableName	= 'wpw1_cwa_consolidated_advisor';
			$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
			$studentTableName		= 'wpw1_cwa_consolidated_student';
		}
 		
 		if ($doDebug) {
 			echo "Using inp_semester: $inp_semester<br />
					advisorTableName: $advisorTableName<br />
					advisorClassTableName: $advisorClassTableName<br />
					studentTableName: $studentTableName<br />";
 		}
 
		if ($doDebug) {
 			echo "<b>Building the advisor arrays</b><br /><br />";
		}
		$continueProgram			= TRUE;
		$sql						= "select * from $advisorTableName 
										where semester='$inp_semester' 
										order by call_sign";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);
						

					if ($doDebug) {
						echo "<br />Processing advisor $advisor_call_sign<br />";
					}
					$processAdvisor							= TRUE;
					if ($advisor_survey_score == '6' || $advisor_survey_score == 9 || $advisor_survey_score == 13) { 	/// bypass this advisor
						$processAdvisor						= FALSE;
						if ($doDebug) {
							echo "Advisor $advisor_call_sign has a survey score of $advisor_survey_score. Bypassing<br />";
						}
						$errorArray[]						= "Advisor $advisor_call_sign bypassed due to survey score of $advisor_survey_score<br />";
					}
					if ($advisor_verify_response == 'R') {
						$processAdvisor						= FALSE;
						if ($doDebug) {
							echo "Advisor $advisor_call_sign has a verify_response of R. Bypassing<br />";
						}
					}
					if ($inp_type == 'assigned') {
						if ($advisor_verify_response != 'Y') {	//// bypass this advisor
							$processAdvisor						= FALSE;
							if ($doDebug) {
								echo "Advisor $advisor_call_sign has a verify_response of $advisor_verify_response. Bypassed<br />";
							}
						}
					}
					if ($processAdvisor) {
						$thisIncrement++;
						
						$advisorDataArray[$advisor_call_sign]['last name']	= $advisor_last_name;
						$advisorDataArray[$advisor_call_sign]['first name']	= $advisor_first_name;
						$advisorDataArray[$advisor_call_sign]['state']		= $advisor_state;
						$advisorDataArray[$advisor_call_sign]['country']	= $advisor_country;
						$advisorDataArray[$advisor_call_sign]['email']		= $advisor_email;
						$advisorDataArray[$advisor_call_sign]['phone']		= $advisor_phone;
						$advisorDataArray[$advisor_call_sign]['time zone']	= $advisor_timezone_offset;
 				
		 				if ($doDebug)  {
		 					echo "Added advisor to the advisorDataArray. Getting classes<br />";
		 				}
 
						// now get the advisor's classes and build the advisorClassArray

						$sql							= "select * from $advisorClassTableName 
														   where advisor_call_sign='$advisor_call_sign' 
														   and semester='$inp_semester' 
														   order by advisor_call_sign";
						$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
						if ($wpw1_cwa_advisorclass === FALSE) {
							$myError			= $wpdb->last_error;
							$myQuery			= $wpdb->last_query;
							if ($doDebug) {
								echo "Reading $advisorClassTableName table failed<br />
									  wpdb->last_query: $myQuery<br />
									  wpdb->last_error: $myError<br />";
							}
							$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
							sendErrorEmail($errorMsg);
						} else {
							$numACRows						= $wpdb->num_rows;
							if ($doDebug) {
								$myStr						= $wpdb->last_query;
								echo "ran $myStr<br />and found $numACRows rows<br />";
							}
							if ($numACRows > 0) {
								foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
									$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
									$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
									$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
									$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
									$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
									$advisorClass_sequence 					= $advisorClassRow->sequence;
									$advisorClass_semester 					= $advisorClassRow->semester;
									$advisorClass_timezone 					= $advisorClassRow->time_zone;
									$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
									$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
									$advisorClass_level 					= $advisorClassRow->level;
									$advisorClass_class_size 				= $advisorClassRow->class_size;
									$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
									$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
									$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
									$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
									$advisorClass_action_log 				= $advisorClassRow->action_log;
									$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
									$advisorClass_date_created				= $advisorClassRow->date_created;
									$advisorClass_date_updated				= $advisorClassRow->date_updated;

									$advisorClass_advisor_last_name 		= no_magic_quotes($advisorClass_advisor_last_name);
								
									if ($advisorClass_class_size == '') {
										$advisorClass_class_size = $defaultClassSize;
									}
									$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['level']			= $advisorClass_level;
									$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['class size']	= $advisorClass_class_size;
									$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['utc time']		= $advisorClass_class_schedule_times_utc;
									$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['utc days']		= $advisorClass_class_schedule_days_utc;
									$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['local time']	= $advisorClass_class_schedule_times;
									$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['local days']	= $advisorClass_class_schedule_days;

									if ($doDebug) {
										echo "Advisor $advisor_call_sign class seq $advisorClass_sequence at level $advisorClass_level added to advisorClassArray<br />";
									}		
								}
							} else {
								$errorArray[]	= "Advisor $advisor_call_sign has no classes in $advisorClassTableName<br />";
								if ($doDebug) {
									echo "added advisor_call_sign of $advisor_call_sign to the errorArray <br />";
								}
							}		
						}
					}
				}	// end of advisor while
			} else {				//// no advisor records
				if ($doDebug) {
					echo "No records found in advisor table<br />";
				}
				$continueProgram		= FALSE;
			}
		
			if ($continueProgram) {
				// Get the students and build the related student arrays
				if ($doDebug) {
					echo "<br /><br /><b>Building Student Arrays</b><br />";
				}
				if ($inp_verified == 'verified') {
					$sql				= "select * from $studentTableName 
											where semester='$inp_semester' 
											and response='Y' order by call_sign";
				} else {
					$sql			 	= "select * from $studentTableName 
											where semester='$inp_semester'";
				}

				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $studentTableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
				} else {
					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numSRows rows<br />";
					}
					if ($numSRows > 0) {
						foreach ($wpw1_cwa_student as $studentRow) {
							$student_ID								= $studentRow->student_id;
							$student_call_sign						= strtoupper($studentRow->call_sign);
							$student_first_name						= $studentRow->first_name;
							$student_last_name						= stripslashes($studentRow->last_name);
							$student_email  						= strtolower(strtolower($studentRow->email));
							$student_phone  						= $studentRow->phone;
							$student_ph_code						= $studentRow->ph_code;
							$student_city  							= $studentRow->city;
							$student_state  						= $studentRow->state;
							$student_zip_code  						= $studentRow->zip_code;
							$student_country  						= $studentRow->country;
							$student_country_code					= $studentRow->country_code;
							$student_time_zone  					= $studentRow->time_zone;
							$student_timezone_id					= $studentRow->timezone_id;
							$student_timezone_offset				= $studentRow->timezone_offset;
							$student_whatsapp						= $studentRow->whatsapp_app;
							$student_signal							= $studentRow->signal_app;
							$student_telegram						= $studentRow->telegram_app;
							$student_messenger						= $studentRow->messenger_app;					
							$student_wpm 	 						= $studentRow->wpm;
							$student_youth  						= $studentRow->youth;
							$student_age  							= $studentRow->age;
							$student_student_parent 				= $studentRow->student_parent;
							$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
							$student_level  						= $studentRow->level;
							$student_waiting_list 					= $studentRow->waiting_list;
							$student_request_date  					= $studentRow->request_date;
							$student_semester						= $studentRow->semester;
							$student_notes  						= $studentRow->notes;
							$student_welcome_date  					= $studentRow->welcome_date;
							$student_email_sent_date  				= $studentRow->email_sent_date;
							$student_email_number  					= $studentRow->email_number;
							$student_response  						= strtoupper($studentRow->response);
							$student_response_date  				= $studentRow->response_date;
							$student_abandoned  					= $studentRow->abandoned;
							$student_student_status  				= strtoupper($studentRow->student_status);
							$student_action_log  					= $studentRow->action_log;
							$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
							$student_selected_date  				= $studentRow->selected_date;
							$student_no_catalog			 			= $studentRow->no_catalog;
							$student_hold_override  				= $studentRow->hold_override;
							$student_messaging  					= $studentRow->messaging;
							$student_assigned_advisor  				= $studentRow->assigned_advisor;
							$student_advisor_select_date  			= $studentRow->advisor_select_date;
							$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
							$student_hold_reason_code  				= $studentRow->hold_reason_code;
							$student_class_priority  				= $studentRow->class_priority;
							$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
							$student_promotable  					= $studentRow->promotable;
							$student_excluded_advisor  				= $studentRow->excluded_advisor;
							$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
							$student_available_class_days  			= $studentRow->available_class_days;
							$student_intervention_required  		= $studentRow->intervention_required;
							$student_copy_control  					= $studentRow->copy_control;
							$student_first_class_choice  			= $studentRow->first_class_choice;
							$student_second_class_choice  			= $studentRow->second_class_choice;
							$student_third_class_choice  			= $studentRow->third_class_choice;
							$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
							$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
							$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
							$student_date_created 					= $studentRow->date_created;
							$student_date_updated			  		= $studentRow->date_updated;

							$student_last_name 						= no_magic_quotes($student_last_name);


							if ($doDebug) {
								echo "<br />Processing student $student_call_sign
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Response: $student_response
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Status: $student_student_status
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Pre-assigned Advisor: $student_pre_assigned_advisor
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor
										<br />&nbsp;&nbsp;&nbsp;&nbsp;First Class Choice: $student_first_class_choice
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Second Class Choice: $student_second_class_choice
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Third Class Choice: $student_third_class_choice
										<br />&nbsp;&nbsp;&nbsp;&nbsp;First Class Choice utc: $student_first_class_choice_utc
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Second Class Choice utc: $student_second_class_choice_utc
										<br />&nbsp;&nbsp;&nbsp;&nbsp;Third Class Choice utc: $student_third_class_choice_utc<br />";
							}
							if ($student_response == 'Y') {
								$totalVerifiedStudents++;
							}

							$continueToProcess		= TRUE;
							if ($student_response == 'R') {		/// bypass this guy
								if ($doDebug) {
									echo "Student $student_call_sign has a response of R. Bypassing<br />";
									$continueToProcess	= FALSE;
								}
							}
							// See if student is on hold. If so, assign to the holdArray
							if ($student_intervention_required == 'H') {
								$myStr				= getTheReason($student_hold_reason_code);
								$myStr			 	= "$myStr ($student_level)";
								if ($testMode) {
									$thisMode		= "student2";
								} else {
									$thisMode		= "student";
								}
								$holdArray[]		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>$student_call_sign</a> $student_last_name, $student_first_name $myStr<br />";
								$continueToProcess	= FALSE;
								if ($doDebug) {
									echo "Student $student_call_sign added to the hold array<br />";
								}
							}
							if ($student_student_status == 'R' || $student_student_status == 'C' || $student_student_status == 'V') {
								$continueToProcess = FALSE;
/*
								$newActionLog		= formatActionLog($student_action_log);
								$CRArray[]			= "$student_level|<a href=\"javascript:window.alert('$newActionLog');\">$student_last_name, $student_first_name</a> (<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>
$student_call_sign</a>)|$student_email|$student_phone|$student_state|$student_timezone_offset|$student_student_status|$student_assigned_advisor";
*/
								if ($doDebug) {
									echo "Student $student_call_sign has a student_status of $student_student_status. Bypassed<br />";
								}
							}
							if ($student_student_status == 'N') {				// do nothing. Student quit
								$continueToProcess	= FALSE;
								if ($doDebug) {
									echo "Student $student_call_sign has a student_status of 'N'<br />";
								}
							}

							if ($continueToProcess) {
								if ($student_first_class_choice == '') {
									 $student_first_class_choice 		= 'None';
								}
								if ($student_second_class_choice == '') {
									 $student_second_class_choice 		= 'None';
								}
								if ($student_third_class_choice == '') {
									 $student_third_class_choice 		= 'None';
								}
								$thisIncrement++;

								if ($student_no_catalog == 'Y' && $student_assigned_advisor != '') {			/// add to arbitraryArray
									$arbitraryArray[]											= $student_call_sign;
								} else {
									$student_no_catalog											= '';
								}
								// add student to studentDataArray
								$studentDataArray[$student_call_sign]['last name']				= $student_last_name;
								$studentDataArray[$student_call_sign]['first name']				= $student_first_name;
								$studentDataArray[$student_call_sign]['level']					= $student_level;
								$studentDataArray[$student_call_sign]['time zone']				= $student_timezone_offset;
								$studentDataArray[$student_call_sign]['email']					= $student_email;
								$studentDataArray[$student_call_sign]['phone']					= $student_phone;
								$studentDataArray[$student_call_sign]['messaging']				= $student_messaging;
								$studentDataArray[$student_call_sign]['state']					= $student_state;
								$studentDataArray[$student_call_sign]['response']				= $student_response;
								$studentDataArray[$student_call_sign]['status']					= $student_student_status;
								$studentDataArray[$student_call_sign]['country']				= $student_country;
								$studentDataArray[$student_call_sign]['youth']					= $student_youth;
								$studentDataArray[$student_call_sign]['class priority']			= $student_class_priority;
								$studentDataArray[$student_call_sign]['first class choice']		= $student_first_class_choice_utc;
								$studentDataArray[$student_call_sign]['second class choice']	= $student_second_class_choice_utc;
								$studentDataArray[$student_call_sign]['third class choice']		= $student_third_class_choice_utc;
								$studentDataArray[$student_call_sign]['first class choice local']	= $student_first_class_choice;
								$studentDataArray[$student_call_sign]['second class choice local']	= $student_second_class_choice;
								$studentDataArray[$student_call_sign]['third class choice local']	= $student_third_class_choice;
								$studentDataArray[$student_call_sign]['pi']						= '';
								$studentDataArray[$student_call_sign]['prom']					= $student_promotable;
								$studentDataArray[$student_call_sign]['assigned advisor']		= $student_assigned_advisor;
								$studentDataArray[$student_call_sign]['assigned advisor class']	= $student_assigned_advisor_class;
								$studentDataArray[$student_call_sign]['arbitrarily assigned']	= $student_no_catalog;
								$studentDataArray[$student_call_sign]['pre_assigned_advisor']	= $student_pre_assigned_advisor;
								$myArray			= explode(" ",$student_request_date);
								$studentDataArray[$student_call_sign]['request_date']			= $myArray[0];
							
							
								if ($student_hold_reason_code == 'X') {
									$studentDataArray[$student_call_sign]['excluded advisor']		= $student_excluded_advisor;
								} else {
									$studentDataArray[$student_call_sign]['excluded advisor']		= '';
								}
								if ($student_youth == 'Yes') {
									if ($student_age < 18) { 
										if ($student_student_parent == '') {
											$student_student_parent	= 'Not Given';
										}
										if ($student_student_parent_email == '') {
											$student_student_parent_email = 'Not Given';
										}
										$studentDataArray[$student_call_sign]['pi']			= "The student is a youth under the age of 18. 
																								Parent or guardian is $student_student_parent at email address $student_student_parent_email.</td></tr>";
									} else {
										$studentDataArray[$student_call_sign]['youth']		= 'No';
									}
								}
							
								if ($doDebug) {
									echo "StudentDataArray for $student_call_sign built<br />";
								}
							
								// if student assigned (or pre-assigned) to an advisor, add to advisorStudentArray and studentClassArray
								$doAssignment				= FALSE;
								if ($inp_type == 'assigned') {
									if (($student_student_status == 'S' || $student_student_status == 'Y') and $student_assigned_advisor != '') {
										$doAssignment		= TRUE;
										$thisAdvisor		= $student_assigned_advisor;
									}
								} elseif ($inp_type == 'pre-assigned') {
									if ($student_pre_assigned_advisor != '') {
										$doAssignment		= TRUE;
										$thisAdvisor		= $student_pre_assigned_advisor;
									}
								}
								if ($doAssignment) {
									$thisResult				= checkAdvisorClass($student_call_sign,$thisAdvisor,$student_assigned_advisor_class,$student_level);
									if ($thisResult == TRUE) {
										$advisorStudentArrayKey					= "$thisAdvisor|$student_level|$student_assigned_advisor_class";
										$advisorStudentArray[$advisorStudentArrayKey][$thisIncrement]	= $student_call_sign;
										$studentClassArray[] 	= "$student_level|$thisAdvisor|$student_assigned_advisor_class|$student_call_sign";
										if ($doDebug) {
											echo "Student $student_call_sign added to advisorStudentArray[$advisorStudentArrayKey][$thisIncrement]	= $student_call_sign<br />
												  Student added to studentClassArray[] 	= $student_level|$thisAdvisor|$student_assigned_advisor_class|$student_call_sign<br />";
										}
									} else {
										$errorArray[]		= "Student $student_call_sign assigned to $thisAdvisor class $student_assigned_advisor_class at level $student_level. Advisor does not have that class<br />";
									}
								} else {			/// student is unassigned. Add to the unassigned array
									$fixedPriority			= 3 - intval($student_class_priority);
									if ($doDebug) {
										echo "student $student_call_sign $student_level Priority $student_class_priority is unassigned<br />";
									}
									if ($student_level == 'Beginner') {
										$unassignedBeginnerArray[]		= "$fixedPriority|$student_timezone_offset|$student_call_sign";
										if ($doDebug) {
											echo "Student $student_call_sign added to beginner unassigned array $fixedPriority|$student_timezone_offset|$student_call_sign<br />";
										}
									} elseif ($student_level == 'Fundamental') {
										$unassignedFundamentalArray[]		= "$fixedPriority|$student_timezone_offset|$student_call_sign";
										if ($doDebug) {
											echo "Student $student_call_sign added to fundamental unassigned array $fixedPriority|$student_timezone_offset|$student_call_sign<br />";
										}
									} elseif ($student_level == 'Intermediate') {
										$unassignedIntermediateArray[]		= "$fixedPriority|$student_timezone_offset|$student_call_sign";
										if ($doDebug) {
											echo "Student $student_call_sign added to intermediate unassigned array $fixedPriority|$student_timezone_offset|$student_call_sign<br />";
										}
									} elseif ($student_level == 'Advanced') {
										$unassignedAdvancedArray[]		= "$fixedPriority|$student_timezone_offset|$student_call_sign";
										if ($doDebug) {
											echo "Student $student_call_sign added to advanced unassigned array $fixedPriority|$student_timezone_offset|$student_call_sign<br />";
										}
									}
								}
							}
						}				/// end of student while
					} else {			/// no students in student table
						if ($doDebug) {
							echo "No students found in the $studentTableName table<br />";
						}
						$continueProgram					= FALSE;
					}
				}
			} else {
				if ($doDebug) {
					echo "continueProgram set to FALSE. Student not processed<br />";
				}
			}
			if ($continueProgram) {
				// all arrays built
				if ($doDebug) {			/// dump the arrays
					echo "<br /><b>The Arrays</b>";

					if (count($advisorDataArray) > 0) {
						ksort($advisorDataArray);
						echo "<br/><b>Advisor Data Array</b><br /><pre>";
						print_r($advisorDataArray);
						echo "</pre><br />";
					} else {
						echo "<br />Advisor Data Array empty<br />";
					}
				
					if (count($advisorClassArray) > 0) {
						ksort($advisorClassArray);
						echo "<br/><b>Advisor Class Array</b><br /><pre>";
						print_r($advisorClassArray);
						echo "</pre><br />";
					} else {
						echo "<br />Advisor Class Array empty<br />";
					}
				
					if (count($advisorStudentArray) > 0) {
						ksort($advisorStudentArray);
						echo "<br/><b>Advisor Student Array</b><br /><pre>";
						print_r($advisorStudentArray);
						echo "</pre><br />";
					} else {
						echo "<br />Advisor Student Array empty<br />";
					}
				
					if (count($studentDataArray) > 0) {
						ksort($studentDataArray);	
						echo "<br/><b>student Data Array</b><br /><pre>";
						print_r($studentDataArray);
						echo "</pre><br />";
					} else {
						echo "<br />Student Data Array empty<br />";
					}
				
					if (count($studentClassArray) > 0) {
						sort($studentClassArray);
						echo "<br/><b>student Class Array</b><br />";
						foreach($studentClassArray as $myValue) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;$myValue<br />";
						}
						echo "<br />";
					} else {
						echo "<br />Student Class Array empty<br />";
					}
				
					if (count($unassignedBeginnerArray) > 0) {
						sort($unassignedBeginnerArray);
						echo "<br/><b>Unassigned Beginner Array</b><br />";
						foreach($unassignedBeginnerArray as $myValue) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;$myValue<br />";
						}
					} else {
						echo "<br />Unassigned Beginner Array empty<br />";
					}
				
					if (count($unassignedFundamentalArray) > 0) {
						sort($unassignedFundamentalArray);
						echo "<br/><b>Unassigned Fundamental Array</b><br />";
						foreach($unassignedFundamentalArray as $myValue) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;$myValue<br />";
						}
					} else {
						echo "<br />Unassigned Fundamental Array empty<br />";
					}
				
					if (count($unassignedIntermediateArray) > 0) {
						sort($unassignedIntermediateArray);
						echo "<br/><b>Unassigned Intermediate Array</b><br />";
						foreach($unassignedIntermediateArray as $myValue) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;$myValue<br />";
						}
					} else {
						echo "<br />Unassigned Intermediate Array empty<br />";
					}
				
					if (count($unassignedAdvancedArray) > 0) {
						sort($unassignedAdvancedArray);
						echo "<br/><b>Unassigned Advanced Array</b><br />";
						foreach($unassignedAdvancedArray as $myValue) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;$myValue<br />";
						}
					} else {
						echo "<br />Unassigned Advanced Array empty<br />";
					}
				
					if (count($holdArray) > 0) {
						sort($holdArray);
						echo "<br /><b>hold Array</b><br  />";
						foreach($holdArray as $myValue) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;$myValue";
						}
					} else {
						echo  "<br /><b>Hold Array</b> No Records</br />";
					}
				
					if (count($CRArray) > 0) {
						sort($CRArray);
						echo "<br /><b>CR Array</b><br  />";
						foreach($CRArray as $myValue) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;$myValue<br />";
						}
					} else {
						echo "<br /><b>CRArray</b> No Records</br />";
					}
				}

				// start report generation
			if ($doDebug) {
				echo "<br /><b>Report Generation</b><br />";
			}
			$content .= "<h2>$jobname</h2>\n
						$theStatement
						<p><a href='#report1'>Go to the Advisor Assignment Information Report</a><br />\n
						<a href='#report2'>Go to the Students Assignment Information</a><br />\n
						<a href='#reportARB'>Go to the Students Arbitrarily Assigned Report</a><br />\n
						<a href='#reportX'>Go to the Students Who Were Requested to be Replaced Report</a><br />\n
						<a href='#reportBB'>Go to All Advisors and Class Slots</a><br />\n
						<a href='#reportS'>Go to the Advisors with Small Classes Report</a><br />\n
						<a href='#reportY'>Go to the Advisor Class Slots Available Report</a><br />\n
						<a href='#report3'>Go to the Unassigned Student Information Report</a><br />\n
						<a href='#reportH'>Go to the Students on Hold Report</a><br />\n
						<a href='#reportE'>Go to the Errors Report</a><br />\n
						</p>\n";

			if ($doDebug) {
				echo "<br /><b>Starting the Advisor Assignment Information Report</b><br />\n";
			}
			$content					.= "<a name='report1'><h3>Current Advisor Assignment Information for $inp_semester</h3></a>\n<table>\n";
			$totalAdvisors				= 0;
			$totalClasses				= 0;
			$totalAssigned				= 0;
			ksort($advisorDataArray);
			foreach ($advisorDataArray as $thisCallSign=>$myValue) {
				if ($doDebug) {
					echo "<br />Doing advisor $thisCallSign<br />";
				}
				$thisFirstName			= $advisorDataArray[$thisCallSign]['first name'];
				$thisLastName			= $advisorDataArray[$thisCallSign]['last name'];
				$thisState				= $advisorDataArray[$thisCallSign]['state'];
				$thisCountry			= $advisorDataArray[$thisCallSign]['country'];
				$thisEmail				= $advisorDataArray[$thisCallSign]['email'];
				$thisPhone				= $advisorDataArray[$thisCallSign]['phone'];
				$thisTimeZone			= $advisorDataArray[$thisCallSign]['time zone'];
				
				$totalAdvisors++;
				$content	.= "<tr><th>Advisor</th>\n
									<th>Email</th>\n
									<th>Phone</th>\n
									<th>State</th>\n
									<th colspan='2'>Country</th></tr>\n
								<tr><td style='vertical-align:top;'>$thisLastName, $thisFirstName ($thisCallSign)</td>\n
									<td style='vertical-align:top;'>$thisEmail</td>\n
									<td style='vertical-align:top;'>$thisPhone</td>\n
									<td style='vertical-align:top;'>$thisState</td>\n
									<td colspan='2' style='vertical-align:top;'>$thisCountry</td></tr>\n";
				$advisorClasses	= 0;
				if(array_key_exists($thisCallSign,$advisorClassArray)) {	
					foreach($advisorClassArray[$thisCallSign] as $classSequence=>$classData) {
						$doOnce					= TRUE;
						if ($doDebug) {
							echo "Found sequence $classSequence for the advisor<br />";
						}
						$thisLevel	= $advisorClassArray[$thisCallSign][$classSequence]['level'];
						$thisSize	= $advisorClassArray[$thisCallSign][$classSequence]['class size'];
						$thisTime	= $advisorClassArray[$thisCallSign][$classSequence]['utc time'];
						$thisDays	= $advisorClassArray[$thisCallSign][$classSequence]['utc days'];
						$thisTimel	= $advisorClassArray[$thisCallSign][$classSequence]['local time'];
						$thisDaysl	= $advisorClassArray[$thisCallSign][$classSequence]['local days'];
						
						$advisorClasses++;
						$totalClasses++;
						
						$content	.= "<tr><td colspan='6'><b>Advisor Class:</b> $classSequence<br />\n
												<b>Class Size:</b> $thisSize&nbsp;&nbsp;&nbsp;
												<b>Level:</b> $thisLevel&nbsp;&nbsp;&nbsp;
												<b>Local:</b> $thisTimel $thisDaysl&nbsp;&nbsp;
												<b>UTC:</b> $thisTime $thisDays</td></tr>\n";

						// now get any students
						$studentCount				= 0;
						$thisSlotsAvail				= 0;
						$advisorStudentArrayKey		= "$thisCallSign|$thisLevel|$classSequence";
						if (array_key_exists($advisorStudentArrayKey,$advisorStudentArray)) {	/// have students
							if ($doDebug) {
								echo "There are students for class $classSequence<br />";
							}
							foreach($advisorStudentArray[$advisorStudentArrayKey] as $myKey=>$myValue) {
								if ($doDebug) {
									echo "Found $myKey = $myValue in advisorStudentArray when looking up $advisorStudentArrayKey<br />";
								}
								// got a student. Get the student data
								$thisStudent			= $myValue;
								$thisStudentFirstName	= $studentDataArray[$thisStudent]['first name'];
								$thisStudentlastName	= $studentDataArray[$thisStudent]['last name'];
								$thisStudentEmail		= $studentDataArray[$thisStudent]['email'];
								$thisStudentPhone		= $studentDataArray[$thisStudent]['phone'];
								$thisStudentMessaging	= $studentDataArray[$thisStudent]['messaging'];
								$thisStudentStatus		= $studentDataArray[$thisStudent]['status'];
								$thisStudentState		= $studentDataArray[$thisStudent]['state'];
								$thisStudentCountry		= $studentDataArray[$thisStudent]['country'];
								$thisStudentYouth		= $studentDataArray[$thisStudent]['youth'];
								$thisStudentPI			= $studentDataArray[$thisStudent]['pi'];
								$thisStudentProm		= $studentDataArray[$thisStudent]['prom'];
								$thisStudentArbitrary	= $studentDataArray[$thisStudent]['arbitrarily assigned'];
								$thisStudentPreAssign	= $studentDataArray[$thisStudent]['pre_assigned_advisor'];
								$thisFirstClassChoice	= $studentDataArray[$thisStudent]['first class choice'];
								$thisSecondClassChoice	= $studentDataArray[$thisStudent]['second class choice'];
								$thisThirdClassChoice	= $studentDataArray[$thisStudent]['third class choice'];
							
								if ($thisStudentYouth == 'No') {
									$thisStudentYouth	= '';
									$thisStudentPI		= '';
								}
								
								$studentCount++;
								$totalAssigned++;
								$myStr					= "";
								$preStr					= "";
								if ($thisStudentArbitrary == 'Y') {
									$myStr		.= "<b><em>Arbitrarily Assigned</em></b>";
								}
								if ($thisStudentPreAssign != '' && $thisStudentPreAssign == $thisCallSign) {
									$myStr		.= "<em>Student pre-assigned</em>";
								}
								$content	.= "<tr><td colspan='6' style='vertical-align:top;'><table style='border-bottom-style:solid;'>\n";
								if ($doOnce) {
								 	$doOnce		= FALSE;
								 	$content	.= "<tr><td style='width:250px;'><b>Student</b></td>\n
														<td style='width:200px;'><b>Email</b></td>\n
														<td style='width:200px;'><b>Phone</b></td>\n
														<td style='width:200px;'><b>State</b></td>\n
														<td><b>Country</b></td>\n
														<td><b>Status</b></td></tr>\n";
								}
								$content		.= "<tr><td style='vertical-align:top;width:250px;'>$thisStudentlastName, $thisStudentFirstName ($thisStudent)<br />$myStr</td>\n
														<td style='vertical-align:top;width:200px;'>$thisStudentEmail<br />$thisFirstClassChoice</td>\n
														<td style='vertical-align:top;width:200px;'>$thisStudentPhone ($thisStudentMessaging)<br />$thisSecondClassChoice</td>\n
														<td style='vertical-align:top;width:200px;'>$thisStudentState<br />$thisThirdClassChoice</td>\n
														<td style='vertical-align:top;'>$thisStudentCountry</td>\n
														<td style='text-align:center;vertical-align:top;'>$thisStudentStatus</td></tr>\n";
								if ($thisStudentYouth == 'Yes') {
									$content	.= "<tr><td colspan='6'>$thisStudentPI</td></tr>\n";
								}
								$content		.= "</table></td></tr>\n";
							}
							$content	.= "<tr><td colspan='6'>$studentCount Students Assigned to this Class</td></tr>\n";
							$thisSlotsAvail	= $thisSize - $studentCount;
							$content	.= "<tr><td colspan='6'><hr></td></tr>\n";
						} else {
							$content	.= "<tr><td colspan='6'>No Students Assigned to this Class</td></tr>\n";
							$thisSlotsAvail = $thisSize;
							if ($doDebug) {
								echo "No students found for class $classSequence<br />\n";
							}
							$content	.= "<tr><td colspan='6'><hr></td></tr>\n";
						}
///						if ($thisSlotsAvail > 0) {
							$thisConvert			= $levelConvert[$thisLevel];
 							$slotsAvailableArray[] 	= "$thisConvert|$thisLastName, $thisFirstName|$thisCallSign|$thisTimeZone|$classSequence|$thisTime $thisDays|$thisTimel $thisDaysl|$thisSlotsAvail|$thisSize";
 							$thisNumberStudents		= $thisSize - $thisSlotsAvail;
 							if ($doDebug) {
 								echo "added $thisLevel|$thisLastName, $thisFirstName|$thisCallSign|$thisTimeZone|$classSequence|$thisTime $thisDays|$thisTimel $thisDaysl|$thisSlotsAvail|$thisSize to slotsAvailableArray<br />\n";
 							}
 							if ($thisNumberStudents < 4) {			/// have a small class
 								$advisorSmallClass[]	= "$thisLastName, $thisFirstName ($thisCallSign)|$thisTimeZone|$thisLevel|$classSequence|$thisTime $thisDays|$thisTimel $thisDaysl|$thisNumberStudents|$thisSize";
 								if ($doDebug) {
 									echo "added $thisLastName, $thisFirstName ($thisCallSign)|$thisTimeZone|$thisLevel|$classSequence|$thisTime $thisDays|$thisTimel $thisDaysl|$thisNumberStudents|$thisSize to advisorSmallClass array<br />\n";
 								}
 							}
//						}
					}
				} else {
					$content			.= "<tr><td colspan='6'>No Class Records for this Advisor</td></tr>\n";
				}
				if ($doDebug) {
					echo "End of everything for this advisor<br />\n";
				}

			}
			$content		.= "</table>
<p>$totalAdvisors Total Advisors<br />
	$totalClasses Total Classes<br />
	$totalAssigned Total Assigned Students</p>";
		
		
//////// end of Current Advisor Assignment Information report			
	
	
//////// Start of Student Assignment Information Report		
			if ($doDebug) {
				echo "<br />Preparing Student Assignment Information Report<br />";
			}
			
// studentClassArray[] 	= student_level|thisAdvisor|student_assigned_advisor_class|student_call_sign

			$thisBeginner			= 0;
			$thisFundamental				= 0;
			$thisIntermediate		= 0;
			$thisAdvanced			= 0;
			
			$content		.= "<a name='report2'><h3>Student Assignment Information for $inp_semester</h3></a>
								<table style='width:900px;'>
								<tr><th>Level</th>
									<th>Advisor</th>
									<th>Class</th>
									<th>Student</th>
									<th>TZ</th>
									<th>Status</th>
									<th>Email</th>
									<th>Phone</th>
									<th>State</th>
									<th>Country</th>
									<th>Prom</th></tr>";
			
			$totalAssigned							= 0;
			$totalVerified							= 0;
			sort($studentClassArray);
			foreach($studentClassArray as $myValue) {
				if ($doDebug) {
					echo "Processing $myValue<br />";
				}
				$myArray							= explode("|",$myValue);
				$student_Advisor					= $myArray[1];
				$student_class						= $myArray[2];
				$student_level						= $myArray[0];
				$student_call_sign					= $myArray[3];
				
				$student_first_name					= $studentDataArray[$student_call_sign]['first name'];
				$student_last_name					= $studentDataArray[$student_call_sign]['last name'];
				$student_status						= $studentDataArray[$student_call_sign]['status'];
				$student_email						= $studentDataArray[$student_call_sign]['email'];
				$student_phone						= $studentDataArray[$student_call_sign]['phone'];
				$student_time_zone					= $studentDataArray[$student_call_sign]['time zone'];
				$student_state						= $studentDataArray[$student_call_sign]['state'];
				$student_country					= $studentDataArray[$student_call_sign]['country'];
				$student_messaging					= $studentDataArray[$student_call_sign]['messaging'];
				$student_prom						= $studentDataArray[$student_call_sign]['prom'];
 					$thisStudentArbitrary				= $studentDataArray[$student_call_sign]['arbitrarily assigned'];
				$myStr					= "";
				if ($thisStudentArbitrary == 'Y') {
					$myStr				= "<br /><b><em>Student Arbitrarily Assigned</em></b>";
				}
				
				$totalAssigned++;
				if ($student_level == 'Beginner') {
					$thisBeginner++;
				} elseif ($student_level == 'Fundamental') {
					$thisFundamental++;
				} elseif ($student_level == 'Intermediate') {
					$thisAdvanced++;
				}
				if ($student_status == 'Y') {
					$totalVerified++;
				}
				$content		.= "<tr><td style='vertical-align:top;'>$student_level</td>
										<td style='vertical-align:top;'>$student_Advisor$myStr</td>
										<td style='text-align:center;vertical-align:top;'>$student_class</td>
										<td style='vertical-align:top;'>$student_last_name, $student_first_name ($student_call_sign)</td>
										<td style='text-align:center;vertical-align:top;'>$student_time_zone</td>
										<td style='text-align:center;vertical-align:top;'>$student_status</td>
										<td style='vertical-align:top;'>$student_email</td>
										<td style='vertical-align:top;'>$student_phone ($student_messaging)</td>
										<td style='vertical-align:top;'>$student_state</td>
										<td style='vertical-align:top;'>$student_country</td>
										<td style='text-align:center;vertical-align:top;'>$student_prom</td></tr>";				
			}
			$content			.= "</table>
									<p>$totalAssigned Total Assigned Students<br />
									   $totalVerified Total students verified by their advisor</p><br />";
		
//////// End of Student Assignment Information Report	

//////// Start of Arbitrarily Assigned Student Information Report				

			if ($doDebug) {
				echo "<br />Start of the Arbitrarily Assigned Student Information Report<br />";
			}
			$content		.= "<a name='reportARB'><h3>Arbitrarily Assigned Student Information for $inp_semester</h3></a>
								<p><table style='width:1000px;'><tr>
								<tr><th>Call Sign</th>
									<th>Name</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Level</th>
									<th>Country</th>
									<th>TZ</th>
									<th>Class Choices Local</th>
									<th>Assigned Advisor</th>
									<th>Local Assigned Class Time</th></tr>";
			$arbitraryCount			= 0;
			$myInt			= count($arbitraryArray);
			if ($myInt > 0) {
				foreach($arbitraryArray as $thisStudentCallSign) {
					if ($doDebug) {
						echo "processing $thisStudentCallSign<br />";
					}
					$arbitraryCount++;
					$thisFirstName			= $studentDataArray[$thisStudentCallSign]['first name'];
					$thisLastName			= $studentDataArray[$thisStudentCallSign]['last name'];
					$thisTimeZone			= $studentDataArray[$thisStudentCallSign]['time zone'];
					$thisLevel				= $studentDataArray[$thisStudentCallSign]['level'];
					$thisEmail				= $studentDataArray[$thisStudentCallSign]['email'];
					$thisPhone				= $studentDataArray[$thisStudentCallSign]['phone'];
					$thisMessaging			= $studentDataArray[$thisStudentCallSign]['messaging'];
					$thisState				= $studentDataArray[$thisStudentCallSign]['state'];
					$thisCountry			= $studentDataArray[$thisStudentCallSign]['country'];
					$thisStatus				= $studentDataArray[$thisStudentCallSign]['status'];
					$thisFirstClassChoice	= $studentDataArray[$thisStudentCallSign]['first class choice'];
					$thisSecondClassChoice	= $studentDataArray[$thisStudentCallSign]['second class choice'];
					$thisThirdClassChoice	= $studentDataArray[$thisStudentCallSign]['third class choice'];
					$thisFirstClassChoiceL	= $studentDataArray[$thisStudentCallSign]['first class choice local'];
					$thisSecondClassChoiceL	= $studentDataArray[$thisStudentCallSign]['second class choice local'];
					$thisThirdClassChoiceL	= $studentDataArray[$thisStudentCallSign]['third class choice local'];
					$thisExcludedAdvisor	= $studentDataArray[$thisStudentCallSign]['excluded advisor'];
					$thisAssignedAdvisor	= $studentDataArray[$thisStudentCallSign]['assigned advisor'];
					$thisAssignedAdvisorClass	= $studentDataArray[$thisStudentCallSign]['assigned advisor class'];
							

					//// get the advisor's class information
					$sql					= "select * from $advisorClassTableName 
													where advisor_call_sign='$thisAssignedAdvisor' 
														and sequence=$thisAssignedAdvisorClass";
					$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						$myError			= $wpdb->last_error;
						$myQuery			= $wpdb->last_query;
						if ($doDebug) {
							echo "Reading $advisorClassTableName table failed<br />
								  wpdb->last_query: $myQuery<br />
								  wpdb->last_error: $myError<br />";
						}
						$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
						sendErrorEmail($errorMsg);
					} else {
						$numACRows						= $wpdb->num_rows;
						if ($doDebug) {
							$myStr						= $wpdb->last_query;
							echo "ran $myStr<br />and found $numACRows rows<br />";
						}
						if ($numACRows > 0) {
							foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
								$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
								$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
								$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
								$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
								$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
								$advisorClass_sequence 					= $advisorClassRow->sequence;
								$advisorClass_semester 					= $advisorClassRow->semester;
								$advisorClass_timezone 					= $advisorClassRow->time_zone;
								$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
								$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
								$advisorClass_level 					= $advisorClassRow->level;
								$advisorClass_class_size 				= $advisorClassRow->class_size;
								$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
								$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
								$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
								$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
								$advisorClass_action_log 				= $advisorClassRow->action_log;
								$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
								$advisorClass_date_created				= $advisorClassRow->date_created;
								$advisorClass_date_updated				= $advisorClassRow->date_updated;

								$result						= utcConvert('tolocal',$thisTimeZone,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc);
								if ($result[0] == 'FAIL') {
									if ($doDebug) {
										echo "utcConvert failed 'tolocal',$timezone,$UTCTime,$UTCDays<br />
												Error: $result[3]<br />";
									}
									$errorArray[]			= "Student $thisStudentCallSign has arbitrary assignment to $thisAssignedAdvisor class $thisAssignedAdvisorClass where class times do not convert to local<br />";
									$displayDays			= "<b>ERROR</b>";
									$displayTimes			= '';
								} else {
									$displayTimes			= $result[1];
									$displayDays			= $result[2];
								}
							}

						} else {
							$errorArray[]			= "Student $thisStudentCallSign has arbitrary assignment to $thisAssignedAdvisor class $thisAssignedAdvisorClass which does not exist<br />";
							$displayDays			= "<b>ERROR</b>";
							$displayTimes			= '';
						}
					}	
					$theLink	= "<a href='$studentHistoryURL?strpass=2&inp_student=$thisStudentCallSign' target='_blank'>$thisLastName, $thisFirstName ($thisStudentCallSign)</a>";
										
					$content				.= "<tr><td style='vertical-align:top;'>$theLink</td>
													<td style='vertical-align:top;'>$thisLastName, $thisFirstName</td>
													<td style='vertical-align:top;'>$thisEmail</td>
													<td style='vertical-align:top;'>$thisPhone ($thisMessaging)</td>
													<td style='vertical-align:top;'>$thisLevel</td>
													<td style='vertical-align:top;'>$thisCountry</td>
													<td style='vertical-align:top;'>$thisTimeZone</td>
													<td style='vertical-align:top;'>$thisFirstClassChoiceL<br />$thisSecondClassChoiceL<br />$thisThirdClassChoiceL</td>
													<td style='vertical-align:top;'>$thisAssignedAdvisor-$thisAssignedAdvisorClass</td>
													<td style='vertical-align:top;'>$displayTimes $displayDays</td></tr>";
				}
				$content		.= "</table>
									<p>$arbitraryCount Arbitrarily Assigned Students<br />
									<em>Clicking on the Student Call Sign link will open the student's detailed history in a new tab</em></p>";
			} else {
				$content	.= "<tr><td colspan='10'>No Arbitrarily Assigned Students</td></tr></table>";
			}
			
//////// end of Arbitrarily Assigned Student Information Report


	
//////// Start of the C&R Report


			if ($doDebug) {
				echo "<br >Start of the C&R Report<br />";
			}
			// Prepare report of C and R Students
			$cCount			= 0;
			$rCouht			= 0;
			$content		.= "<a name='reportX'><h3>Students with a Status of C, R, or V</h3></a>
								<table style='width:900px;'><tr>
								<th>Name</th>
								<th>Email</th>
								<th>Phone</th>
								<th>State</th>
								<th>TZ</th>
								<th>Level</th>
								<th>Status</th>
								<th>Former<br />Advisor</th></tr>";
			$thisCount						= count($CRArray);
			if ($thisCount > 0) {
				sort($CRArray);
				$rCount						= 0;
				$cCount						= 0;
				$vCount						= 0;
				foreach($CRArray as $myValue) {
					$myArray			 	= explode("|",$myValue);
					$thisName				= $myArray[1];
					$thisEmail				= $myArray[2];
					$thisPhone				= $myArray[3];
					$thisState				= $myArray[4];
					$thisTimeZone			= $myArray[5];
					$thisLevel				= $myArray[0];
					$thisStatus				= $myArray[6];	
					$thisAdvisor			= $myArray[7]; 
					
					if ($thisStatus == 'C') {
						$cCount++;
					} elseif ($thisStatus == 'R') {
						$rCount++;
					} else {
						$vCount++;
					}
					$content				.= "<tr><td>$thisName</td>
													<td>$thisEmail</td>
													<td>$thisPhone</td>
													<td>$thisState</td>
													<td>$thisTimeZone</td>
													<td>$thisLevel</td>
													<td style='text-align:center;'>$thisStatus</td>
													<td>$thisAdvisor</td></tr>";
				}
				$content					.= "</table>
												<p>$cCount Total students with status of C (Student has been replaced)<br />
												   $rCount Total students with status of R (Student to be replaced)<br />
												   $vCount Total students with status of V (Student to be replaced and put in the unassigned pool)<br />
												   $thisCount Total students with C, R, or V status</p>";
   				} else {
   					$content					.= "
<tr><td colspan='8'>No Entries</td></tr></table>";
   				}
		
//////// End of the C&R Report




///////// 	Start of display all advisor slots
			// slotsAvailableArray[] 	= thisLevel|thisLastName, thisFirstName|thisCallSign|thisTimeZone|classSequence|thisTime thisDays|thisTimel thisDaysl|thisSlotsAvail|thisSize";

			if ($doDebug) {
				echo "<br />Start of display all advisor slots report<br />";
			}
			$content			.= "<a name='reportBB'><h3>All Advisors and Class Slots</h3></a>
									<table>
									<tr><th>Name</th>
										<th>TZ</th>
										<th>Level</th>
										<th>Sequence</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";
			$thisCount			= 0;
			$totalSlots			= 0;
			if (count($slotsAvailableArray) > 0) {
				sort($slotsAvailableArray);
				foreach($slotsAvailableArray as $myValue) {
					$myArray		= explode("|",$myValue);
					$thisName		= $myArray[1];
					$thisCallSign	= $myArray[2];
					$thisTZ			= $myArray[3];
					$thisLevel		= $myArray[0];
					$thisSeq		= $myArray[4];
					$thisTime		= $myArray[5];
					$thisTimel		= $myArray[6];
					$thisNmbr		= $myArray[7];
					$thisSize		= $myArray[8];

					$thisConvert	= $levelBack[$thisLevel];

					if ($thisNmbr > 0) {
						$totalSlots	= $totalSlots + $thisNmbr;
					}
					$thisMode		= 'Production';
					if ($testMode) {
						$thisMode	= 'TESTMODE';
					}
					$theLink	= "<a href='$studentManagementURL?strpass=81&inp_advisor_callsign=$thisCallSign&inp_advisorClass=$thisSeq&inp_search=standard&inp_mode=$thisMode' target='_blank'>$thisName ($thisCallSign)</a>";
					$content	.= "<tr><td>$theLink</td>
										<td style='vertical-align:top;'>$thisTZ</td>
										<td style='vertical-align:top;'>$thisConvert</td> 
										<td style='vertical-align:top;'>$thisSeq</td>
										<td style='vertical-align:top;'>$thisTime UTC<br />$thisTimel Local</td>
										<td style='text-align:center;vertical-align:top;'>$thisSize</td>
										<td style='text-align:center;vertical-align:top;'>$thisNmbr</td></tr>";
					$thisCount++;
				}
				$content			.= "</table>
										<p>$thisCount Number of advisors<br />
										   $totalSlots Total slots available</p>";
			} else {
				$content			.= "<tr><td colspan='6'>No Entries</td></tr></table>";
			}
///////// 	End of display available advisor slots



/////////		Display advisors with small class sizes

			// advisorSmallClass[]	= thisLastName, thisFirstName (thisCallSign)|thisTimeZone|thisLevel|classSequence|thisTime thisDays|thisTimel thisDaysl|thisNumberStudents|thisSize

			if ($doDebug) {
				echo "<br />Start of advisors with small class sizes report<br />";
			}	
			$content			.= "<a name='reportS'><h3>Advisor with Small Classes</h3></a>
									<p><table>
									<tr><th>Name</th>
										<th>TZ</th>
										<th>Level</th>
										<th>Sequence</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";
			$thisCount			= count($advisorSmallClass);
			if ($thisCount > 0) {
				sort($advisorSmallClass);
				foreach($advisorSmallClass as $myValue) {
					$myArray	= explode("|",$myValue);
					$thisName	= $myArray[0];
					$thisTZ		= $myArray[1];
					$thisLevel	= $myArray[2];
					$thisSeq	= $myArray[3];
					$thisTime	= $myArray[4];
					$thisTimel	= $myArray[5];
					$thisNmbr	= $myArray[6];
					$thisSize	= $myArray[7];
					
					$thisInt	= $thisSize - $thisNmbr;
					
					$content	.= "<tr><td style='vertical-align:top;'>$thisName</td>
										<td style='vertical-align:top;'>$thisTZ</td>
										<td style='vertical-align:top;'>$thisLevel</td> 
										<td style='vertical-align:top;text-align:center;'>$thisSeq</td>
										<td style='vertical-align:top;'>$thisTime UTC<br />$thisTimel Local</td>
										<td style='text-align:center;vertical-align:top;'>$thisSize</td>
										<td style='text-align:center;vertical-align:top;'>$thisInt</td></tr>";
				}
				$content			.= "</table>
										<p>$thisCount Small Advisor Classes</p>";
			} else {
				$content			.= "<tr><td colspan='6'>No Entries</td></tr></table>";
			}


/////////		End of Display advisors with small class sizes


///////// 	Start of display available advisor slots
// slotsAvailableArray[] 	= thisLevel|thisLastName, thisFirstName|thisCallSign|thisTimeZone|classSequence|thisTime thisDays|thisTimel thisDaysl|thisSlotsAvail|thisSize";

			$content			.= "<a name='reportY'><h3>Advisor Class Slots Available</h3></a>
									<table>
									<tr><th>Name</th>
										<th>TZ</th>
										<th>Level</th>
										<th>Sequence</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";
			$thisCount			= 0;
			$totalSlots			= 0;
			if (count($slotsAvailableArray) > 0) {
				sort($slotsAvailableArray);
				foreach($slotsAvailableArray as $myValue) {
					$myArray		= explode("|",$myValue);
					$thisName		= $myArray[1];
					$thisCallSign	= $myArray[2];
					$thisTZ			= $myArray[3];
					$thisLevel		= $myArray[0];
					$thisSeq		= $myArray[4];
					$thisTime		= $myArray[5];
					$thisTimel		= $myArray[6];
					$thisNmbr		= $myArray[7];
					$thisSize		= $myArray[8];

					$thisConvert	= $levelBack[$thisLevel];
					if ($thisNmbr > 0) {
						$totalSlots	= $totalSlots + $thisNmbr;
						$thisMode		= 'Production';
						if ($testMode) {
							$thisMode	= 'TESTMODE';
						}
						$theLink	= "<a href='$studentManagementURL?strpass=81&inp_advisor_callsign=$thisCallSign&inp_advisorClass=$thisSeq&inp_search=standard&inp_mode=$thisMode' target='_blank'>$thisName ($thisCallSign)</a>";
						$content	.= "<tr><td>$theLink</td>
											<td style='vertical-align:top;'>$thisTZ</td>
											<td style='vertical-align:top;'>$thisConvert</td> 
											<td style='vertical-align:top;'>$thisSeq</td>
											<td style='vertical-align:top;'>$thisTime UTC<br />$thisTimel Local</td>
											<td style='text-align:center;vertical-align:top;'>$thisSize</td>
											<td style='text-align:center;vertical-align:top;'>$thisNmbr</td></tr>";
						$thisCount++;
					}
				}
				$content			.= "</table>
										<p>$thisCount Number of advisors with slots available<br />
										   $totalSlots Total slots available</p>";
			} else {
				$content			.= "<tr><td colspan='6'>No Entries</td></tr></table>";
			}
///////// 	End of display available advisor slots



			
//////// Start of Unassigned Student Information Report				

			if ($doDebug) {
				echo "<br />Start of Unassigned Student Information Report <br />";
			}

			$content		.= "<a name='report3'><h3>Unassigned Student Information for $inp_semester</h3></a>";
			$namesArray		= array('unassignedBeginnerArray'=>'Beginner',
									'unassignedFundamentalArray'=>'Fundamental',
									'unassignedIntermediateArray'=>'Intermediate',
									'unassignedAdvancedArray'=>'Advanced');
			$totalUnassigned			= 0;
			foreach($namesArray as $unassignedName=>$displayName) {
				$thisUnassigned			= 0;
				$content	.= "<h4>Unassigned $displayName Students</h4>
								<p><table style='width:900px;'><tr>
								<tr><th>Call Sign<br />Lookup</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Request Date</th>
									<th>State</th>
									<th>Country</th>
									<th>Excl Advisor</th></tr>";
				if (count(${$unassignedName}) > 0) {
					sort(${$unassignedName});
					foreach(${$unassignedName} as $myValue) {
						$myArray				= explode("|",$myValue);
						$thisClassPriority		= intval($myArray[0]);
						$unassignedCallSign		= $myArray[2];
						$thisFirstName			= $studentDataArray[$unassignedCallSign]['first name'];
						$thisLastName			= $studentDataArray[$unassignedCallSign]['last name'];
						$thisTimeZone			= $studentDataArray[$unassignedCallSign]['time zone'];
						$thisLevel				= $studentDataArray[$unassignedCallSign]['level'];
						$thisEmail				= $studentDataArray[$unassignedCallSign]['email'];
						$thisPhone				= $studentDataArray[$unassignedCallSign]['phone'];
						$thisMessaging			= $studentDataArray[$unassignedCallSign]['messaging'];
						$thisState				= $studentDataArray[$unassignedCallSign]['state'];
						$thisCountry			= $studentDataArray[$unassignedCallSign]['country'];
						$thisStatus				= $studentDataArray[$unassignedCallSign]['status'];
						$thisFirstClassChoice	= $studentDataArray[$unassignedCallSign]['first class choice'];
						$thisSecondClassChoice	= $studentDataArray[$unassignedCallSign]['second class choice'];
						$thisThirdClassChoice	= $studentDataArray[$unassignedCallSign]['third class choice'];
						$thisFirstClassChoiceL	= $studentDataArray[$unassignedCallSign]['first class choice local'];
						$thisSecondClassChoiceL	= $studentDataArray[$unassignedCallSign]['second class choice local'];
						$thisThirdClassChoiceL	= $studentDataArray[$unassignedCallSign]['third class choice local'];
						$thisExcludedAdvisor	= $studentDataArray[$unassignedCallSign]['excluded advisor'];
						$thisRequestDate		= $studentDataArray[$unassignedCallSign]['request_date'];
						
						$thisClassPriority		= 3 - $thisClassPriority;
						
						$thisUnassigned++;
						$totalUnassigned++;
						
						
						if ($thisFirstClassChoiceL != '' && $thisFirstClassChoiceL != 'None') {
							$myArray		 	= explode(" ",$thisFirstClassChoiceL);
							$myStr				= $myArray[0];
							$myDays			 	= $myArray[1];
							$myTimes			= $timeConvertArray[$myStr];
							$thisFirstClassChoiceL	= "$myTimes $myDays";
						} else {
							$thisFirstClassChoiceL	= 'None';
						}
						if ($thisSecondClassChoiceL != '' && $thisSecondClassChoiceL != 'None') {
							$myArray		 	= explode(" ",$thisSecondClassChoiceL);
							$myStr				= $myArray[0];
							$myDays			 	= $myArray[1];
							$myTimes			= $timeConvertArray[$myStr];
							$thisSecondClassChoiceL	= "$myTimes $myDays";
						} else {
							$thisSecondClassChoiceL	= 'None';
						}
						if ($thisThirdClassChoiceL != '' && $thisThirdClassChoiceL != 'None') {
							$myArray		 	= explode(" ",$thisThirdClassChoiceL);
							$myStr				= $myArray[0];
							$myDays			 	= $myArray[1];
							$myTimes			= $timeConvertArray[$myStr];
							$thisThirdClassChoiceL	= "$myTimes $myDays";
						} else {
							$thisThirdClassChoiceL	= 'None';
						}
						
						///// has the student taken this level before and was promoted?
						reset($pastSemesterArray);
						$gotStudentMatch			= FALSE;
						$wasPromoted				= FALSE;
						$pastSemesterArray			= array_reverse($pastSemesterArray);
						foreach($pastSemesterArray as $pastStudentValue) {
							if (!$gotStudentMatch) {
								///// get each past semester and see if student was enrolled at the same level
								$sql					= "select * from $studentTableName where semester='$pastStudentValue' and call_sign='$unassignedCallSign'";
								$wpw1_cwa_student	= $wpdb->get_results($sql);
								if ($wpw1_cwa_student === FALSE) {
									if ($doDebug) {
										echo "Reading $tudentTableName table failed<br />";
										echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
										echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
									}
								} else {
									$numPSRows									= $wpdb->num_rows;
									if ($doDebug) {
										$myStr			= $wpdb->last_query;
										echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
									}
									if ($numPSRows > 0) {
										foreach ($wpw1_cwa_student as $studentRow) {
											$student_ID							= $studentRow->student_id;
											$student_call_sign						= strtoupper($studentRow->call_sign);
											$student_first_name					= $studentRow->first_name;
											$student_last_name						= stripslashes($studentRow->last_name);
											$student_email  						= strtolower(strtolower($studentRow->email));
											$student_ph_code						= $studentRow->ph_code;
											$student_phone  						= $studentRow->phone;
											$student_city  						= $studentRow->city;
											$student_state  						= $studentRow->state;
											$student_zip_code  					= $studentRow->zip_code;
											$student_country_code					= $studentRow->country_code;
											$student_country  						= $studentRow->country;
											$student_time_zone  					= $studentRow->time_zone;
											$student_timezone_id					= $studentRow->timezone_id;
											$student_timezone_offset				= $studentRow->timezone_offset;
											$student_whatsapp						= $studentRow->whatsapp_app;
											$student_signal						= $studentRow->signal_app;
											$student_telegram						= $studentRow->telegram_app;
											$student_messenger						= $studentRow->messenger_app;					
											$student_wpm 	 						= $studentRow->wpm;
											$student_youth  						= $studentRow->youth;
											$student_age  							= $studentRow->age;
											$student_student_parent 				= $studentRow->student_parent;
											$student_student_parent_email  		= strtolower($studentRow->student_parent_email);
											$student_level  						= $studentRow->level;
											$student_waiting_list 					= $studentRow->waiting_list;
											$student_request_date  				= $studentRow->request_date;
											$student_semester						= $studentRow->semester;
											$student_notes  						= $studentRow->notes;
											$student_welcome_date  				= $studentRow->welcome_date;
											$student_email_sent_date  				= $studentRow->email_sent_date;
											$student_email_number  				= $studentRow->email_number;
											$student_response  					= strtoupper($studentRow->response);
											$student_response_date  				= $studentRow->response_date;
											$student_abandoned  					= $studentRow->abandoned;
											$student_student_status  				= strtoupper($studentRow->student_status);
											$student_action_log  					= $studentRow->action_log;
											$student_pre_assigned_advisor  		= $studentRow->pre_assigned_advisor;
											$student_selected_date  				= $studentRow->selected_date;
											$student_no_catalog		  			= $studentRow->no_catalog;
											$student_hold_override  				= $studentRow->hold_override;
											$student_messaging  					= $studentRow->messaging;
											$student_assigned_advisor  			= $studentRow->assigned_advisor;
											$student_advisor_select_date  			= $studentRow->advisor_select_date;
											$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
											$student_hold_reason_code  			= $studentRow->hold_reason_code;
											$student_class_priority  				= $studentRow->class_priority;
											$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
											$student_promotable  					= $studentRow->promotable;
											$student_excluded_advisor  			= $studentRow->excluded_advisor;
											$student_student_survey_completion_date = $studentRow->student_survey_completion_date;
											$student_available_class_days  		= $studentRow->available_class_days;
											$student_intervention_required  		= $studentRow->intervention_required;
											$student_copy_control  				= $studentRow->copy_control;
											$student_first_class_choice  			= $studentRow->first_class_choice;
											$student_second_class_choice  			= $studentRow->second_class_choice;
											$student_third_class_choice  			= $studentRow->third_class_choice;
											$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
											$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
											$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
											$student_date_created 					= $studentRow->date_created;
											$student_date_updated			  		= $studentRow->date_updated;
										
											$gotStudentMatch							= TRUE;
											if ($student_level == $thisLevel) {		/// have a level match
												if ($student_promotable == 'P') {		/// must be promotable
													$wasPromoted						= TRUE;
													if ($doDebug) {
														echo "Found student in studentTableName table having already been promoted<br />";
													}
												}
											}
										}
									}
								}
							}
						}
						$promotedStr			= "";
						if ($wasPromoted) {
							$promotedStr		= "<br /><em>Student has previously taken this level before and was promoted.</em>";
						}
						
						$theLink	= "<a href='$studentManagementURL?strpass=70&inp_student_callsign=$unassignedCallSign&inp_mode=$inp_mode' target='_blank'>$thisLastName, $thisFirstName ($unassignedCallSign)</a>";
						$content				.= "<tr><td style='vertical-align:top;'>$theLink</td>
														<td style='vertical-align:top;'>$thisEmail<br />$thisFirstClassChoice</td>
														<td style='vertical-align:top;'>$thisPhone ($thisMessaging)<br />$thisSecondClassChoice</td>
														<td style='vertical-align:top;'>$thisRequestDate</td>
														<td style='vertical-align:top;'>$thisState<br />$thisThirdClassChoice</td>
														<td style='vertical-align:top;'>$thisCountry</td>
														<td style='vertical-align:top;'>$thisExcludedAdvisor</td></tr>";
						if ($promotedStr != "") {
							$content			.= "<tr><td colspan='6'>$promotedStr</td></tr>";
						}
					}
				} else {
					$content	.= "<tr><td colspan='6'>No Unassigned Students in this Category</td></tr>";
				}
				$content		.= "</table>
									<p>$thisUnassigned Total $displayName unassigned students<br />
									<em>Clicking on the student name link will open the 'Find Possible Classes for a Student' function in a new tab</em></p>";
			}
			$content			.= "<p>$totalUnassigned Total unassigned at all levels</p><br />";
			
//////// End of Unassigned Student Information Report				
	
	


////////////	 students on hold report
			if ($doDebug) {
				echo "<br />Doing Students On Hold Report<br />";
			}
			//	$holdArray		student_call_sign student_last_name, student_first_name reason
			$content		.= "<a name='reportH'><h3>Students On Hold Report</h3></a>";
			$holdCount			= 0;
			sort($holdArray);
			foreach($holdArray as $myValue) {
				$holdCount++;
				$content				.= "$myValue";
			}
			$content			.= "<br /><p>$holdCount Students on hold<br />
<em>Clicking on the student call sign will open the Display and Update Student function</em></p>";

//////////////	end of students on hold report




//////////////	error report
			if ($doDebug) {
				echo "<br >Doing the error Report<br />";
			}
			$content		.= "<a name='reportE'><h3>Errors Report</h3></a>
								<table>
								<tr><th>Error</th></tr>";
			if (count($errorArray) > 0) {
				foreach($errorArray as $myValue) {
					$content	.= "<tr><td>$myValue</td></tr>";
				}
			} else {
				$content		.= "<tr><td>No errors noted</td></tr>";
			}
			$content		.= "</table>";

//////////////	end of error report




		} else {
			if ($doDebug) {
				echo "continueProgram was set to FALSE during student processing. No output produced<br />";
			}
		}
	}

	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>V$thisVersion Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("jobname|$nowDate|$nowTime|$userName|$thisStr|Time|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as $jobname<br />";
		}
		$storeResult	= storeReportData_func($jobname,$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}
	return $content;

}
add_shortcode ('student_and_advisor_assignments_v2', 'student_and_advisor_assignments_v2_func');
