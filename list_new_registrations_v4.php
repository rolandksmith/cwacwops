function list_new_registrations_v3_func(){

/*	List new registrations and any issues with usernames

	modified 26Dec23 by Roland to remove usernames that never create a sign up record
	Modified 8Jan24 by Roland to add ability to ignore errors and no longer automatically 
		some users
	
*/

	global $wpdb, $doDebug, $currentSemester, $nextSemester, $semesterTwo, 
			$semesterThree, $semesterFour, $userName, $jobname, $allUsersArray;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$rkslist						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];


	$versionNumber				 	= "4";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$rkslistArray		= array();
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
	$studentUpdateURL		= "$siteURL/cwa-display-and-update-student-information/";	
	$advisorUpdateURL		= "$siteURL/cwa-display-and-update-advisor-information/";	
	$jobname				= "List New Registrations V$versionNumber";
	$advisorTableName		= "wpw1_cwa_consolidated_advisor";
	$studentTableName		= "wpw1_cwa_consolidated_student";
	$tempTableName			= "wpw1_cwa_temp_data";
	$nowDate				= date('Y-m-d H:i:s');
	$userLoginCount			= 0;
	$userUnverifiedCount	= 0;
	$userUnverifiedDeleted	= 0;
	$newRegistrations		= 0;
	$badUsernameCount		= 0;
	$signupEmailCount		= 0;
	$tempDataAdded			= 0;
	$tempDataDeleted		= 0;
	$usernamesDeleted		= 0;
	$newSignup				= 0;
	$advisorMissingUsername	= 0;
	$studentMissingUsername	= 0;
	$userNameArray			= array();
	$signupArray			= array();
	$user_needs_verification	= FALSE;
	$id						= '';
	$user_login				= '';
	$display_name			= '';
	$user_registered		= '';
	$first_name				= '';
	$last_name				= '';
	$user_role				= '';
	$studentNoSignup		= 0;
	$advisorNoSignup		= 0;
	$advisorNoUsernameArray	= array();
	$studentNoUsernameArray	= array();
	$advisorNoUsername		= 0;
	$studentNoUsername		= 0;
	$badUserNameCount		= 0;
	$bypassArray			= array('ROLAND',
									'KCGATOR', 
									'N7AST', 
									'F8TAM', 
									'BOBC',
									'AH7RF',
									'ah7rf');
$signupRecord		= FALSE;		// whether or not there is a signup record with callsign = username
$verifiedUser		= FALSE;		// whether or not the username record is verified
$validFormat		= FALSE;		// whether or not the username is a callsign or the user's last name
$tempRegister		= FALSE;		// whether or not there is a temp_data register record
$tempIgnore			= FALSE;		// whether or not there is a temp_data ignore record
$threeDayDate		= '';			// temp_data register date_written plus 3 days
$threeDayPlus		= FALSE;		// whether or not the three day countdown date is less than today
$tenDayDate			= '';			// temp_data register date_written plus 10 days
$tenDayPlus			= FALSE;		// whether or not temp_data register date_written is less than today
$setTempRegister	= array();		// whether or not to write a temp_data register record
$setTempIgnore		= array();		// whether or not to write a temp_data ignore record
$deleteTempRegister	= array();		// whether or not to delete a temp_data register record
$deleteTempIgnore	= array();		// whether or not to delete a temp_data ignore record
$sendSignupEmail	= array();		// whether or not to send a signup reminder email
$emailSignup		= FALSE;		// found signup record using email address
$signupCallsign		= ''			// Callsign in signup record
$sendRegisterEmail	= array();		// Whether nor not to send email requesting user create a username
	
	
	

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
	$runByCron				= FALSE;
////// see if this is the time to actually run
	if ($doDebug) {
		echo "<br />starting<br />";
	}
		
	if ($userName != '') {
		$content 			.= "<h3>$jobname Executed by $userName</h3>";
	} else {
		$content			.= "<h3>$jobname Automatically Executed</h3>";
		$runByCron			= TRUE;
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
		$nowTime = strtotime($thisTime);
		if ($nowTime >= $checkBegin && $nowTime <= $checkEnd) {
			$runTheJob = TRUE;
		} else {
			$runTheJob = FALSE;
			$userName	= "CRON Aborted";
			if ($doDebug) {
				echo "runTheJob is FALSE<br />";
			}
		}
	}
	if ($runTheJob) {

		function delete_user( $user_id ) {

			global $wpdb, $doDebug;
			
//			if ($doDebug) {
//				echo "would have deleted user id $user_id<br />";
//				return TRUE;
//			}

			//Include the user file with the user administration API
			require_once( ABSPATH . 'wp-admin/includes/user.php' );

			//Delete a WordPress user by specifying its user ID. Here the user with an ID equal to $user_id is deleted.
			return wp_delete_user( $user_id );

		}
		
		function delete_temp_record($user_login,$token) {
			global $wpdb, $doDebug;
			
//			if ($doDebug) {
//				echo "would have deleted temp_data for $user_login<br/>";
//				return TRUE;
//			}
			
			$result	= $wpdb->delete('wpw1_cwa_temp_data',
									array('token'=>$token,
											'callsign'=>$user_login),
									array('%s','%s'));
			if ($result === FALSE) {
				handleWPDBError("List New Registrations V2",$doDebug);
				return FALSE;
			} elseif ($result == 0) {
				$lastQuery		= $wpdb->last_query;
				if ($doDebug) {
					echo "List New Registrations attempting to delete 
user_login $user_login with token $token deleted 0 rows. Query: $lastQuery<br />";
				}
			} else {
				return TRUE;
			}
		}




/////// real start

		require_once( ABSPATH . 'wp-admin/includes/user.php' );

		// get all registrations
		$sql				= "SELECT id, 
									   user_login, 
									   user_email, 
									   display_name, 
									   user_registered 
								FROM `wpw1_users` 
								order by user_login";
		$result				= $wpdb->get_results($sql);
		if ($result === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows		= $wpdb->num_rows;
			if ($doDebug) {
				$debugData .= "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				$myInt				= strtotime("$nowDate -36 hours");
				$recents			= date('Y-m-d H:i:s',$myInt);
				$content			.= "<h4>New Registrants Since $recents</h4>
										<table style='width:auto;'>
										<tr><th>Role</th>
											<th>Call Sign</th>
											<th>Name</th>
											<th>Email</th>
											<th>Signup</th></tr>";
				foreach($result as $resultRow) {
					$doProceed			= TRUE;

					$signupRecord		= FALSE;		// whether or not there is a signup record with callsign = username
					$verifiedUser		= FALSE;		// whether or not the username record is verified
					$validFormat		= FALSE;		// whether or not the username is a callsign or the user's last name
					$tempRegister		= FALSE;		// whether or not there is a temp_data register record
					$tempIgnore			= FALSE;		// whether or not there is a temp_data ignore record
					$threeDayDate		= '';			// temp_data register date_written plus 3 days
					$threeDayPlus		= FALSE;		// whether or not the three day countdown date is less than today
					$tenDayDate			= '';			// temp_data register date_written plus 10 days
					$tenDayPlus			= FALSE;		// whether or not temp_data register date_written is less than today
					$signupCallsign		= ''			// Callsign in signup record

					$user_id			= $resultRow->id;
					$user_login			= $resultRow->user_login;
					$user_email			= $resultRow->user_email;
					$display_name		= $remyStrsultRow->display_name;
					$user_registered	= $resultRow->user_registered;
					
					$user_uppercase		= strtoupper($username);
					
					if ($doDebug) {
						$debugData .= "<br />Processing $user_login<br />";
					}
					
					if (in_array($user_uppercase,$bypassArray)) { 
						$doProceed 		= FALSE;
					}
					if ($doProceed) {
						$user_first_name			= '';
						$user_last_name				= 'N/A';
						$user_role					= '';
						$userLoginCount++;
						$doProceed					= TRUE;
					
					
						$metaSQL		= "select meta_key, meta_value 
											from `wpw1_usermeta` 
											where user_id = $user_id 
											and (meta_key = 'first_name' 
												or meta_key = 'last_name' 
												or meta_key = 'wpw1_capabilities' 
												or meta_key = 'wpumuv_needs_verification')";
						$metaResult		= $wpdb->get_results($metaSQL);
						if ($metaResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numMRows	= $wpdb->num_rows;
							if ($doDebug) {
								$debugData .= "ran $metaSQL<br />and retrieved $numMRows rows<br />";
							}
							foreach($metaResult as $metaResultRow) {
								$meta_key		= $metaResultRow->meta_key;
								$meta_value		= $metaResultRow->meta_value;
						
								if ($meta_key == 'last_name') {
									$user_last_name	= $meta_value;
								}
								if ($meta_key == 'first_name') {
									$user_first_name = $meta_value;
								}
								if ($meta_key == 'wpw1_capabilities') {
						
									$myInt			= strpos($meta_value,'administrator');
									if ($myInt !== FALSE) {
										$user_role	= 'aministrator';
									}
									$myInt			= strpos($meta_value,'student');
									if ($myInt !== FALSE) {
										$user_role	= 'student';
									}
									$myInt			= strpos($meta_value,'advisor');
									if ($myInt !== FALSE) {
										$user_role	= 'advisor';
									}
								}
								if ($meta_key == 'wpumuv_needs_verification') {
									$verifiedUser				= FALSE;
								} else {
									$verifiedUser				= TRUE;
								}
							}
						}
						if ($doProceed) {
						
							$allUsersArray[$user_uppercase]	= array('last_name'=>$user_last_name, 
															 'first_name'=>$user_first_name, 
															 'display_name'=>$display_name, 
															 'user_registered'=>$user_registered, 
															 'user_email'=>$user_email, 
															 'id'=>$user_id, 
															 'user_role'=>$user_role,
															 'tempRegisterID'=>0,
															 'tempIgnoreID'=>0; 
															 'hasError'=>'N', 
															 'theError'=>"Username created $user_registered<br />");
							if ($doDebug) {
								$debugData .= "added $user_login to allUsersArray<br />";
							}
												
							// get the temp_data record, if any
							$gotTempRecord		= FALSE;
							$tempSQL			= "select * from wpw1_cwa_temp_data 
													where callsign = '$user_uppercase' and 
														  (token = 'register' or 
														   token = 'ignore') 
													order by date_written";
							$tempResult			= $wpdb->get_results($tempSQL);
							if ($tempResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$numTempRows	= $wpdb->num_rows;
								if ($doDebug)  {
									$debugData .= "ran $tempSQL<br />and retrieved $numTempRows rows<br />";
								}
								if ($numTempRows > 0) {
									$gotTempRecord	= TRUE;
									foreach ($tempResult as $tempResultRow) {
										$tempID			= $tempResultRow->record_id;
										$tempData		= $tempResultRow->temp_data;
										$date_written	= $tempResultRow->date_written;
										
										if ($tempData == 'register') {
											$tempRegister			= TRUE;
											$allUsersArray[$user_uppercase]['thempRegisterID']	= $tempID;
											$myInt					= strtotime("$date_written + 3 days");
											$threeDayDate			= date('Y-m-d H:i:s',$myInt);
											if ($nowDate < $threeDayDate) {
												$threeDayPlus		= TRUE;
											}
											$myInt					= strtotime("$date_written + 10 days");
											$tenDayDate				= date('Y-m-s H:i:s',$myInt);
											if ($nowDate < $tenDayDate) {
												$tenDayPlus			= TRUE;
											}
										} elseif ($tempData == 'ignore') {
											$tempIgnore		= TRUE;
											$allUsersArray[$user_uppercase]['tempIgnoreID']	= $tempID;
										}
									}
								}
							}
						
							// if the user_role is blank, then delete the user
							if ($user_role == '') {
								if ($doDebug) {
									$debugData .= "user_role of $user_role is invalid.<br />";
								}
								$allUsersArray[$user_uppercase]['hasError']	.= "Y";
								$allUsersArray[$user_uppercase]['theError']	.= "user_role invalid. Recommend deleting username<br />";
							}
						
						
					
							if ($doProceed) {
								// do some checks on the username
								$badUserName			= FALSE;
								$alphaResult			= preg_match('/^[A-Za-z0-9]+$/',$user_login);
								if ($alphaResult == 1) {			// have a match
									if ($doDebug) {
										$debugData .= "$user_login passes the preg_match test<br />";
									}
									$betaResult		= preg_match('/^[A-Za-z]+$/',$user_login);
									if ($betaResult == 1) {		// it's alphabetic -- not a callsign
										// is username also the last name? if not, say so
										$mystr1				= strtoupper($user_login);
										$myStr2				= strtoupper($user_last_name);
										if ($mystr1 != $myStr2) {
											if ($doDebug) {
												$debugData .= "<b>ERROR</b> username $user_login is not a callsign and not last name of $user_last_name<br />";
											}
											$badUserName	= TRUE;
										}
									} else {						// has numeric -- maybe a callsign
										$testCallsign		= preg_match('/^[a-zA-Z0-9]{1,3}[0-9][a-zA-Z0-9]{0,3}[a-zA-Z]+$/',$user_login);
										if ($testCallsign == 1) {		// fits the callsign regex
											if ($doDebug) {
												$debugData .= "user_login $user_login passed the callsign regex<br />";
											}
										} else {							
											if ($doDebug) {
												$debugData .= "<b>ERROR</b> username $user_login does not fit a callsign pattern<br />";
											}
											$badUserName		= TRUE;
										}
									}
								} else {
									if ($doDebug) {
										$debugData .= "<b>ERROR</b> $user_login does not pass the preg_match test<br />";
									}
									$badUserName			= TRUE;
								}
						
								if ($badUserName) {
									$badUserNameCount++;
								} else {
									$validFormat				= TRUE;
								}
					
					
								// see if the user_login has a signup record
								$signup				= '';
								if ($user_role == 'student') {
									$student_level	= '';
									$student_semester = '';
									$studentSQL		= "select * from $studentTableName 
														where call_sign = '$user_uppercase'
														order by date_created DESC 
														limit 1";
									$studentResult	= $wpdb->get_results($studentSQL);
									if ($studentResult === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										$numSRows	= $wpdb->num_rows;
										if ($numSRows > 0) {
											foreach($studentResult as $studentResultRow) {
												$student_semester	= $studentResultRow->semester;
												$student_level		= $studentResultRow->level;
							
												$signup				= "signed up for $student_level in $student_semester";
												$signupRecord		= TRUE;
												$allUsersArray[$user_uppercase]['theError']	.= "User has a student signup for $student_level in $student_semester<br />";
												if ($doDebug) {
													$debugData .= "$user_login has a student signup record<br />";
												}
											}
										} else {
											// no signup record by username. See if there is one using email
											$myStr				= strtolower($user_email);
											$studentSQL			= "select * from $studentTableName 
																	where (email='$myStr' pr 
																		   email='$user_email') 
																	order by date_created DESC 
																	limit 1";
											$studentResult	= $wpdb->get_results($studentSQL);
											if ($studentResult === FALSE) {
												handleWPDBError($jobname,$doDebug);
											} else {
												$numSRows	= $wpdb->num_rows;
												if ($numSRows > 0) {
													foreach($studentResult as $studentResultRow) {
														$signupCallsign		= $studentResultRow->call_sign;
														$student_semester	= $studentResultRow->semester;
														$student_level		= $studentResultRow->level;
									
														$signup				= "signed up for $student_level in $student_semester";
														$emailSignup		= TRUE;
														$allUsersArray[$user_uppercase]['theError']	.= "User has a student signup for $student_level in $student_semester callsign $signupCallsign<br />";
														if ($doDebug) {
															$debugData .= "$user_uppercase has a student signup record with callsign $signupCallsign<br />";
														}
													}
												}
											}	
										}
									}
								} elseif ($user_role == 'advisor') {
									$advisorSQL		= "select * from $advisorTableName 
													where call_sign = '$user_uppercase'
													order by date_created DESC 
													limit 1";
									$advisorResult	= $wpdb->get_results($advisorSQL);
									if ($advisorResult === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										$numARows	= $wpdb->num_rows;
										if ($numARows > 0) {
											foreach($advisorResult as $advisorResultRow) {
												$advisor_semester	= $advisorResultRow->semester;
							
												$signup				= "signed up $advisor_semester";
												$signupRecord		= TRUE;
												$allUsersArray[$user_uppercase]['theError']	.= "User has an advisor signup in $advisor_semester<br />";
												if ($doDebug) {
													$debugData .= "$user_login has an advisor signup record<br />";
												}
											}
										} else {		// no signup record by username. Check by email
											$myStr			= strtolower($user_email);
											$advisorSQL		= "select * from $advisorTableName 
															where (email = '$myStr' or 
																   email = '$user_email') 
															order by date_created DESC 
															limit 1";
											$advisorResult	= $wpdb->get_results($advisorSQL);
											if ($advisorResult === FALSE) {
												handleWPDBError($jobname,$doDebug);
											} else {
												$numARows	= $wpdb->num_rows;
												if ($numARows > 0) {
													foreach($advisorResult as $advisorResultRow) {
														$advisor_semester	= $advisorResultRow->semester;
									
														$signup				= "signed up $advisor_semester";
														$allUsersArray[$user_uppercase]['theError']	.= "User has an advisor signup in $advisor_semester callsign $signupCallsign<br />";
														$emailSignup		= TRUE;
														if ($doDebug) {
															$debugData .= "$user_login has an advisor signup record under callsign $signupCallsign<br />";
														}
													}
												}
											}
										}
									}
								}

	
								// if a recent registration, display
								$recentRegister			= FALSE;
								if ($user_registered >= $recents) {
									$recentRegister		= TRUE;
									$newRegistrations++;
									if ($doDebug) {
										$debugData .= "$user_login is a recent registration<br />";
									}
									$user_uppercase				= strtoupper($user_login);
									$thisStr			= '';
									if (!$unverifiedUser) {
										if ($user_role == 'advisor') {
											$update			= "<a href='https://cwa.cwops.org/cwa-display-and-update-advisor-information/?request_type=callsign&request_info=$user_uppercase&inp_table=advisor&strpass=2' target='_blank'>$user_login</a>'";
										} elseif ($user_role == 'student') {
											$update			= "<a href='https://cwa.cwops.org/cwa-display-and-update-student-information/?request_type=callsign&request_info=$user_uppercase&request_table=wpw1_cwa_consolidated_student&strpass=2' target='_blank'>$user_login</a>";
										} else {
											$update			= $user_login;
										}
									} else { 
										$update				= $user_login;
										$signup				= "Unverified User";
									}
									$content			.= "<tr><td>$user_role</td>
																<td>$update</td>
																<td>$user_last_name, $user_first_name</td>
																<td><a href='mailto:$user_email' target='_blank'>$user_email</a></td>
																<td>$signup $thisStr</td></tr>";
								}
					

								if ($doDebug) {			// display what we've got so far
									foreach($allUsersArray[$user_uppercas] as $thisKey=>$thisValue) {
										$debugData .= "$thisKey: $thisValue<br />";
									}
									$codeStr		= '';
									if ($recentRegister) {
										$debugData .= "recentRegister: TRUE<br />";
									} else {
										$debugData .= "recentRegister: FALSE<br />";
									}
									if ($signupRecord) {
										$debugData .= "signupRecord: TRUE<br />";
										$codeStr	.= 'Y';
									} else {
										$debugData .= "signupRecord: FALSE<br />";
										$codeStr	.= 'N';
									}
									if ($verifiedUser) {
										$debugData .= "verifiedUser: TRUE<br />";
										$codeStr	.= 'Y';
									} else {
										$debugData .= "verifiedUser: FALSE<br />";
										$codeStr	.= 'N';
									}
									if ($validFormat) {
										$debugData .= "validFormat: TRUE<br />";
										$codeStr	.= 'Y';
									} else {
										$debugData .= "validFormat: FALSE<br />";
										$codeStr	.= 'N';
									}
									if ($tempRegister) {
										$debugData .= "tempRegister: TRUE<br />";
										$codeStr	.= 'Y';
									} else {
										$debugData .= "tempRegister: FALSE<br />";
										$codeStr	.= 'N';
									}
									if ($tempIgnore) {
										$debugData .= "tempIgnore: TRUE<br />";
										$codeStr	.= 'Y';
									} else {
										$debugData .= "tempIgnore: FALSE<br />";
										$codeStr	.= 'N';
									}
									if (!$signupRecord) {
										if ($emailSignup) {
											$debugData .= "emailSignup: TRUE<br />";
											$codeStr	.= 'Y';
										} else {
											$debugData .= "emailSignup: FALSE<br />";
											$codeStr	.= 'N';
										}
									} else {
										$codeStr		.= '-':
									}
									$debugData .= "Code: $codeStr<br />";
								}							
							
							
								/*	Rules
									If unverified and no signup record, wait three days
									If unverified and a signup record, wait ten days
								*/
								
								if ($signupRecord && $verifiedUser && $validFormat && $tempRegister && $tempIgnore) { 
									if ($doDebug) {
										$debugData .= "YYYYY- has signed up, verified, valid format, tempRegister is set, tempIgnore is set<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										Delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if ($signupRecord && $verifiedUser && !$validFormat && $tempRegister && $tempIgnore) { 
									if ($doDebug) {
										$debugData .= "YYNYY- has signed up, verified, invalid format, tempRegister is set, tempIgnore is set<br />
										Program error. tempRegister and tempIgnore can't be set at the same time. 
										Delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if ($signupRecord && !$verifiedUser && $validFormatsignupRecord && $tempRegister && $tempIgnore) { 
									if ($doDebug) {
										$debugData .= "YNYNN- has signed up, unverified, valid format, tempRegister is set, tempIgnore is set<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										Delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if ($signupRecord && !$verifiedUser && !$validFormat && $tempRegister && $tempIgnore) { 
									if ($doDebug) {
										$debugData .= "YNNYY- has signed up, unverified, invalid format, tempRegister is set, tempIgnore is set<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										Delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								
								if (!$signupRecord && $verifiedUser && $validFormat && $tempRegister && $tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NYYYYY: no signup record, verified, valid format, tempRegister is set, tempIgnore is set, emailSignup is set<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if (!$signupRecord && $verifiedUser && $validFormat && $tempRegister && $tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYYYYN: no signup record, verified, valid format, tempRegister is set, tempIgnore is set, no emailSignup<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if (!$signupRecord && $verifiedUser && !$validFormat && $tempRegister && $tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NYNYYY: no signup record, verified, invalid format, tempRegister is set, tempIgnore is set, emailSignup is set<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if (!$signupRecord && $verifiedUser && !$validFormat && $tempRegister && $tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYNYYN: no signup record, verified, invalid format, tempRegister is set, tempIgnore is set, no emailSignup<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if (!$signupRecord && !$verifiedUser && $validFormat && $tempRegister && $tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NNYYYY: no signup record, unverified, valid format, tempRegister is set, tempIgnore is set, emailSignup is set<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if (!$signupRecord && !$verifiedUser && $validFormat && $tempRegister && $tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NNYYYN: no signup record, unverified, valid format, tempRegister is set, tempIgnore is set, no emailSignup<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if (!$signupRecord && !$verifiedUser && !$validFormat && $tempRegister && $tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NNNYYY: no signup record, unverified, invalid format, tempRegister is set, tempIgnore is set, emailSignup is set<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								if (!$signupRecord && !$verifiedUser && !$validFormat && $tempRegister && $tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NNNYYN: no signup record, unverified, invalid format, tempRegister is set, tempIgnore is set, no emailSignup<br />
										Program error. tempRegister and tempIgnore can not be set at the same time. 
										delete tempRegister. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= 'Program error. Deleting temp_data records<br />';
									delete_tempRegister($user_uppercase,$registerToken);
									delete_tempIgnore($user_uppercase,$ignoreToken);
								}
								
								
								//////////??????????/////////
								
								
								if ($signupRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore) {
									if ($doDebug) {
										$debugData .= "YYYYN- has signed up, verified, valid format, tempRegister is set, no tempIgnore<br />
										User got username and verified but didn't sign up. Has received signup reminder 
										email and has now signed up. Delete tempRegister<br />";
									}
									$deleteTempRegister								= TRUE;
								}
								if ($signupRecord && $verifiedUser && $validFormat && !$tempRegister && $tempIgnore) { 
									if ($doDebug) {
										$debugData .= "YYYNY- has signed up, verified, valid format, no tempRegister, tempIgnore is set<br />
										No need anymore for tempIgnore. Delete tempIgnore<br />";
									}
									$allUsersArray[$user_uppercase]['theError']	.= 'Deleting unnecessary ignore record in temp_data<br />';
									$deleteTempIgnore							= TRUE;
								}
								if ($signupRecord && $verifiedUser && $validFormat && !$tempRegister && !$tempIgnore) {
									if ($doDebug) {
										$debugData .= "YYYNN- has signed up, verified, valid format, no tempRegister, no tempIgnore<br />
										No action needed<br />";
									}
								}
								if ($signupRecord && $verifiedUser && !$validFormat && $tempRegister && !$tempIgnore) {
									if ($doDebug) {
										$debugData .= "YYNYN- has signed up, verified, invalid format, tempRegister is set, no tempIgnore<br />
										User got a username which is invalid and signed up. A tempRegister record was written 
										to start a ten-day countdown. Check to see if ten days have passed. If so, 
										recommend ignoring the error\<br />";
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= 'User has invalid username. Ten 
										days have passed. Recommend ignoring.<br />';
									}
								}
								if ($signupRecord && $verifiedUser && !$validFormat && !$tempRegister && $tempIgnore) { 
									if ($doDebug) {
										$debugData .= "YYNNY- has signed up, verified, invalid format, no tempRegister, tempIgnore is set<br />
										tempIgnore has been set to ignore this error. No action needed<br />";
									}
								}
								if ($signupRecord && $verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore) {
									if ($doDebug) {
										$debugData .= "YYBNNN- as signed up, verified, invalid format, no tempRegister, no tempIgnore<br />
										User has obtained an invalid username and signed up. This is the first time we're 
										seeing this record. Set tempRegister for a ten-day countdown. Show error.<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "Username is invalid<br />
																					Signed up with invalid username<br />
																					Set ten-day timer<br />";
									$setTempRegisterArray[]						= "$username&$user_role";
								}
								if ($signupRecord && !$verifiedUser && $validFormat && $tempRegister && !$tempIgnore) {
									if ($doDebug) {
										$debugData .= "YMYYY- has signed up, unverified, valid format, tempRegister is set, no tempIgnore<br />
										User signed up before usernames. Has obtained username but not verified. Ten-day 
										timer has already been set. See if time is up. If so, show message and recommend 
										ignoring the error. Otherwise, show error and when the ten-days are up.<br />";
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User signed up before usernames were implemented<br />
																						User signup and username have invalid invalid format<br />
																						Ten-day timer has expired<br />
																						Recommend ignoring<br />";
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User signed up before usernames were implemented<br />
																						User signup and username have invalid invalid format<br />
																						Recommend getting correct callsign information<br />
																						Ten-day timer set. Error will continue to be displayed until<br />
																						$tenDayDate<br />";
										$setTempRegisterArray[]						= "$username&$user_role";
									}	
								}
								
								if ($signupRecord && !$verifiedUser && $validFormat && !$tempRegister && $tempIgnore) { 
									if ($doDebug) {
										$debugData .= "YNYNY- has signed up, unverified, valid format, no tempRegister, tempIgnore is set<br />
										User is unverified and tempIgnore is set. No action taken<br />";
									}
								}
								if ($signupRecord && !$verifiedUser && $validFormat && !$tempRegister && !$tempIgnore) {
									if ($doDebug) {
										$debugData .= "YNYNN- as signed up, unverified, valid format, no tempRegister, no tempIgnore<br />
										User signed up before usernames were implemented. Has since gotten a username but 
										has not verified. Sending reminder email. Setting ten-day countdown timer. At that 
										time will recommend manually verifying if the user has not verified.<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "User signed up before usernames were implemented<br<br />";
									$setTempRegisterArray[]							= $username;
									$sendSignupEmailArray[]							= $username;
								}
								if ($signupRecord && !$verifiedUser && !$validFormat && $tempRegister && !$tempIgnore) {
									if ($doDebug) {
										$debugData .= "YNNYN- has signed up, unverified, invalid format, tempRegister is set, no tempIgnore<br />
										We've seen this record before. The ten-day timer is set. If time has expired, recommend 
										verifying and ignoring<br />";
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has a signup record<br />
																						User is unverified<br />
																						Username and signup record callsign is invalid<br />
																						Ten-day time has expired
																						Recommend verifying and ignoring<br />";
									
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has a signup record<br />
																						User is unverified<br />
																						Username and signup record callsign is invalid<br />
																						Ten-day timer will expire on $tenDayDate<br />";
										
									}
								}
								if ($signupRecord && !$verifiedUser && !$validFormat && !$tempRegister && $tempIgnore) { 
									if ($doDebug) {
										$debugData .= "YNNNY- has signed up, unverified, invalid format, no tempRegister, tempIgnore is set<br />
										tempIgnore is set. No action taken<br />";
									}
								}
								if ($signupRecord && !$verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore) {
									if ($doDebug) {
										$debugData .= "YNNNN- has signed up, unverified, invalid format, no tempRegister, no tempIgnore<br />
										User has a signup record from before usernames were implemented. Has created a username. 
										Both the username and signup callsign are invalid. We're seeing this record for the 
										first time. Recommend finding correct callsign<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "User has signed up before unsernames were imiplemented<br />
																					Username is unverified<br />
																					Username and callsign format is invalid<br />
																					Recommend finding correct callsign<br />
																					Ten-day timer set<br />";
									$setTempRegisterArray[]						= "$username&$user_role";
								}

								
								// when there is no signup record with the callsign = the username, there might be a signup 
								// record for the same user. That record is found by the user's email address
								
								if (!$signupRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore && $emailSignup) {
									if ($doDebug) {
										$debugData .= "NYYYNY: no signup record, verified, valid format, tempRegister is set, no tempIgnore, and has emailSignup<br />
											 Since tempRegister is set, we've seen this record before and the ten-day countdown was set. 
											 See if the countdown has expired. If remind to synchronize the username and callsign. 
											 Recommend otherwise to set tempIgnore<br />";
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has signup record with callsign different than username<br />
																						Signup callsign has valid format<br />
																						Has a username record<br />
																						Reminder email has been sent<br />
																						Ten-day timer expires on $tenDayDate<br />
																						Recommend trying to sync username and callsign<br />
																						Recommend ignore<br />";
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has signup record with callsign different than username<br />
																						Callsign valid format<br />
																						Username record exists<br />
																						Reminder email has been sent<br />
																						Ten-day timer expires on $tenDayDate<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYYYNN: no signup record, verified, valid format, tempRegister is set, no tempIgnore, and no emailSignup<br />
										Has a username, but no signup record by the username. Username has valid format. We've seen this record before. Ten-day 
										is set. If expired, recommend ignore<br />"; 
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																						User does not have a signup record<br />
																						Username format is valid<br />
																						Reminder email has been sent<br />
																						Ten-day timer expired on $tenDayDate<br />
																						Recommend trying to synch username and callsign<br />
																						Recommend ignore<br />";
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has signup record<br />
																						Callsign valid format<br />
																						No username record<br />
																						Reminder email has been sent<br />
																						Ten-day timer expires on $tenDayDate<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && $validFormat && !$tempRegister && $tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NYYNYY: no signup record, verified, valid format, no tempRegister, tempIgnore is set, and has emailSignup<br />
										Have seen this record before and tempIgnore is set. No action needed<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && $validFormat && !$tempRegister && $tempIgnore && !$emailSignup) { 
									if ($doDebug) {
										$debugData .= "NYYNYN: no signup record, verified, valid format, no tempRegister, tempIgnore is set, and no emailSignup<br />
										Since tempIgnore is set, no action taken<br />";
									}
								}
								
								 if (!$signupRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NYYYNY: no signup record, verified, valid format, tempRegister is set, no tempIgnore, emailSignup is set<br />
										Have seen this record before. tempRegister is set, so the ten-day countdown is happening. If countdown 
										has expired, Show final reminder. Recommend tempIgnore<br />";
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																						User has a signup record but username is not the callsign<br />
																						Username format is valid<br />
																						Reminder email has been sent<br />
																						Ten-day timer expired on $tenDayDate<br />
																						Recommend trying to synch username and callsign<br />
																						Recommend ignore<br />";
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has a signup record but username is not the callsign<br />
																						Username valid format<br />
																						Reminder email has been sent<br />
																						Ten-day timer expires on $tenDayDate<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && $validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYYYNN: no signup record, verified, valid format, tempRegister is set, no tempIgnore, no emailSignup<br />
										Have seen this record before. Ten-day timer is set. If expired, recommend tempIgnore<br />";
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																						User no signup record<br />
																						Username format is valid<br />
																						Reminder email has been sent<br />
																						Ten-day timer expired on $tenDayDate<br />
																						Recommend ignore<br />";
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																						Username valid format<br />
																						Reminder email has been sent<br />
																						User has not siogned up<br />														
																						Ten-day timer expires on $tenDayDate<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && $validFormat && !$tempRegister && $tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYYNYN: no signup record, verified, valid format, no tempRegister, tempIgnore is set, no emailSignup<br />
										Have seen this record before and tempIgnore is set. No further action<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && !$validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYNYNN: no signup record, verified, invalid format, tempRegister is set, no tempIgnore, no emailSignup<br />
										Have seen this record before.Ten-day timer is set. If expired, Recommend setting tempIgnore<br />";
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																						No signup record<br />
																						Username format is not valid<br />
																						Reminder email has been sent<br />
																						Ten-day timer expired on $tenDayDate<br />
																						Recommend ignore<br />";
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																						Username is not valid format<br />
																						Reminder email has been sent<br />
																						User has not signed up<br />														
																						Ten-day timer expires on $tenDayDate<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && !$validFormat && !$tempRegister && $tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NYNNYY: no signup record, verified, invalid format, no tempRegister, tempIgnore is set, emailSignup is set<br />
										Have seen this record before. tempIgnore is set. No further action<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && !$validFormat && !$tempRegister && $tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYNNYN: no signup record, verified, invalid format, no tempRegister, tempIgnore is set, no emailSignup<br />
										Have seen this record before. tempIgnore is set. No further action<br />";
									}
								}
								if (!$signupRecord && $verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NYNNNY: no signup record, verified, invalid format, no tempRegister, no tempIgnore, emailSignup is set<br />
										We're seeing this record for the first time. Has unsername but invalid format. Has a signup record 
										with a callsign that's different from the username. Recommend syncing up the username and callsign. 
										set tempRegister ten-day timer<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																					Username is not valid format<br />
																					User has a signup record but callsign is not the username<br />														
																					Recommend  syncing username and callsign<br />ni
																					Ten-day timer expires on $tenDayDate<br />";
									$setTempRegisterArray[]						= "$username&$user_role";
														
								}
								if (!$signupRecord && $verifiedUser && $validFormat && !$tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYYNNN: no signup record, verified, valid format, no tempRegister, no tempIgnore, no emailSignup<br />
										We're seeing this record for the first time. Has username and valid format. No tempRegister, 
										no tempIgnore, and no signup record. Send signup reminder email. Set
										tempRegister ten-day countdown<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																					Username is valid format<br />
																					User does not have a signup record<br />
																					Sending signup reminder email<br />
																					Ten-day timer expires on $tenDayDate<br />";
									$setTempRegisterArray[]						= "$username&$user_role";
									$sendReminderEmailArray[]					= $user_email;
								}
								if (!$signupRecord && $verifiedUser && $validFormat && !$tempRegister && !$tempIgnore &&!$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYYNNY: no signup record, verified, valid format, no tempRegister, no tempIgnore, has emailSignup<br />
										We're seeing this record for the first time. Has username and valid format. No tempRegister, 
										no tempIgnore, but has a signup record with a different callsign. Set
										tempRegister ten-day countdown. Recommend syncing username and signup callsign<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																					Username is valid format<br />
																					User has a signup record with callsign different than username<br />
																					Recommend syncing username and signup callsign<br />
																					Ten-day timer expires on $tenDayDate<br />";
									$setTempRegisterArray[]					 	= "$username&$user_role";
								}
								if (!$signupRecord && $verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NYNNNN: no signup record, verified, invalid format, no tempRegister, no tempIgnore, no emailSignup<br />
										We're seeing this record for the first time. Has username but invalid format. No tempRegister, 
										no tempIgnore, and no signup record. Recommend determining if username record should be kept. Set
										tempRegister ten-day countdown<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "Has username record<br />
																					Username is not valid format<br />
																					User does not have a signup record<br />
																					Recommend determining if username record should be kept<br />
																					Ten-day timer expires on $tenDayDate<br />";
									$setTempRegisterArray[]						= "$username&$user_role";
								}
								if (!$signupRecord && !$verifiedUser && $validFormat && $tempRegister && !$tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NNYYNY: no signup record, unverified, valid format, tempRegister is set, no tempIgnore, emailSignup is set<br />
										Have seen this record before as tempRegister is set. If time expired, recommend verifying. Ptjerwise 
										Recommend syncing username and callsign<br />";
									}
									if ($tenDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has a valid username<br />
																						User has a signup record with a callsign different than username<br />
																						User has not verified the username<br />
																						Ten-day countdown expired on $tenDayDate<br />
																						Recommend verifying and syncing the username and password<br />";
								
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has a valid username<br />
																						User has a signup record with a callsign different than username<br />
																						User has not verified the username<br />
																						Ten-day countdown will expire on $tenDayDate<br />
																						Recommend contacting user to syncing username and callsign<br />";
									
									}
								}
								if (!$signupRecord && !$verifiedUser && $validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NNYYNN: no signup record, unverified, valid format, tempRegister is set, no tempIgnore, no emailSignup<br />
										We've seen this record before. unverified valid username and no signup record. tempRegister is set. 
										If three days have expired, recommend deleting username record. Otherwise just show the error<br />";
									}
									if ($threeDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has a valid username<br />
																						User has a signup record with a callsign different than username<br />
																						User has not verified the username<br />
																						There is no signup record<br />
																						Three-day countdown expired on $threeDayDate<br >
																						User has not verified. Recommend deleting the user<br />";
									
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has a valid username<br />
																						User has a signup record with a callsign different than username<br />
																						User has not verified the username<br />
																						There is no signup record<br />
																						Three-day countdown will expire on $threeDayDate<br >
																						If user doesn't verify by then, recommend deleting the user<br />";
									
									}
								}
								if (!$signupRecord && !$verifiedUser && $validFormat && !$tempRegister && $tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NNYNYY: no signup record, unverified, valid format, no tempRegister, tempIgnore is set, emailSignup is set<br />
										Have seen this record before. tempIgnore is set. No further action<br />";
									}
								}
								if (!$signupRecord && !$verifiedUser && $validFormat && !$tempRegister && $tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NNYNYN: no signup record, unverified, valid format, no tempRegister, tempIgnore is set, no emailSignup<br />
										We've seen this record before. For some reason tempIgnore is set. No further action.<br />";
									}
								}
								if (!$signupRecord && !$verifiedUser && $validFormat && !$tempRegister && !$tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NNYNNY: no signup record, unverified, valid format, no tempRegister, no tempIgnore, emailSignup is set<br />
										First time seeing this record. Has an unverified but valid username and a signup record with a 
										callsign different from the username. Set tempRecord for three-day time for user to verify. If not 
										verified by then, will recommend verifying. Meanwhile, recommend getting username and callsign sync'd 
										up</br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid username<br />
																					Username is not verified<br />
																					User has a signup record with callsign different than username<br />
																					Setting three-day countdown to expire on $threeDayDate<br />
																					Recommend contacting user and syncing username and callsign<br />";
									$setTempRegisterArray[]						= "$username&$user_role";
								}
								if (!$signupRecord && !$verifiedUser && $validFormat && !$tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NNYNNN: no signup record, unverified, valid format, no tempRegister, no tempIgnore, no emailSignup<br />
										First time we've seen this record. Username is valid format but unverified. No signup record found. 
										Recommend contacting user to determine if real. Setting tempRegister for a three-day countdown to 
										see if he verifies in the meantime.<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "User has a valid username<br />
																					Username is not verified<br />
																					User has does not have a signup record<br />
																					Setting three-day countdown to expire on $threeDayDate<br />
																					Recommend sending verify email to user<br />";
									$setTempRegisterArray[]						= "$username&$user_role";
								}
								if (!$signupRecord && !$verifiedUser && !$validFormat && $tempRegister && !$tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NNNYNY: no signup record, unverified, invalid format, tempRegister is set, no tempIgnore, emailSignup is set<br />
										Have seen this record before. User has a signup record with invalid format and has a signup record 
										under a callsign different from the username. tempRegister is set. If three-days has expired, 
										show error and recommend verifying the user and syncing the username and signup callsign<br />";
									}
									if ($threeDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid username<br />
																						Username is not verified<br />
																						User has has a signup record with callsign different from username<br />
																						Three-day countdown expired on $threeDayDate<br />
																						Recommend verifying the user and syncing the username and callsign<br />";
									
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid username<br />
																						Username is not verified<br />
																						User has has a signup record with callsign different from username<br />
																						Three-day countdown will expire on $threeDayDate<br />
																						Recommend syncing the username and callsign<br />";
									
									}
								}
								if (!$signupRecord && !$verifiedUser && !$validFormat && $tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NNNYNN: no signup record, unverified, invalid format, tempRegister is set, no tempIgnore, no emailSignup<br />
										Have seen this record before. User has an invalid username. tempRegister is set waiting on three-day 
										countdown. If expired, recommend deleting the user. <br />";
									}
									if ($threeDayPlus) {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid username<br />
																						Username is not verified<br />
																						User has does not have a signup record<br />
																						Three-day countdown expired on $threeDayDate<br />
																						Recommend deleting the username<br />";
									
									} else {
										$allUsersArray[$user_uppercase]['hasError']	= 'Y';
										$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid username<br />
																						Username is not verified<br />
																						User has does not have a signup record<br />
																						Three-day countdown will expire on $threeDayDate<br />
																						If not verified will recommend deleting username<br />";
									
									}
								}
								if (!$signupRecord && !$verifiedUser && !$validFormat && !$tempRegister && $tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NNNNYY: no signup record, unverified, invalid format, no tempRegister, tempIgnore is set, emailSignup is set<br />
										Have seen this record before. temmpIgnore is set. No action taen<br />";
									}
								}
								if (!$signupRecord && !$verifiedUser && !$validFormat && !$tempRegister && $tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NNNNYN: no signup record, unverified, invalid format, no tempRegister, tempIgnore is set, no emailSignup<br />
										Have seen this record before. tempIgnore is set. No action taken<br />";
									}
								}
								if (!$signupRecord && !$verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore && $emailSignup) { 
									if ($doDebug) {
										$debugData .= "NNNNNY: no signup record, unverified, invalid format, no tempRegister, no tempIgnore, emailSignup is set<br />
										<br />";
									}
								}
								if (!$signupRecord && !$verifiedUser && !$validFormat && !$tempRegister && !$tempIgnore && !$emailSignup) {
									if ($doDebug) {
										$debugData .= "NNNNNN: no signup record, unverified, invalid format, no tempRegister, no tempIgnore, no emailSignup<br />
										First time with this record. User obtained an invalid username and has not validated. No signup record 
										found. Set a three-day timer. If not verified by then, recommend deleting the user<br />";
									}
									$allUsersArray[$user_uppercase]['hasError']	= 'Y';
									$allUsersArray[$user_uppercase]['theError']	.= "User has an invalid username<br />
																					Username is not verified<br />
																					User has does not have a signup record<br />
																					Three-day countdown will expire on $threeDayDate<br />
																					If not verified will recommend deleting username<br />";
									$setTempRegisterArray[]						= "$username&$user_role";
								}

							}
						}
					}
				}
			}
		}			// end of checking usernames

		// see if there are any students or advisor records with no corresponding user record
		// start with advisors
		if ($doDebug) {
			echo "<br /><b>Looking for advisor anomolies</b><br />";
		}
		$missingAdvisorUserName	= FALSE;
		$sql					= "select * from $advisorTableName 
								where (semester = '$currentSemester' 
										or semester = '$nextSemester' 
										or semester = '$semesterTwo' 
										or semester = '$semesterThree' 
										or semester = '$semesterFour') 
										order by call_sign";
		$advisorResult		= $wpdb->get_results($sql);
		if ($advisorResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br /> and retrieved $numARows rows<br />";
			}
			if ($numARows > 0) {
				foreach($advisorResult as $advisorResultRow) {
					$advisor_call_sign		= $advisorResultRow->call_sign;
					$advisor_last_name		= $advisorResultRow->last_name;
					$advisor_first_name		= $advisorResultRow->first_name;
					$advisor_email			= $advisorResultRow->email;
					$date_created			= $advisorResultRow->date_created;
					$advisor_semester		= $advisorResultRow->semester;
					$advisor_email			= $advisorResultRow->email;
					$advisorDateCreated		= $advisorResultRow->date_created;

					$allUsersArray[$advisor_call_sign]	= array('last_name'=>$advisor_last_name, 
															 'first_name'=>$advisor_first_name, 
															 'display_name'=>'', 
															 'user_registered'=>'', 
															 'user_email'=>$advisor_email, 
															 'id'=>0, 
															 'user_role'=>'advisor',
															 'tempIgnoreID'=>0,
															 'tempRegisterID'=>0, 
															 'hasError'=>'N', 
															 'theError'=>"Advisor signup created on $advisorDateCreated<br />");
					
					// see if there is a user record for this callsign
					if(!array_key_exists($advisor_call_sign,$allUsersArray)) {
						// no username record
						$advisorNoUsername++;
						$missingAdvisorUserName			= TRUE;
						if ($doDebug) {
							echo "<b>$advisor_call_sign No username record</b><br />";
						}
						$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
						$allUsersArray[$advisor_call_sign]['theError']	.= "No username found for $advisor_call_sign<br />
					}
					// see if there is a tempRegister or a tempIgnore record
					$gottempRegister	= FALSE;
					$gotTempIgnore		= FALSE;
					$gotTempRecord		= FALSE;
					$tempSQL			= "select * from wpw1_cwa_temp_data 
											where callsign = '$advisor_call_sign' and 
												  (token = 'register' or 
												   token = 'ignore') 
											order by date_written";
					$tempResult			= $wpdb->get_results($tempSQL);
					if ($tempResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numTempRows	= $wpdb->num_rows;
						if ($doDebug)  {
							$debugData .= "ran $tempSQL<br />and retrieved $numTempRows rows<br />";
						}
						if ($numTempRows > 0) {
							$gotTempRecord	= TRUE;
							foreach ($tempResult as $tempResultRow) {
								$tempID			= $tempResultRow->record_id;
								$tempData		= $tempResultRow->temp_data;
								$date_written	= $tempResultRow->date_written;
								
								if ($tempData == 'register') {
									$tempRegister1			= TRUE;
									$allUsersArray[$advisor_call_sign]['tempRegsiterID']	= $tempID;	 
									$myInt					= strtotime("$date_written + 3 days");
									$threeDayDate1			= date('Y-m-d H:i:s',$myInt);
									if ($nowDate < $threeDayDate1) {
										$threeDayPlus		= TRUE;
									}
									$myInt					= strtotime("$date_written + 10 days");
									$tenDayDate1			= date('Y-m-s H:i:s',$myInt);
									if ($nowDate < $tenDayDate1) {
										$tenDayPlus1		= TRUE;
									}
								} elseif ($tempData == 'ignore') {
									$tempIgnore1			= TRUE;
									$allUsersArray[$advisor_call_sign]['tempIgnoreID']	= $tempID;	 
								}
							}
						}
					}
					if ($missingAdvisorUserName) {
						if (!$gotTempRegister1 && !$gotTempIgnore1) {
							if ($doDebug) {
								$debugData	.= "Checking Advisor Signups. No Username, no tempRegister, no tempIgnore<br />
												Seeing this record for the first time. Send register email. Set tempRegister 
												for a ten-day countdown<br />";
							}
							$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
							$allUsersArray[$advisor_call_sign]['theError']	.= "$advisor_call_sign does not have a username record<br />
																				First time with this record<br />
																				Sending register email<br />
																				Ten-day countdown timer will expire on $tenDayDat1<br />";
							$sendRegisterEmailArray[]						= $advisor_email;
							$setTempRegisterArray[]							= "$advisor_call_sign&$advisor";
						}
						if ($gotTempRegister1 && !$gotTempIgnore1) {
							if ($doDebug) {
								$debugData	.= "Checking Advisor Signups. No username, tempRegister is set, no tempIgnore<br />
												Have seen this record before and set the ten-day timer. If timer has expired 
												Recommend setting tempIgnore<br />";
							}
							if ($tenDayPlus1) {
								$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
								$allUsersArray[$advisor_call_sign]['theError']	.= "$advisor_call_sign does not have a username record<br />
																					Register email request has been sent<br />
																					Ten-day countdown timer expired on $tenDayDay1<br />
																					User has not registerd<br />
																					Recommend ignoring the error<br />";
							} else {
								$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
								$allUsersArray[$advisor_call_sign]['theError']	.= "$advisor_call_sign does not have a username record<br />
																					Register email has been sent<br />
																					Ten-day countdown timer will expire on $tenDayDay1<br />";
							
							}							
						}
						if (!$gotTempRegister1 && $gotTempIgnore1) {
							if ($doDebug) {
								$debugData	.= "Checking Advisor Signups. No username, no tempRegister, tempIgnore is set<br />
												tempIgnore is set. No further action<br />";
							}
						}
						if ($gotTempRegister1 && $gotTempIgnore1) {
							if ($doDebug) {
								$debugData	.= "Checking Advisor Signups. No username, tempRegister is set, tempIgnore is set<br />
												Program Error. Delete tempRegister and tempIgnore<br />";
							}
							$allUsersArray[$advisor_call_sign]['hasError']	= 'Y';	 
							$allUsersArray[$advisor_call_sign]['theError']	.= "$advisor_call_sign does not have a username record<br />
																				Register email has been sent<br />
																				Both the ten-day countdown timer and ignore are set. Program Error!<br />
																				Deleting both temp_data Register and temp_data Ignore<br />";
							$deleteTempRegisterArray[]						= "$advisor_call_sign&$advisor";
							$deleteTempIgnore								= "$advisor_call_sign&$advisor";
						}
					} else {			/// have a matching username record
						if ($tempIgnore1) {
							$deleteTempIgnoreArray[]				= "$advisor_call_sign&$advisor";
						}
						if ($tempRegsiter1) {
							$deleteTempRegisterArray[]				= "$advisor_call_sign&$advisor";
						}
					}
				}
			}
		}

		// now do students
		if ($doDebug) {
			echo "<br /><b>Looking for student anomolies</b><br />";
		}
		$missingStudentUserNaem	= FALSE;
		$sql					= "select * from $studentTableName 
									where (semester = '$currentSemester' 
											or semester = '$nextSemester' 
											or semester = '$semesterTwo' 
											or semester = '$semesterThree' 
											or semester = '$semesterFour') 
									and response = 'Y' 
									and (student_status = 'S' or student_status = 'Y')";
		$studentResult		= $wpdb->get_results($sql);
		if ($studentResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br /> and retreived $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach($studentResult as $studentResultRow) {
					$student_call_sign		= $studentResultRow->call_sign;
					$student_last_name		= $studentResultRow->last_name;
					$student_first_name		= $studentResultRow->first_name;
					$student_email			= $studentResultRow->email;
					$student_response		= $studentResultRow->response;
					$student_semester		= $studentResultRow->semester;
					$date_created			= $studentResultRow->date_created;
					$student_email			= $studentResultRow->email;
					$studentRequestDate		= $studentResultRow->request_date;

					$allUsersArray[$student_call_sign]	= array('last_name'=>$student_last_name, 
																 'first_name'=>$student_first_name, 
																 'display_name'=>'', 
																 'user_registered'=>'', 
																 'user_email'=>$student_email, 
																 'id'=>0, 
																 'user_role'=>'student', 
																 'tempRegisterID'=>0,
																 'tempIgnoreID'=>0,
																 'hasError'=>'N', 
																 'theError'=>"Student request date was $studentRequestDate<br />")
					// see if there is a username record
					if (!array_key_exists($student_call_sign,$allUsersArray)) {
						// no username record
						$missingStudentUserName		= TRUE;
						if ($doDebug) {
							echo "<b>$student_call_sign No username record</b><br />";
						}
						$studentNoUsername++;
						$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
						$allUsersArray[$student_call_sign]['theError']	.= "No username found for $student_call_sign<br />
					}	
					// see if there is a tempRegister or a tempIgnore record
					$gottempRegister	= FALSE;
					$gotTempIgnore		= FALSE;
					$gotTempRecord		= FALSE;
					$tempSQL			= "select * from wpw1_cwa_temp_data 
											where callsign = '$student_call_sign' and 
												  (token = 'register' or 
												   token = 'ignore') 
											order by date_written";
					$tempResult			= $wpdb->get_results($tempSQL);
					if ($tempResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numTempRows	= $wpdb->num_rows;
						if ($doDebug)  {
							$debugData .= "ran $tempSQL<br />and retrieved $numTempRows rows<br />";
						}
						if ($numTempRows > 0) {
							$gotTempRecord	= TRUE;
							foreach ($tempResult as $tempResultRow) {
								$tempID			= $tempResultRow->record_id;
								$tempData		= $tempResultRow->temp_data;
								$date_written	= $tempResultRow->date_written;
								
								if ($tempData == 'register') {
									$tempRegister1			= TRUE;
									$allUsersArray[$student_call_sign]['tempRegsiterID']	= $tempID;	 
									$myInt					= strtotime("$date_written + 3 days");
									$threeDayDate1			= date('Y-m-d H:i:s',$myInt);
									if ($nowDate < $threeDayDate1) {
										$threeDayPlus		= TRUE;
									}
									$myInt					= strtotime("$date_written + 10 days");
									$tenDayDate1			= date('Y-m-s H:i:s',$myInt);
									if ($nowDate < $tenDayDate1) {
										$tenDayPlus1		= TRUE;
									}
								} elseif ($tempData == 'ignore') {
									$tempIgnore1			= TRUE;
									$allUsersArray[$student_call_sign]['tempIgnoreID']	= $tempID;	 
								}
							}
						}
					}
					if ($missingStudentUserName) {
						if (!$gotTempRegister1 && !$gotTempIgnore1) {
							if ($doDebug) {
								$debugData	.= "Checking Student Signups. No Username, no tempRegister, no tempIgnore<br />
												Seeing this record for the first time. Send register email. Set tempRegister 
												for a ten-day countdown<br />";
							}
							$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
							$allUsersArray[$student_call_sign]['theError']	.= "$student_call_sign does not have a username record<br />
																				First time with this record<br />
																				Sending register email<br />
																				Ten-day countdown timer will expire on $tenDayDat1<br />";
							$sendRegisterEmailArray[]							= $student_email;
							$setTempRegisterArray[]								= "$student_call_sign&$advisor";
						}
						if ($gotTempRegister1 && !$gotTempIgnore1) {
							if ($doDebug) {
								$debugData	.= "Checking Student Signups. No username, tempRegister is set, no tempIgnore<br />
												Have seen this record before and set the ten-day timer. If timer has expired 
												Recommend setting tempIgnore<br />";
							}
							if ($tenDayPlus1) {
								$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
								$allUsersArray[$student_call_sign]['theError']	.= "$student_call_sign does not have a username record<br />
																					Register email request has been sent<br />
																					Ten-day countdown timer expired on $tenDayDay1<br />
																					User has not registerd<br />
																					Recommend ignoring the error<br />";
							} else {
								$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
								$allUsersArray[$student_call_sign]['theError']	.= "$student_call_sign does not have a username record<br />
																					Register email has been sent<br />
																					Ten-day countdown timer will expire on $tenDayDay1<br />";
							
							}							
						}
						if (!$gotTempRegister1 && $gotTempIgnore1) {
							if ($doDebug) {
								$debugData	.= "Checking Student Signups. No username, no tempRegister, tempIgnore is set<br />
												tempIgnore is set. No further action<br />";
							}
						}
						if ($gotTempRegister1 && $gotTempIgnore1) {
							if ($doDebug) {
								$debugData	.= "Checking Student Signups. No username, tempRegister is set, tempIgnore is set<br />
												Program Error. Delete tempRegister and tempIgnore<br />";
							}
							$allUsersArray[$student_call_sign]['hasError']	= 'Y';	 
							$allUsersArray[$student_call_sign]['theError']	.= "$student_call_sign does not have a username record<br />
																				Register email has been sent<br />
																				Both the ten-day countdown timer and ignore are set. Program Error!<br />
																				Deleting both temp_data Register and temp_data Ignore<br />";
							$deleteTempRegisterArray[]						= "$student_call_sign&$advisor";
							$deleteTempIgnoreArray[]						= "$student_call_sign&$advisor";
						}
					} else {			/// have a matching username record
						if ($tempIgnore1) {
							$deleteTempIgnoreArray[]						= "$student_call_sign&$advisor";
						}
						if ($tempRegsiter1) {
							$deleteTempRegisterArray[]						= "$student_call_sign&$advisor";
						}
					}
				}
			}
		}
///// All processing done. Do the requested actions

		foreach($sendSignupEmailArray as $thisEmail) {							
			if ($doDebug) {
				echo "Sending email to $thisEmail<br />";
			}
			$thisRole		= ucfirst($user_role);
			if ($user_role == 'student') {
				$article	= 'a';
				$textStr	= "take a class";
			} else {
				$article	= 'an';
				$textStr	= "be an advisor";
			}	
			$theSubject		= "CW Academy -- Missing $thisRole Sign up Information";
			$theContent		= "You recently obtained a username and password for the 
CW Academy website, but did not sign for a class. Obtaining a CW Academy username and password does 
not automatically sign you up for $article $user_role class. Please go to <a href='$siteURL/progran_list/'>CW 
Academy</a> enter your username and password, and sign up by clicking on the 'Sign up' button.<br />73,<br />CW Academy";
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$thisEmail,
														'theSubject'=>$theSubject,
														'theContent'=>$theContent,
														'theCc'=>'',
														'theAttachment'=>'',
														'mailCode'=>13,
														'jobname'=>$jobname,
														'increment'=>0,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug));
		}
 
		foreach($sendRegisterEmailArray as $thisEmail) {
			if ($doDebug) {
				echo "Sending email to $thisEmail<br />";
			}
			$thisRole		= ucfirst($user_role);
			if ($user_role == 'student') {
				$article	= 'a';
				$textStr	= "take a class";
			} else {
				$article	= 'an';
				$textStr	= "be an advisor";
			}	
			$theSubject	 	= "CW Academy -- Please Set Up your Username and Password for CW Academy";
			$theContent		= "<p>Since you signed up to $textStr, CW Academy has implemented a new 
user management system which will further isolate your personal information from the Internet. In order 
to have access to the CW Academy website, you will need to obtain a username and a password.</p>
<p>Please go to the <a href='https://cwa.cwops.org/program_list/'>CW Academy</a> and set up 
your username and password. <b>NOTE!</b> Your username MUST be your amateur radio callsign, or, 
if you don't have a callsign, it must be your last name.</p><br />73,<br />CW Academy";
			}
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$user_email,
														'theSubject'=>$theSubject,
														'theContent'=>$theContent,
														'theCc'=>'',
														'theAttachment'=>'',
														'mailCode'=>13,
														'jobname'=>$jobname,
														'increment'=>0,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug));
		}
		
		
		
		
		foreach ($setTempRegisterArray as $thisData) {
			if ($doDebug) {
				echo "adding temp_data record<br >";
			}
			$myArray			= explode("&",$thisData);
			$thisCallSign		= $myArray[0];
			$thisRole			= $myArray[1];
			$tempResult			= $wpdb->insert('wpw1_cwa_temp_data', 
										array('callsign'=>$thisCallSign, 
												'token'=>'register', 
												'temp_data'=>$thisRole, 
												'date_written'=>$nowDate),
										array('%s','%s','%s','%s'));
			if ($tempResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				if ($doDebug) {
					echo "added $user_login Register to temp_data<br />";
				}
				$tempDataAdded++;
			}
		}
		
		foreach($deleteTempRegisterArray as $thisData) {
			if ($doDebug) {
				echo "Delete the temp_data";
			}
			$myArray			= explode("&",$thisData);
			$thisCallSign		= $myArrray[0];
			$thisRole			= $myArray[1];
			delete_temp_record($thisCallSign, 'register');
			$tempDataDeleted++;
		}

		foreach($deleteTempIgnorerArray as $thisData) {
			if ($doDebug) {
				echo "Delete the temp_data";
			}
			$myArray			= explode("&",$thisData);
			$thisCallSign		= $myArrray[0];
			$thisRole			= $myArray[1];
			delete_temp_record($thisCallSign, 'ignore');
			$tempDataDeleted++;
		}


		if ($setTempIgnore) {
			if ($doDebug) {
				echo "adding temp_data record<br >";
			}
			$tempResult			= $wpdb->insert('wpw1_cwa_temp_data', 
										array('callsign'=>$user_login, 
												'token'=>'ignore', 
												'temp_data'=>$user_role, 
												'date_written'=>$nowDate),
										array('%s','%s','%s','%s'));
			if ($tempResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				if ($doDebug) {
					echo "added $user_login Ignore to temp_data<br />";
				}
				$tempDataAdded++;
			}

		if ($deleteUser) {
			$result		= wp_delete_user( $user_id );
			if ($result === FALSE) {
				if ($doDebug) {
					echo "deleting username failed<br />";
				}
				$allUsersArray[$user_uppercase]['hasError']	= 'Y';
				$allUsersArray[$user_uppercase]['theError']	.= 'Deleting username record failed<br />';
			} else {
				if ($doDebug) {
					echo "Deleted username<br />";
				}
				$allUsersArray[$user_uppercase]['hasError']	= 'Y';
				$allUsersArray[$user_uppercase]['theError']	.= 'Username record deleted<br />';
				$tempDataDeleted++;
				$usernamesDeleted++;
			}
		}	
	}										
}
}
$content			.= "</table>
				<p>Clicking on the email address will open a new email message</p>";
}
}


		// display the error arrays
		if (count($allUsersArray) > 0) {
			if ($doDebug) {
				echo "<br />have allUsersArray data to display<br />";
			}
			ksort($allUsersArray);
			$myCount	= 0;
			$content	.= "<h4>Errors Encountered</h4>
							<table style=width:1000px;'>
							<tr><th>Role</th>
								<th>Callsign</th>
								<th>Name</th>
								<th>Email</th>
								<th>Errors</th>
								<th>Delete ID</th></tr>";
			foreach($allUsersArray as $thisUser => $userData) {
				if ($allUsersArray[$thisUser]['hasError'] == 'Y') {
					$thisRole		= $allUsersArray[$thisUser]['user_role'];
					$thisLastName	= $allUsersArray[$thisUser]['last_name'];
					$thisFirstName	= $allUsersArray[$thisUser]['first_name'];
					$thisEmail		= $allUsersArray[$thisUser]['user_email'];
					$theErrors		= $allUsersArray[$thisUser]['theError'];
					$userID			= $allUsersArray[$thisUser]['id'];
					
					if ($thisRole == 'Advisor') {
						$thisLink		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$thisUser&request_table=$advisorTableName&strpass=2' target='_blank'>$thisUser</a>";
						$emailLink		= "<a href='$advisorUpdateURL?request_type=email&request_info=$thisEmail&request_table=$advisorTableName&strpass=2' target='_blank'>$thisEmail</a>";
					} elseif ($thisRole == 'student') {
						$thisLink		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$thisUser&request_table=$studentTableName&strpass=2' target='_blank'>$thisUser</a>";
						$emailLink		= "<a href='$studentUpdateURL?request_type=email&request_info=$thisEmail&request_table=$studentTableName&strpass=2' target='_blank'>$thisEmail</a>";
					} else {
						$thisLink		= $thisUser;
						$emailLink		= $thisEmail;
					}
					$deleteIDLink	= "<a href='$siteURL/cwa-delete-user-info/?inp_type=id&inp_value=$userID&strpass=2' target='_blank'>Delete User</a>";
					$content		.= "<tr><td style='vertical-align:top;'>$thisRole</td>
											<td style='vertical-align:top;'>$thisLink</td>
											<td style='vertical-align:top;'>$thisLastName, $thisFirstName</td>
											<td style='vertical-align:top;'>$emailLink</td>
											<td style='vertical-align:top;'>$theErrors</td>
											<td style='vertical-align:top;'>$deleteIDLink</td></tr>";
					$myCount++;
				}
			}
			$content			.= "</table>$myCount Errors Displayed<br />Clicking on the email address searches the Display and Update records by email address<br /><br />";
		}
		if ($rkslist) {		// print out list of email addresses
			$content	.= "<h4>Email Addresses Needing Followup</h4>";
			foreach ($rkslistArray as $thisValue) {
				$content	.= "$thisValue\n<br />";
			}
			$content		.= "<br />";
		}



				


		// read the temp_data table and see if any of those records should be deleted
		// if the user_login has a signup record, delete the temp_data record
		$tempDataDeleted = 0;
		$tempSql		= "select * from wpw1_cwa_temp_data 
							where token = 'register' 
							order by callsign";
		$tempResult		= $wpdb->get_results($tempSql);
		if ($tempResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($numRows > 0) {
				foreach($tempResult as $tempResultRow) {
					$temp_id			= $tempResultRow->record_id;
					$temp_callsign		= strtoupper($tempResultRow->callsign);
					$temp_token			= $tempResultRow->token;
					$temp_data			= $tempResultRow->temp_data;
					$temp_date_written	= $tempResultRow->date_written;
					
					$doContinue			= TRUE;
					// see if there is a signup record
					if ($temp_data == 'student') {
						$tempStr		= 'wpw1_cwa_consolidated_student';
					} elseif ($temp_data == 'advisor') {
						$tempStr		= 'wpw1_cwa_consolidated_advisor';
					} else {
						$doContinue		= FALSE;
					}
					if ($doContinue) {
						$thisSQL		= "select count(call_sign) 
											from $tempStr 
											where call_sign = '$temp_callsign' 
											and (semester = '$currentSemester' 
													or semester = '$nextSemester' 
													or semester = '$semesterTwo' 
													or semester = '$semesterThree' 
													or semester = '$semesterFour')";
						$thisCount			= $wpdb->get_var($thisSQL);
						if ($thisCount == NULL || $thisCount == 0) {		// no record
							if ($doDebug) {
								echo "no signup record found for temp_data $temp_callsign<br />";
							}
						} else {					/// signup found. Delete the temp_data record
							delete_temp_record($temp_callsign,$temp_token);
							$tempDataDeleted++;
							$newSignup++;
						}
					}
				}
			}
		}
		
		

		$content	.= "<h4>Counts</h4>
						$userLoginCount: User Login Records<br />
						$newRegistrations: New User Registrations in Past 36 Hours<br /><br />
						$userUnverifiedCount: User Records that are Unverified<br />
						$userUnverifiedDeleted: Unverified User Records Deleted<br />
						$badUserNameCount: Usernames with invalid format<br />
						$advisorNoSignup: Advisors with no signup record<br />
						$studentNoSignup: Students with no signup record<br />
						$advisorNoUsername: Advisor Records with no Corresponding Username<br />
						$newSignup: Users who have responded to signup requests<br />
						$studentNoUsername: Student Records with no Corresponding Username<br /><br />
						$signupEmailCount: Emails sent<br />
						$usernamesDeleted: Username records deleted<br />
						$tempDataAdded: TempData records added<br />
						$tempDataDeleted: TempData records deleted<br />";
		

		$endingMicroTime = microtime(TRUE);
		$elapsedTime	= $endingMicroTime - $startingMicroTime;
		$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
		$content		.= "<br /><p>Report V$versionNumber pass 1 took $elapsedTime seconds to run</p>";
		$nowDate		= date('Y-m-d');
		$nowTime		= date('H:i:s');
		$thisStr		= 'Production';
		if ($testMode) {
			$thisStr	= 'Testmode';
		}
		$ipAddr			= get_the_user_ip();
		$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|1: $elapsedTime|$ipAddr");
		if ($result == 'FAIL') {
			$content	.= "<p>writing to joblog.txt failed</p>";
		}
		
		/// if run thru cron, save the report and set up reminders otherwise display the report
		if ($runByCron) {
			// store the report in the reports table
			$storeResult	= storeReportData_v2($jobname,$content,$testMode,$doDebug);
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
			$reminder_text	= "<p>To view the New Registrations report for $nowDate $nowTime, click <a href='$siteURL/cwa-display-saved-report/?strpass=3&inp_callsign=WR7Q&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
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
			$reminder_text	= "<p>To view the New Registrations report for $nowDate $nowTime, click <a href='$siteURL/cwa-display-saved-report/?strpass=3&inp_callsign=K7OJL&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
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
		} else {
			return $content;
		}
	}
}
add_shortcode ('list_new_registrations_v3','list_new_registrations_v3_func');
