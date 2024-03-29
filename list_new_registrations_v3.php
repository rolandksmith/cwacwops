function list_new_registrations_v3_func(){

/*	List new registrations and any issues with usernames

	// modified 26Dec23 by Roland to remove usernames that never create a sign up record
	
*/

	global $wpdb, $doDebug, $currentSemester, $nextSemester, $semesterTwo, 
			$semesterThree, $semesterFour, $userName, $jobname, $allUsersArray;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$rkslist						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];


	$versionNumber				 	= "3";
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
				echo "ran $sql<br />and retrieved $numRows rows<br />";
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
					$showDeleteID		= FALSE;
					$validFormat		= FALSE;
					$unverifiedUser		= FALSE;
					$verifiedUser		= FALSE;
					$signupRecord		= FALSE;
					$threeDaysPlus		= FALSE;
					$tenDaysPlus		= FALSE;
					$user_id			= $resultRow->id;
					$user_login			= $resultRow->user_login;
					$user_email			= $resultRow->user_email;
					$display_name		= $resultRow->display_name;
					$user_registered	= $resultRow->user_registered;
					
					if ($doDebug) {
						echo "<br />Processing $user_login<br />";
					}
					
					$myStr				= strtoupper($user_login);
					if (in_array($myStr,$bypassArray)) { 
						$doProceed 		= FALSE;
					}
					if ($doProceed) {
						$user_first_name			= '';
						$user_last_name				= 'N/A';
						$user_role					= '';
						$user_needs_verification	= FALSE;
						$unverifiedUser				= FALSE;
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
								echo "ran $metaSQL<br />and retrieved $numMRows rows<br />";
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
									$user_needs_verification	= TRUE;
									$unverifiedUser				= TRUE;
								} else {
									$verifiedUser				= TRUE;
								}
							}
						}
						
						if ($doDebug) {
							echo "First Name: $user_first_name<br />
									Last Name: $user_last_name<br />
									Role: $user_role<br />";
							if ($user_needs_verification) {
								echo "User_needs_verification: TRUE<br />";
							} else {
								echo "User_needs_verification: FALSE<br />";
							}
						}
						
						$myStr					= strtoupper($user_login);
						$allUsersArray[$myStr]	= array('last_name'=>$user_last_name, 
														 'first_name'=>$user_first_name, 
														 'display_name'=>$display_name, 
														 'user_registered'=>$user_registered, 
														 'user_email'=>$user_email, 
														 'id'=>$user_id, 
														 'user_role'=>$user_role, 
														 'hasError'=>'N', 
														 'theError'=>'');
						if ($doDebug) {
							echo "added $user_login to allUsersArray<br />";
						}
						$allUsersArray[$myStr]['theError']	.= "Username created $user_registered<br />";
						
						// if the user_role is blank, then delete the user
						if ($user_role == '') {
							if ($doDebug) {
								echo "user_role of $user_role is invalid. Deleting username<br />";
							}
							$allUsersArray[$myStr]['hasError']	.= "Y";
							$allUsersArray[$myStr]['theError']	.= "user_role invalid. Username deleted<br />";
							delete_user($user_id);
							$usernamesDeleted++;
						}
						
						
						// if user needs verification and more than 3 days ago, delete the
						// user record and be done with this record
						if ($user_needs_verification) {
							$userUnverifiedCount++;
							$allUsersArray[$myStr]['hasError']	= 'Y';
							$allUsersArray[$myStr]['theError']	.= 'Unverified user_login<br />';

							//	If the user_login is 3 days old, delete the record
							
							$myInt		= strtotime("$nowDate -3 days");
							$myDate		= date('Y-m-d H:i:s',$myInt);
							if ($doDebug) {
								echo "user needs verification. Checking if register date ($user_registered) is before $myDate<br />";
							}
							if ($user_registered <= $myDate) {
								if ($doDebug) {
									echo "will delete the user_login<br />";
								}
							
								$thisResult	= delete_user($user_id);
//	$thisResult	= TRUE;
								if ($thisResult === TRUE) {
									if ($doDebug) {
										echo "user_login is deleted<br />";
									}
									$userUnverifiedDeleted++;
									$allUsersArray[$myStr]['theError']	.= 'Unverified user has been deleted<br />';
									$usernamesDeleted++;
								} else {
									handleWPDBError($jobname,$doDebug);
									$allUsersArray[$myStr]['hasError']	= 'Y';
									$allUsersArray[$myStr]['theError']	.= 'Unverified user delete failed<br />';
								}
								$doProceed		= FALSE;
								if ($doDebug) {
									echo "done with this user<br />";
								}
							}
						} else {
							$allUsersArray[$myStr]['theError']	.= "User record is verified<br />";
						
						}
					
						if ($doProceed) {
							// do some checks on the username
							$badUserName			= FALSE;
							$alphaResult			= preg_match('/^[A-Za-z0-9]+$/',$user_login);
							if ($alphaResult == 1) {			// have a match
								if ($doDebug) {
									echo "$user_login passes the preg_match test<br />";
								}
								$betaResult		= preg_match('/^[A-Za-z]+$/',$user_login);
								if ($betaResult == 1) {		// it's alphabetic -- not a callsign
									// is username also the last name? if not, say so
									$mystr1				= strtoupper($user_login);
									$myStr2				= strtoupper($user_last_name);
									if ($mystr1 != $myStr2) {
										if ($doDebug) {
											echo "<b>ERROR</b> username $user_login is not a callsign and not last name of $user_last_name<br />";
										}
										$badUserName	= TRUE;
										$allUsersArray[$myStr]['hasError']	= 'Y';
										$allUsersArray[$myStr]['theError']	.= 'User_login does not match last name<br />';
									}
								} else {						// has numeric -- maybe a callsign
									$testCallsign		= preg_match('/^[a-zA-Z0-9]{1,3}[0-9][a-zA-Z0-9]{0,3}[a-zA-Z]+$/',$user_login);
									if ($testCallsign == 1) {		// fits the callsign regex
										if ($doDebug) {
											echo "user_login $user_login passed the callsign regex<br />";
										}
									} else {							
										if ($doDebug) {
											echo "<b>ERROR</b> username $user_login does not fit a callsign pattern<br />";
										}
										$badUserName		= TRUE;
										$allUsersArray[$myStr]['hasError']	= 'Y';
										$allUsersArray[$myStr]['theError']	.= 'User_login does not fit callsign pattern<br />';
									}
								}
							} else {
								if ($doDebug) {
									echo "<b>ERROR</b> $user_login does not pass the preg_match test<br />";
								}
								$badUserName			= TRUE;
								$allUsersArray[$myStr]['hasError']	= 'Y';
								$allUsersArray[$myStr]['theError']	.= 'User_login is not alphnumeric<br />';
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
													where call_sign = '$myStr'
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
											if ($doDebug) {
												echo "$user_login has a student signup record<br />";
											}
										}
									} else {
										// no signup record
										$studentNoSignup++;
										if ($doDebug) {
											echo "$user_login DOES NOT have a student signup record<br />";
										}
										$allUsersArray[$myStr]['hasError']	= 'Y';
										$allUsersArray[$myStr]['theError']	.= 'User does not have a student signup record<br />';
									}
								}
							} elseif ($user_role == 'advisor') {
								$advisorSQL		= "select * from $advisorTableName 
												where call_sign = '$myStr'
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
											if ($doDebug) {
												echo "$user_login has an advisor signup record<br />";
											}
										}
									} else {		// no signup record
										$advisorNoSignup++;
										if ($doDebug) {
											echo "$user_login DOES NOT have an advisor signup record<br />";
										}
										$allUsersArray[$myStr]['hasError']	= 'Y';
										$allUsersArray[$myStr]['theError']	.= 'User does not have an advisor signup record<br />';
									}
								}
							}
							if ($signupRecord) {
								$allUsersArray[$myStr]['theError']	.= 'User has a signup record<br />';
								}
	
							// if a recent registration, display
							$recentRegister			= FALSE;
							if ($user_registered >= $recents) {
								$recentRegister		= TRUE;
								$newRegistrations++;
								if ($doDebug) {
									echo "$user_login is a recent registration<br />";
								}
								$myStr				= strtoupper($user_login);
								$thisStr			= '';
								if (!$unverifiedUser) {
									if ($user_role == 'advisor') {
										$update			= "<a href='https://cwa.cwops.org/cwa-display-and-update-advisor-information/?request_type=callsign&request_info=$myStr&inp_table=advisor&strpass=2' target='_blank'>$user_login</a>'";
									} elseif ($user_role == 'student') {
										$update			= "<a href='https://cwa.cwops.org/cwa-display-and-update-student-information/?request_type=callsign&request_info=$myStr&request_table=wpw1_cwa_consolidated_student&strpass=2' target='_blank'>$user_login</a>";
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
					
							// get the temp_data record, if any
							$gotTempRecord		= FALSE;
							$tempSQL			= "select * from wpw1_cwa_temp_data 
													where callsign = '$myStr' and 
														  token = 'register'";
							$tempResult			= $wpdb->get_results($tempSQL);
							if ($tempResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$numTempRows	= $wpdb->num_rows;
								if ($doDebug)  {
									echo "ran $tempSQL<br />and retrieved $numTempRows rows<br />";
								}
								if ($numTempRows > 0) {
									$gotTempRecord	= TRUE;
									foreach ($tempResult as $tempResultRow) {
										$tempID			 = $tempResultRow->record_id;
										$date_written	= $tempResultRow->date_written;
									
										if ($doDebug) {
											echo "$user_login has a temp_data record<br />";
										}
										$myInt		= strtotime("$date_written +3 days");
										$myDate		= date('Y-m-d H:i:s',$myInt);
										if ($nowDate > $myDate) {
											$threeDaysPlus	= TRUE;
										}
										$myInt		= strtotime("$date_written +10 days");
										$myDate		= date('Y-m-d H:i:s',$myInt);
										if ($nowDate > $myDate) {
											$tenDaysPlus	= TRUE;
										}
										
									}
								} else {
									$gotTempRecord	= FALSE;
									$tempID			= '';
									if ($doDebug) {
										echo "$user_login DOES NOT have a temp_data record<br />";
									}
								}
							}

							if ($doDebug) {
								$myStr1			= "FALSE";
								if ($recentRegister) {
									$myStr1 	= "TRUE";
								}
								$myStr2			= "FALSE";
								if ($unverifiedUser) {
									$myStr2		= "TRUE";
								}
								$myStr3			= "FALSE";
								if ($validFormat) {
									$myStr3		= "TRUE";
								}
								$myStr4			= "FALSE";
								if ($gotTempRecord) {
									$myStr4		= "TRUE";
								}
								$myStr5			= "FALSE";
								if ($verifiedUser) {
									$myStr5		= "TRUE";
								}
								$myStr6			= "FALSE";
								if ($signupRecord) {
									$myStr6		= "TRUE";
								}
								$myStr7			= "FALSE";
								if ($threeDaysPlus) {
									$myStr8	= "TRUE";
								}
								$myStr8			= "FALSE";
								if ($tenDaysPlus) {
									$myStr8		= "TRUE";
								}
								echo "Logicals:<br />
										recentRegister: $myStr1<br />
										unverifiedUser: $myStr2<br />
										verifiedUser: $myStr5<br />
										validFormat: $myStr3<br />
										gotTempRecord: $myStr4<br />
										signupRecord: $myStr6<br />
										threeDaysPlus: $myStr7<br />
										tenDaysPlus: $myStr8<br />";
							}
							
							// new logicals
							$deleteUser				= FALSE;
							$addTempRecord			= FALSE;
							$sendReminder			= FALSE;
							$deleteTemp				= FALSE;
							$sendRedo				= FALSE;
							
							
							if ($verifiedUser && $validFormat && $gotTempRecord && $signupRecord) {	// 1
								if ($doDebug) {
									echo "YYYY No action<br />";
								}
							}
							if ($verifiedUser && $validFormat && $gotTempRecord && !$signupRecord) { // 2
								if ($doDebug) {
									echo "YYYYN if date-written more than 3 days old, delete temp record<br />";
								}
								if ($threeDaysPlus) {
									$deleteTemp		= TRUE;
								}
								$allUsersArray[$myStr]['hasError']	= 'N';

							}
							if ($verifiedUser && $validFormat && !$gotTempRecord && $signupRecord) { // 3
								if ($doDebug) {
									echo "YYNY No action<br />";
								}
							}
							if ($verifiedUser && $validFormat && !$gotTempRecord && !$signupRecord) { // 4
								if ($doDebug) {
									echo "YYNN Add temp record, send reminder email<br >";
								}
								$addTempRecord		= TRUE;
								$sendReminder		= TRUE;
							}
							if ($verifiedUser && !$validFormat && $gotTempRecord && $signupRecord) { // 5
								if ($doDebug) {
									echo "YNYY  No action<br >";
								}
							}
							if ($verifiedUser && !$validFormat && $gotTempRecord && !$signupRecord) { // 6
								if ($doDebug) {
									echo "YNYN If date_written more than 3 days old, delete user<br >";
								}
								if ($threeDaysPlus) {
									$deleteUser		= TRUE;
								} else {
									$allUsersArray[$myStr]['theError']	.= "Reminder email sent on $date_written<br />";
								
								}
 							}
							if ($verifiedUser && !$validFormat && !$gotTempRecord && $signupRecord) { // 7
								if ($doDebug) {
									echo "YNNY No action<br >";
								}
							}
							if ($verifiedUser && !$validFormat && !$gotTempRecord && !$signupRecord) { // 8
								if ($doDebug) {
									echo "YNNN send redo email, delete username<br >";
								}
								$sendRedo		= TRUE;
//								$deleteUser		= TRUE;
							}
							if (!$verifiedUser && $validFormat && $gotTempRecord && $signupRecord) { // 9
								if ($doDebug) {
									echo "NYYY Not possible .. ERROR  … delete temp record<br >";
								}
//								$deleteTemp			= TRUE;
							}
							if (!$verifiedUser && $validFormat && $gotTempRecord && !$signupRecord) { // 10
								if ($doDebug) {
									echo "NYYN If date_written more than 3 days old, delete user<br >";
								}
								if ($threeDaysPlus) {
//									$deleteUser		= TRUE;
								}
							}
							if (!$verifiedUser && $validFormat && !$gotTempRecord && $signupRecord) { // 11
								if ($doDebug) {
									echo "NYNY No action<br >";
								}
								
							}
							if (!$verifiedUser && $validFormat && !$gotTempRecord && !$signupRecord) { // 12
								if ($doDebug) {
									echo "NYNN Add temp record<br >";
								}
								$addTempRecord		= TRUE;
							}
							if (!$verifiedUser && !$validFormat && $gotTempRecord && $signupRecord) { // 13
								if ($doDebug) {
								echo "NNYY Not possible .. ERROR  … delete temp record<br >";
								}
								$deleteTemp			= TRUE;
							}
							if (!$verifiedUser && !$validFormat && $gotTempRecord && !$signupRecord) { // 14
								if ($doDebug) {
									echo "NNYN If temp record more than 3 days old, delete temp record, delete user<br >";
								}
								if ($threeDaysPlus) {
									$deleteTemp		= TRUE;
								}
							}
							if (!$verifiedUser && !$validFormat && !$gotTempRecord && $signupRecord) { // 15
								if ($doDebug) {
									echo "NNNY No action<br >";
								}
							}
							if (!$verifiedUser && !$validFormat && !$gotTempRecord && !$signupRecord) { // 16
								if ($doDebug) {
									echo "NNNN Add temp record<br >";
								}
								$addTempRecord		= TRUE;
							}
							if ($verifiedUser && $validFormat && !$signupRecord && $rkslist) {	// get list of email addresses
								if ($doDebug) {
									echo "gathering email addresses<br />";
								}
								$rkslistArray[]		= $user_email;
							}
							if ($sendReminder || $sendRedo) {							
								if ($doDebug) {
									echo "Sending email<br />";
								}
								$thisRole		= ucfirst($user_role);
								if ($user_role == 'student') {
									$article	= 'a';
									$textStr	= "take a class";
								} else {
									$article	= 'an';
									$textStr	= "be an advisor";
								}	
								if ($sendReminder) {
									$theSubject		= "CW Academy -- Missing $thisRole Sign up Information";
									$theContent		= "You recently obtained a username and password for the 
CW Academy website, but did not sign for a class. Obtaining a CW Academy username and password does 
not automatically sign you up for $article $user_role class. Please go to <a href='$siteURL/progran_list/'>CW 
Academy</a> and sign up by clicking on the 'Sign up' button.<br />73,<br />CW Academy";
								} else {			// send redo
									$theSubject	 	= "CW Academy -- Invalid Information Entered";
									$theContent		= "You recently obtained a username and password for the 
CW Academy website. However there were two issues. First, you entered an invalid username of $user_login. The 
username must be either your amateur radio callsign or your last name if you do not have a callsign. Secondly, 
you did not sign up for a class. Your username record has been deleted. If you are wanting to $textStr, 
then setup a new (valid) username and password, click on the verification link in the email CW Academy will send to you, 
and then log in and sign up.<br />73,<br />CW Academy";
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
//	$mailResult = TRUE;
								if ($mailResult !== FALSE) {
									$allUsersArray[$myStr]['hasError']	= 'Y';
									$allUsersArray[$myStr]['theError']	.= "Email reminder to signup sent to $user_email at $nowDate<br />";
									$signupEmailCount++;
								}
							}
							if ($addTempRecord) {
								if ($doDebug) {
									echo "adding temp_data record<br >";
								}
								$tempResult			= $wpdb->insert('wpw1_cwa_temp_data', 
															array('callsign'=>$user_login, 
																	'token'=>'register', 
																	'temp_data'=>$user_role, 
																	'date_written'=>$nowDate),
															array('%s','%s','%s','%s'));
								if ($tempResult === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									if ($doDebug) {
										echo "added $user_login to temp_data<br />";
									}
									$tempDataAdded++;
								}
							}
							if ($deleteTemp) {							
								if ($doDebug) {
									echo "Delete the temp_data";
								}
								delete_temp_record($user_login, 'register');
								$tempDataDeleted++;
							}

							if ($deleteUser) {
								$result		= wp_delete_user( $user_id );
								if ($result === FALSE) {
									if ($doDebug) {
										echo "deleting username failed<br />";
									}
									$allUsersArray[$myStr]['hasError']	= 'Y';
									$allUsersArray[$myStr]['theError']	.= 'Deleting username record failed<br />';
								} else {
									if ($doDebug) {
										echo "Deleted username<br />";
									}
									$allUsersArray[$myStr]['hasError']	= 'Y';
									$allUsersArray[$myStr]['theError']	.= 'Username record deleted<br />';
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

										
		// see if there are any students or advisor records with no corresponding user record
		// start with advisors
		if ($doDebug) {
			echo "<br /><b>Looking for advisor anomolies</b><br />";
		}
		$thisInt				= 0;
		$emailArray				= array();
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
					
					// see if there is a user record for this callsign
					if(!array_key_exists($advisor_call_sign,$allUsersArray)) {
						// no username record
						$advisorNoUsername++;
						if ($doDebug) {
							echo "<b>$advisor_call_sign No username record</b><br />";
						}
						$allUsersArray[$advisor_call_sign]	= array('last_name'=>$advisor_last_name, 
																 'first_name'=>$advisor_first_name, 
																 'display_name'=>'', 
																 'user_registered'=>'', 
																 'user_email'=>$advisor_email, 
																 'id'=>0, 
																 'user_role'=>'advisor', 
																 'hasError'=>'N', 
																 'theError'=>'user_login does not have a username record<br />');
																 
					}
				}
			}
		}

		// now do students
		if ($doDebug) {
			echo "<br /><b>Looking for student anomolies</b><br />";
		}
		$firstTime				= TRUE;
		$thisInt				= 0;
		$emailArray				= array();
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

					// see if there is a username record
					if (!array_key_exists($student_call_sign,$allUsersArray)) {
						// no username record
						if ($doDebug) {
							echo "<b>$student_call_sign No username record</b><br />";
						}
						$studentNoUsername++;
//						$allUsersArray[$student_call_sign]	= array('last_name'=>$student_last_name, 
//																	 'first_name'=>$student_first_name, 
//																	 'display_name'=>'', 
//																	 'user_registered'=>'', 
//																	 'user_email'=>$student_email, 
//																	 'id'=>0, 
//																	 'user_role'=>'student', 
//																	 'hasError'=>TRUE, 
//																	 'theError'=>'user_login does not have a username record<br />', 
//																	 'sendEmail'=>'Y', 
//																	 'sendIssue'=>"In order to manage your student information you need to go to <a href='$siteURL/register/'>CW Academy</a> and set up your username and password.<br />");

					}	
				}
			}
		}


		// read the temp_date table and see if any of those records should be deleted
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
