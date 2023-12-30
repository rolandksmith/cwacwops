function daily_catalog_cron_process_v3_func() {

/*		Daily Catalog Cron				being updated

   This job is run via a cron curl job to run the associated webpage

	The catalog table contains the advisor classes for each semester
	
	The program first looks at the semester list in data initialization
		If a record does not exist for any of the future semesters, it creates 
			a standard catalog record for that semester for both Production and TestMode
			
	If the current semester is 'Not in Session' and 47 days or less until the next 
		semester starts, the program reads the advisor classes for the next semester
		and builds a catalog
		
	If the current semester is in session no further action is taken


	advisorArray: 	advisor callsign
	classesArray: 	[level|time ITC|days] = number of classes
	advisorClasses:	[level|time UTC|days][advisorClassInc] = advisor_call_sign-advisorClass_sequence

	Catalog format:
		level|time UTC|days|number of classes|advisor-sequence ....

	If the catalog is to be generated
		Read the advisor/advisorClass records for the upcoming semester
		

	This job generates the class catalog
	which is stored in a table wpw1_cwa_current_catalog
	
	
	created from the big daily cron job on 6July2021 by Roland
	Modified 31Aug2021 by Roland to use 2-hour blocks instead of 3-hour blocks
		version moved to V2
	Modified 27Oct2021 by Roland to use the logic for which data will be in the catalog 
		as specified above
	Modified 4Jan2022 by Roland to use the new table files rather than pods
	Modified 19Feb2022 to do the comparison of the old catalog to the new catalog
	Modified 19Apr2022 to use a database table rather than a flat file
 	Modified 1Jun2022 by Roland to put classes in a one-hour time block
 	
 	Modified 23Aug22 by Roland to the new catalog format and include the standard catalog
 		version set to v3
 	Modified 15Apr23 by Roland to fix action_log
 	Modified 15May23 by Roland to show abbreviated catalog
 	Modified 12May23 by Roland to use consolidated tables
 	Modified 19NOv23 by Roland for new portal process
*/


	global $wpdb, $testMode, $doDebug, $printArray;

	$doDebug				= FALSE;
	$testMode				= FALSE;
	$fakeMode				= FALSE;
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
//	$testEmailTo			= "kcgator@gmail.com,rolandksmith@gmail.com";
	$testEmailTo			= "rolandksmith@gmail.com";

	ini_set('max_execution_time',360);

	$initializationArray 	= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName				= $initializationArray['userName'];
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');


// Needed variables initialization
	$currentSemester		= $initializationArray['currentSemester'];
	$prevSemester			= $initializationArray['prevSemester'];
	$nextSemester			= $initializationArray['nextSemester'];
	$semesterTwo			= $initializationArray['semesterTwo'];
	$semesterThree			= $initializationArray['semesterThree'];
	$semesterFour			= $initializationArray['semesterFour'];
	$siteURL				= $initializationArray['siteurl'];
	$theSemester			= $initializationArray['currentSemester'];
	if ($currentSemester == 'Not in Session') {
		$theSemester		= $nextSemester;
	}
	$replacementPeriod		= $initializationArray['validReplacementPeriod'];
	$validReplacementPeriod	= FALSE;
	if ($replacementPeriod == 'Y') {
		 $validReplacementPeriod = TRUE;
	}
	$jobname				= 'Daily Catalog Cron V3';
	$catalogReport			= '';
	$errorArray				= array();
	$recordsProcessed		= 0;
	$advisorArray			= array();
	$classesArray			= array();
	$advisorClassInc		= 0;
	$strPass				= "0";
	$semesterArray			= array();
	$oldCatalogArray		= array();
	$newCatalogArray		= array();
	$additionsArray			= array();
	$deletionsArray			= array();
	$changesArray			= array();
	
	$standardCatalog		= "Advanced|0030|Tuesday,Friday|1|&Advanced|0200|Tuesday,Friday|1|&Advanced|0300|Tuesday,Friday|1|&Advanced|1800|Monday,Thursday|1|&Advanced|1800|Tuesday,Friday|1|&Advanced|1900|Monday,Thursday|1|&Advanced|2330|Monday,Thursday|1|&Beginner|0000|Tuesday,Friday|1|&Beginner|0030|Tuesday,Friday|1|&Beginner|0100|Tuesday,Friday|1|&Beginner|0100|Wednesday,Saturday|1|&Beginner|0130|Tuesday,Friday|1|&Beginner|0200|Tuesday,Friday|1|&Beginner|0300|Tuesday,Friday|1|&Beginner|1400|Monday,Thursday|1|&Beginner|1800|Monday,Thursday|1|&Beginner|1900|Monday,Thursday|1|&Beginner|1930|Monday,Thursday|1|&Beginner|2200|Monday,Thursday|1|&Beginner|2300|Monday,Thursday|1|&Beginner|2300|Sunday,Wednesday|1|&Fundamental|0000|Tuesday,Friday|1|&Fundamental|0030|Tuesday,Friday|1|&Fundamental|0100|Tuesday,Friday|1|&Fundamental|0200|Tuesday,Friday|1|&Fundamental|0200|Wednesday,Saturday|1|&Fundamental|0300|Tuesday,Friday|1|&Fundamental|0400|Tuesday,Friday|1|&Fundamental|1500|Tuesday,Friday|1|&Fundamental|1600|Tuesday,Friday|1|&Fundamental|1800|Tuesday,Friday|1|&Fundamental|1900|Monday,Thursday|1|&Intermediate|0000|Tuesday,Friday|1|&Intermediate|0030|Tuesday,Friday|1|&Intermediate|0100|Tuesday,Friday|1|&Intermediate|0130|Tuesday,Friday|1|&Intermediate|0230|Tuesday,Friday|1|&Intermediate|0400|Tuesday,Friday|1|&Intermediate|1700|Monday,Thursday|1|&Intermediate|1700|Tuesday,Friday|1|&Intermediate|1800|Monday,Thursday|1|&Intermediate|1900|Monday,Thursday|1|&Intermediate|2030|Monday,Thursday|1|&Intermediate|2300|Monday,Thursday|1|";
		
	if ($fakeMode) {			/// fudge initializationArray
		$currentSemester 	= 'Not in Session';
		$daysToSemester 	= 45;
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
		
	$runTheJob				= TRUE;
		
	if ($userName != '') {
		$content 			.= "<h3>Daily Catalog Cron V3 Process Executed by $userName</h3>";
	} else {
		$content			.= "<h3>Daily Catalog Cron V3 Process Automatically Executed</h3>";
		$userName			= "CRON";
		$dst				= date('I');
		if ($dst == 0) {
			$checkBegin 	= strtotime('13:50:00');
			$checkEnd 		= strtotime('14:30:00');
			$thisTime 		= date('H:i:s');
		
		} else {
			$checkBegin 	= strtotime('12:50:00');
			$checkEnd 		= strtotime('13:30:00');
			$thisTime 		= date('H:i:s');
		}
		$nowTime 			= strtotime($thisTime);
		if ($nowTime >= $checkBegin && $nowTime <= $checkEnd) {
			$runTheJob 		= TRUE;
		} else {
			$runTheJob 		= FALSE;
			$userName		= "CRON Abort";
			if ($doDebug) {
				echo "runTheJob is FALSE<br />";
			}
//			$theRecipient	= '';
//			$theSubject		= 'CW Academy - Cron Triggered';
//			$theContent		= "The Catalog Cron was triggered at $thisTime. It did not run.";
//			$mailCode		= 16;
//			$result			= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
//												 'theSubject'=>$theSubject,
//												 'jobname'=>$jobname,
//												 'theContent'=>$theContent,
//												 'mailCode'=>$mailCode,
//												 'testMode'=>$testMode,
//												 'doDebug'=>$doDebug));
		}
	}
	if ($runTheJob) {
	
		if ($testMode) {
			$content .= "<p><strong>Function is under development.</strong></p>";
			$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
			$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
			$catalogTableName			= 'wpw1_cwa_current_catalog';
			$mode						= 'TestMode';
		} else {
			$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
			$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
			$catalogTableName			= 'wpw1_cwa_current_catalog';
			$mode						= 'Production';
		}

		//// first put out any standard catalogs needed
		if ($doDebug) {
			echo "<br />putting out any standard catalogs needed<br />";
		}
		$standardSemesterOrder			= array('nextSemester','semesterTwo','semesterThree','semesterFour');
		foreach($standardSemesterOrder as $thisSemesterName) {
			$thisSemester				= ${$thisSemesterName};
			$sql						= "select * from $catalogTableName where semester='$thisSemester' and mode='$mode'";
			$cwa_catalog				= $wpdb->get_results($sql);
			if ($cwa_catalog === FALSE) {
				$errorArray[]			= "unable to find $catalogTableName table to read the catalog<br />";
				$myError			= $wpdb->last_error;
				$myQuery			= $wpdb->last_query;
				if ($doDebug) {
					echo "Reading $catalogTableName table failed<br />
						  wpdb->last_query: $myQuery<br />
						  wpdb->last_error: $myError<br />";
				}
				$errorMsg			= "Daily Student Cron (A) Reading $catalogTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
				sendErrorEmail($errorMsg);
			} else {
				$numRows				= $wpdb->num_rows;
				if ($doDebug) {
					$myStr				= $wpdb->last_query;
					echo "ran $myStr<br />and retrieved $numRows rows<br />";
				}
				if ($numRows == 0) {			/// no catalog entry found insert standard catalog
					if ($doDebug) {
						echo "no catalog found for $thisSemester semester in mode $mode<br />
							   Writing a catalog entry<br />";
					}
					$catalogResult		= $wpdb->insert($catalogTableName,
														array('semester'=>$thisSemester,
															  'mode'=>$mode,
															  'catalog'=>$standardCatalog),
														 array('%s','%s','%s'));

					if ($catalogResult === FALSE) {
						$content				.= "<br />ERROR Inserting new catalog into $catalogTableName failed<br />";
						$myError			= $wpdb->last_error;
						$myQuery			= $wpdb->last_query;
						if ($doDebug) {
							echo "Reading Inserting standard catalog into $catalogTableName failed<br />
								  wpdb->last_query: $myQuery<br />
								  wpdb->last_error: $myError<br />";
						}
						$errorMsg			= "Daily Student Cron (B) Inserting standard catalog into $catalogTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
						sendErrorEmail($errorMsg);
					}
				
				}
			}
		}
		/////////// get the old catalog for comparison purposes
		if ($doDebug) {
			echo "<br />Getting the old catalog for comparison purposes<br />";
		}
		$gotOldCatalog				= FALSE;
		$sql 						= "select * from $catalogTableName 
										where semester='$nextSemester' 
										and mode='$mode'
										order by date_created DESC 
										limit 1";
		$result						= $wpdb->get_results($sql);
		if ($result === FALSE) {
			$errorArray[]			= "unable to find $catalogTableName table to read the catalog<br />";
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $catalogTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "Daily Student Cron (C) Reading $catalogTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numRows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numRows records from $catalogTableName<br />";
			}
			if ($numRows > 0) {
				foreach ($result as $catalogRow) {
					$record_id		= $catalogRow->record_id;
					$oldCatalog		= $catalogRow->catalog;
					$gotOldCatalog	= TRUE;
					
					if ($doDebug) {
						echo "got a catalog for $thisSemester in mode $mode<br />";
					}
				}
			} else {
				$errorArray[]		= "No previous catalog records found in $catalogTableName table<br />";
				if ($doDebug) {
					echo "No previous catalog records found in $catalogTableName table<br />";
				}
			}
		}

		if ($gotOldCatalog) {
			if ($doDebug) {
				echo "<br />Have an old catalog record:<br />";
			}
			$thisArray						= explode("&",$oldCatalog);
			$myInt							= count($thisArray);
			if ($doDebug) {
				echo "Exploded the oldCatalog and got $myInt entries<br /><pre>";
				print_r($thisArray);
				echo "</pre><br />";
			}
			/// prepare catalogReport for the standard catalog
			$catalogReport					= "<h3>Abbreviated Catalog for $nextSemester</h3>";
			sort($thisArray);
			foreach($thisArray as $thisValue) {
				$catalogReport				.= "$thisValue<br />";
			}
			/// decide if a new catalog should be generated
			$doCatalog 						= FALSE;
			$daysToSemester					= days_to_semester($nextSemester);
			if ($doDebug) {
				echo "<br />daysToSemester: $daysToSemester<br />";
			}
			if ($currentSemester == 'Not in Session' && $daysToSemester > 0 && $daysToSemester <= 52) {
				$doCatalog					= TRUE;
			}
			if ($currentSemester != 'Not in Session' && $validReplacementPeriod) {
				$doCatalog					= TRUE;
			}
			if ($doCatalog) {
				if ($doDebug) {
					echo "doCatalog is TRUE. Generating the Catalog<br />
						  First build the oldCatalogArray<br />";
				}
				foreach($thisArray as $buffer) {
//					if ($doDebug) {
//						echo "buffer: $buffer<br />";
//					}	
					$myArray				= explode("|",$buffer);
					$myInt1					= count($myArray);
					if ($doDebug) {
						echo "Exploded an entry in buffer and got $myInt1 entries<br />";
					}
					if ($myInt1 > 1) {
						$thisLevel			= $myArray[0];
						$thisTimeCode		= $myArray[1];
						$thisDaysCode		= $myArray[2];
						$thisCount			= $myArray[3];
						$thisAdvisors		= $myArray[4];
		
						$oldArrayKey		= "$thisLevel|$thisTimeCode|$thisDaysCode";
						$oldArrayValue		= "$thisCount|$thisAdvisors";	
						$oldCatalogArray[$oldArrayKey]	= $oldArrayValue;	
					}
				}
				if ($doDebug) {
					echo "oldCatalogArray built<br /><br />
						  Now get the advisors and their classes<br />";
				}

	
				//////////	Build the arrays

				/// get each advisor and associated class record for that advisor
				$sql				= "select 
									   a.call_sign,
									   a.survey_score,
									   a.verify_response,
									   b.sequence,
									   b.level,
									   b.class_incomplete,
									   b.class_schedule_days_utc,
									   b.class_schedule_times_utc
									   from $advisorTableName as a 
									   join $advisorClassTableName as b 
									   where a.semester='$theSemester' 
									   and a.semester = b.semester 
									   and a.call_sign = b.advisor_call_sign 
									   order by call_sign";
				$cwa_advisor		= $wpdb->get_results($sql);
				if ($cwa_advisor === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $advisorTableName and $advisorClassTableName tables failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "Daily Student Cron (D) Reading $advisorTableName and $advisorClassTableName tables failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
				} else {
					$numARows									= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numARows rows in $$advisorTableName / $advisorClassTableName<br />";
					}
					if ($numARows > 0) {
						foreach ($cwa_advisor as $advisorRow) {
							$advisor_call_sign 						= strtoupper($advisorRow->call_sign);
							$advisor_survey_score 					= $advisorRow->survey_score;
							$advisor_verify_response 				= strtoupper($advisorRow->verify_response);
							$advisorClass_sequence 					= $advisorRow->sequence;
							$advisorClass_level 					= $advisorRow->level;
							$advisorClass_class_incomplete 			= $advisorRow->class_incomplete;
							$advisorClass_class_schedule_days_utc 	= $advisorRow->class_schedule_days_utc;
							$advisorClass_class_schedule_times_utc 	= $advisorRow->class_schedule_times_utc;

							if ($doDebug) {
								echo "<br /><b>Processing Advisor $advisor_call_sign Sequence $advisorClass_sequence</b> ($advisor_survey_score | $advisor_verify_response)<br />
									  Level: $advisorClass_level<br />
									  schedule Days: $advisorClass_class_schedule_days_utc 
									  schedule times: $advisorClass_class_schedule_times_utc<br />";
							}
							if ($advisor_survey_score != '6' and $advisor_verify_response != 'R') {
								if ($doDebug) {
									echo "Adding $advisor_call_sign to advisorArray and processing classes<br />";
								}
								if ($advisorClass_class_incomplete == 'Y') {
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp;&nbsp;advisorClass incomplete. Skipping<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;Value: $advisorClass_class_incomplete<br />";
									}
									$errorArray[]	= "advisorClass incomplete for $advisor_call_sign, $advisorClass_sequence. Skipped.<br />";
								} else {
									// fix up the class schedule times to be on the hour
									$myStr1 		= substr($advisorClass_class_schedule_times_utc,0,2);
									$advisorClass_class_schedule_times_utc	= $myStr1 . "00";
									// see if record is in the classesArray. If not, add it. Otherwise, count it
									$classesArrayKey					= "$advisorClass_level|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc";
									if (array_key_exists($classesArrayKey,$classesArray)) {
										$classesArray[$classesArrayKey]++;
									} else {
										$classesArray[$classesArrayKey]	= 1;
									}
									// now put the record into the advisorClasses array
									$advisorClasses[$classesArrayKey][$advisorClassInc]	= "$advisor_call_sign-$advisorClass_sequence";
									$advisorClassInc++;
									if ($doDebug) {
										echo "Incremented $classesArrayKey in classesArray<br />Put $advisor_call_sign-$advisorClass_sequence in advisorClasses array<br />";
									}
								}
							} else {
								if ($doDebug) {
									echo "$advisor_call_sign has issues with survey score or verify response<br />";
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "No $advisorTableName / $advisorClassTableName records found for $nextSemester<br />";
						}
					}
				}
				ksort($classesArray);
				if ($doDebug) {
					echo "<br />classesArray built for advisorClass table<br /><pre>";
					print_r($classesArray);
					echo "</pre><br />";
				}
				ksort($advisorClasses);
				if ($doDebug) {
					echo "<br />advisorClasses array built:<br /><pre>";
					print_r($advisorClasses);
					echo "</pre><br />";
				}

				$firstTime				= TRUE;
				$catalogReport			= "";
				$catalogReport			= "<p>Generated Class Catalog for $nextSemester<br />";
				foreach($classesArray as $myKey=>$myValue) {
					if ($doDebug) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;classesArray: $myKey = $myValue<br />";
					}
					// get list of advisors with this class
					$advisorList		= '';
					$advisorFirst		= TRUE;
					$prevAdvisor		= '';
					if (array_key_exists($myKey,$advisorClasses)) {
						foreach($advisorClasses[$myKey] as $thisSeq=>$thisAdvisor) {
							if ($thisAdvisor != $prevAdvisor) {
								if ($advisorFirst) {
									$advisorList	= $thisAdvisor;
									$advisorFirst	= FALSE;	
								} else {
									$advisorList	.= ",$thisAdvisor";
								}
								$prevAdvisor		= $thisAdvisor;
							}
						}
					}
					$thisOutputRecord			= "$myKey|$myValue|$advisorList";
					$newCatalogArray[$myKey]	= "$myValue|$advisorList";
					if ($firstTime) {
						$firstTime				= FALSE;
						$outputRecord			= $thisOutputRecord;
					} else {
						$outputRecord			.= "&$thisOutputRecord";
					}
			
					$catalogReport				.= "$thisOutputRecord<br />";
					if ($doDebug) {
						echo "added $thisOutputRecord to catalog and the display<br />";
					}
			
				}

				$needToInsert				= FALSE;			/// only write catalog record if there are changes			
				$content					.= "<h4>Catalog Additions, Changes, and Deletions</h4>";
				foreach($newCatalogArray as $newKey=>$newValue) {
					if (array_key_exists($newKey,$oldCatalogArray)) {
						$oldValue			= $oldCatalogArray[$newKey];
						if ($newValue != $oldValue) {
							$changesArray[]	= "From: $newKey|$oldValue<br />To: $newKey|$newValue";
						}
					} else {
						$additionsArray[]	= "Added: $newKey|$newValue";
					}
				}

				// find any deletions
				foreach($oldCatalogArray as $oldKey=>$oldValue) {
					if (!array_key_exists($oldKey,$newCatalogArray)) {
						$deletionsArray[]	= "Deleted $oldKey|$oldValue";
					}
				}



				if (count($additionsArray) > 0) {
					$needToInsert			= TRUE;
					$content 				.= "Have Additions:<br />";
					foreach($additionsArray as $myValue) {
						$content 			.= "$myValue<br />";
					}
				} else {
					$content 				.= "No Additions<br />";
				}
				if (count($changesArray) > 0) {
					$needToInsert			= TRUE;
					$content 				.= "<br />Have Changes:<br />";
					foreach($changesArray as $myValue) {
						$content 			.= "$myValue<br />";
					}
				} else {
					$content 				.= "<br />No Changes<br />";
				}
				if (count($deletionsArray) > 0) {
					$needToInsert			= TRUE;
					$content 				.= "<br />Have Deletions:<br />";
					foreach($deletionsArray as $myValue) {
						$content 			.= "$myValue<br />";
					}
				} else {
					$content 				.= "<br />No Deletions<br />";
				}
				if ($needToInsert) {						/// something changed. Update catalog record
					$catalogResult			= $wpdb->update($catalogTableName,
															array('catalog'=>$outputRecord),
															array('record_id'=>$record_id),
															 array('%s'),
															 array('%d'));

					if ($catalogResult === FALSE) {
						$content				.= "<br />ERROR Inserting new catalog into $catalogTableName failed<br />";
						$myError			= $wpdb->last_error;
						$myQuery			= $wpdb->last_query;
						if ($doDebug) {
							echo "Inserting $outputRecord into $catalogTableName failed<br />
								  wpdb->last_query: $myQuery<br />
								  wpdb->last_error: $myError<br />";
						}
						$errorMsg			= "Daily Student Cron (E) Inserting $outputRecord into $catalogTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
						sendErrorEmail($errorMsg);
					}
				} 
			}
			///// all processing done. Prepare totals	
			if ($doDebug) {
				echo "<br />Sending email with the totals<br />";
			}

			$content		.= "<br /><br />$catalogReport</p>";
			if (count($errorArray) > 0) {
				$content	.= "<p>Class Catalog Errors:<br />";
				foreach($errorArray as $myValue) {
					$content	.= "$myValue";
				}
				$content		.= "</p>";
			}
		}

		$thisTime 		= date('Y-m-d H:i:s');
		$content		.= "<br />Function completed at $thisTime<br />";
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
		$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|0: $elapsedTime");
		if ($result == 'FAIL') {
			$content	.= "<p>writing to joblog.txt failed</p>";
		}
		// store the report in the reports table
		$storeResult	= storeReportData_v2('Daily Catalog Cron',$content,$testMode,$doDebug);
		if ($storeResult[0] === FALSE) {
			if ($doDebug) {
				echo "storing report failed. $storeResult[1]<br />";
			}
			$content	.= "Storing report failed. $storeResult[1]<br />";
		} else {
			$reportid	= $storeResult[2];
		}
		// store the reminder
		$closeStr		= strtotime("+2 days");
		$close_date		= date('Y-m-d H:i:s', $closeStr);
		$token			= mt_rand();
		$reminder_text	= "<p>To view the Daily Catalog Cron report for $nowDate $nowTime, click <a href='$siteURL/cwa-display-saved-report/?strpass=3&inp_callsign=WR7Q&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
		$inputParams		= array("effective_date|$nowDate $nowTime|s",
									"close_date|$close_date|s",
									"resolved_date||s",
									"send_reminder|N|s",
									"send_once|N|s",
									"call_sign|WR7Q|s",
									"role||s",
									"email_text||s",
									"reminder_text|$reminder_text|s",
									"resolved|N|s",
									"token|$token|s");
		$reminderResult	= add_reminder($inputParams,$testMode,$doDebug);
		if ($reminderResult[0] === FALSE) {
			if ($doDebug) {
				echo "adding reminder failed. $reminderResult[1]<br />";
			}
		}
		$token			= mt_rand();
		$reminder_text	= "<p>To view the Daily Catalog Cron report for $nowDate $nowTime, click <a href='$siteURL/cwa-display-saved-report/?strpass=3&inp_callsign=K7OJL&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
		$inputParams		= array("effective_date|$nowDate $nowTime|s",
									"close_date|$close_date|s",
									"resolved_date||s",
									"send_reminder|N|s",
									"send_once|N|s",
									"call_sign|K7OJL|s",
									"role||s",
									"email_text||s",
									"reminder_text|$reminder_text|s",
									"resolved|N|s",
									"token|$token|s");
		$reminderResult	= add_reminder($inputParams,$testMode,$doDebug);
		if ($reminderResult[0] === FALSE) {
			if ($doDebug) {
				echo "adding reminder failed. $reminderResult[1]<br />";
			}
		}

		$theSubject	= "CWA Daily Catalog Cron Process";
		$theContent	= "The daily catalog cron process was run at $nowDate $nowTime, Login to <a href='$siteURL/program-list'>CW Academy</a> to see the 
						report.";
		if ($testMode) {		
			$theRecipient	= '';
			$mailCode	= 2;
			$theSubject = "TESTMODE $theSubject";
		} else {
			$theRecipient	= '';
			$mailCode		= 18;
		}
		$result		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
										 		  'theSubject'=>$theSubject,
										 		  'jobname'=>$jobname,
										 		  'theContent'=>$theContent,
										 		  'mailCode'=>$mailCode,
										 		  'testMode'=>$testMode,
										 		  'doDebug'=>$doDebug));
		if ($result === TRUE) {
			return "Process completed";
		} else {
			$content .= "<br />The final mail send function to $myTo failed.</p>";
			return $content;
		}
	}
}	
add_shortcode ('daily_catalog_cron_process_v3', 'daily_catalog_cron_process_v3_func');
