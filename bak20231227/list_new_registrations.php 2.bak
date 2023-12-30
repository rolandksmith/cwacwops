function list_new_registrations_func(){

	global $wpdb, $doDebug, $currentSemester, $nextSemester, $semesterTwo, 
			$semesterThree, $semesterFour, $userName, $jobname;

	$doDebug						= TRUE;
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
	
//	CHECK THIS!								//////////////////////
//	if ($validUser == "N") {
//		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
	$studentUpdateURL		= "$siteURL/cwa-display-and-update-student-information/";	
	$advisorUpdateURL		= "$siteURL/cwa-display-and-update-advisor-information/";	
	$jobname				= "List New Registrations V$versionNumber";
	$advisorTableName		= "wpw1_cwa_consolidated_advisor";
	$studentTableName		= "wpw1_cwa_consolidated_student";
	$tempTableName			= "wpw1_cwa_temp_data";
	$nowDate				= date('Y-m-d H:i:s');
	

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

			//Include the user file with the user administration API
			require_once( ABSPATH . 'wp-admin/includes/user.php' );

			//Delete a WordPress user by specifying its user ID. Here the user with an ID equal to $user_id is deleted.
			return wp_delete_user( $user_id );

		}
		
		function delete_temp_record($user_login,$token) {
			global $wpdb, $doDebug;
			
			$result	= $wpdb->delete('wpw1_cwa_temp_data',
									array('token'=>$token,
											'user_login'=>$user_login),
									array('%s','%s'));
			if ($result === FALSE) {
				$lastError		= $wpdb->last_error;
				$lastQuery		- $wpdb->last_query;
				if ($doDebug) {
					echo "List New Registrations attempting to delete 
user_login $user_login with token $token failed. Error: $lastError. Query: $lastQuery<br />";
				}
//				sendErrorEmail("List New Registrations attempting to delete 
// user_login $user_login with token $token failed. Error: $lastError. Query: $lastQuery");
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
		
		function delete_user_record($user_login,$user_role) {
			global $wpdb, $doDebug, $currentSemester, $nextSemester, $semesterTwo, 
				$semesterThree, $semesterFour, $userName, $jobname;

			
			if ($user_role == 'student') {
				$theUser			= strtoupper($user_login);
				$studentID			= $wdb->get_results("select student_id 
										from wpw1_cwa_consolidated_student 
										where call_sign = '$theUser' and 
											(semester = '$currentSemester or 
											semester = '$nextSemester' or 
											semester = '$semesterTwo' or 
											semester = '$semesterThree' or 
											semester = '$semesterFour')");
				if ($studentID === FALSE) {
					$lastError		= $wpdb->last_error;
					$lastQuery		= $wpdb->last_query;
					if ($doDebug) {
						echo "Trying to get an student record for $user_login failed. Error: $lastError<br />Query: $lastQuery<br />";
					}
					$content		.= "attempting to get student record for $user_login failed<br />";
				} else {
				 	$numRows		= $wpdb->num_rows;
				 	if ($doDebug) {
				 		echo "Ran $studentID and retrieved $numRows rows<br />";
				 	}
				 	if ($numRows > 0) {
				 		foreach($studentID as $thisRow) {
				 			$studentID	= $thisRow->student_id;

							// delete the record
							$studentUpdateData		= array('tableName'=>'wpw1_cwa_consolidated_student',
															'inp_method'=>'delete',
															'inp_data'=>array(''),
															'inp_format'=>array(''),
															'jobname'=>$jobname,
															'inp_id'=>$studentID,
															'inp_callsign'=>$user_login,
															'inp_semester'=>'',
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateStudent($studentUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $studentTableName<br />";
							}
						}
					}
				}
			} elseif ($user_role == 'advisor') {
				$theUser			= strtoupper($user_login);
				$advisorID			= $wdb->get_results("select advisor_id 
										from wpw1_cwa_consolidated_advisor 
										where call_sign = '$theUser' and 
											(semester = '$currentSemester or 
											semester = '$nextSemester' or 
											semester = '$semesterTwo' or 
											semester = '$semesterThree' or 
											semester = '$semesterFour')");
				if ($advisorID === FALSE) {
					$lastError		= $wpdb->last_error;
					$lastQuery		= $wpdb->last_query;
					if ($doDebug) {
						echo "Trying to get an advisor record for $user_login failed. Error: $lastError<br />Query: $lastQuery<br />";
					}
					$content		.= "attempting to get advisor record for $user_login failed<br />";
				} else {
					$numRows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "Ran $advisorID and retrieved $numRows rows<br />";
					}
					if ($numRows > 0) {
						foreach($advisorID as $thisRow) {
							$advisorID	= $thisRow->advisor_id;

							// delete the record
							$advisorUpdateData		= array('tableName'=>'wpw1_cwa_consolidated_advisor',
															'inp_method'=>'delete',
															'inp_data'=>array(''),
															'inp_format'=>array(''),
															'jobname'=>$jobname,
															'inp_id'=>$advisorID,
															'inp_callsign'=>$user_login,
															'inp_semester'=>'',
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateAdvisor($advisorUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Deleting $advisor_call_sign from $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $advisorTableName<br />";
							}
						}
					}
				}
			}
			return TRUE;
		}
	



		// get all recent registrations
		$myInt				 = strtotime("-12 days");
		$recents			= date('Y-m-d H:i:s',$myInt);
//		$recents			= "$recents 00:00:00";
	
		$registrantArray	= array();

		$content			.= "<h4>Username Issues</h4>
								<p>The following username and any associated signup records have been deleted</p>";							
		$sql				= "SELECT id, 
									   user_login, 
									   user_email
								FROM `wpw1_users` 
								WHERE user_registered >= '$recents'";
		$result				= $wpdb->get_results($sql);
		if ($result === FALSE) {
			$lastError		= $wpdb->last_error;
			if ($doDebug) {
				echo "running $sql returned false. $last_error<br />";
			}
		} else {
			$numRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($result as $resultRow) {
					$id				= $resultRow->id;
					$user_login		= $resultRow->user_login;
					$user_email		= $resultRow->user_email;
				
					$user_first_name	= '';
					$user_last_name		= 'N/A';
					$user_role			= '';
					$metaSQL		= "select meta_key, meta_value 
										from `wpw1_usermeta` 
										where user_id = $id 
										and (meta_key = 'first_name' 
											or meta_key = 'last_name' 
											or meta_key = 'wpw1_capabilities')";
					$metaResult		= $wpdb->get_results($metaSQL);
					if ($metaResult === FALSE) {
						$lastError	= $wpdb->last_error;
						if ($doDebug) {
							echo "running $metaSQL failed. $lastError<br />";
						}
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
						}
					}
					if ($doDebug) {
						echo "<br />Processing $user_login<br />";
					}
					// do some checks on the username
					$userNameError	= FALSE;
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
								$content		.= "username $user_login is not a callsign and not last name of $user_last_name<br />";
								$userNameError	= TRUE;
								if ($doDebug) {
									echo "<b>ERROR</b> username $user_login is not a callsign and not last name of $user_last_name<br />";
								}
							}
						} else {						// has numeric -- maybe a callsign
							$testCallsign		= preg_match('/^[a-zA-Z0-9]{1,3}[0-9][a-zA-Z0-9]{0,3}[a-zA-Z]+$/',$user_login);
							if ($testCallsign == 1) {		// fits the callsign regex
								if ($doDebug) {
									echo "user_login $user_login passed the callsign regex<br />";
								}
							} else {							
								$content		.= "username $user_login does not fit a callsign pattern<br />";
								$userNameError	= TRUE;
								if ($doDebug) {
									echo "<b>ERROR</b> username $user_login does not fit a callsign pattern<br />";
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "<b>ERROR</b> $user_login does not pass the preg_match test<br />";
						}
						$content				.= "Username $user_login did not pass the alphanumeric test<br />";
						$userNameError			= TRUE;
					}
					
					// if userNameError, send email and delete the user as well as any student or advisor records
					if ($userNameError) {
						if ($doDebug) {
							echo "sending email to $user_email<br />";
						}
						$theSubject		= "CW Academy -- Username Error. Record Deleted";
						$theContent		= "<p>To: $user_first_name $user_last_name</p>
<p>You recently registered on the CW Academy website. However, the 
username of $user_login you chose is invalid. The instructions on the registration page were very 
explicit:<br />
Your Username <strong>MUST</strong> be your Amateur Radio Callsign <strong>OR</strong> 
if you do not have a callsign, please use your last name as your Username. 
DO NOT use an email address or some combination of your name!</p>
<p>Your username and any associated records have been deleted. Please sign up 
again using either your amateur radio callsign or your last name.</p>
<p>73,<br />CW Academy</p>";
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
						$deleteUser		= delete_user($id);
						if ($deleteUser === FALSE) {
							if ($doDebug) {
								echo "deleting user id $id failed<br />";
							}
							$content			.= "Attempting to delete id $id for $user_login failed<br />";
						} else {
							if ($doDebug) {
								echo "deleted $id for $user_login<br />";
							}
						}
						// now find and delete any advisor/student records
						$deleteResult			= delete_user_record($user_login,$user_role);
						if ($doDebug) {
							echo "delete_user returned $deleteResult<br />";
						}
					} else {

						$display_name	= "$user_last_name, $user_first_name";
						$registrantArray[]	= "$user_role&$user_login&$display_name&$user_email";
					}
				}
			}
		}
return $content;
		if (count($registrantArray) > 0) {
			$content			.= "<h4>New Registrants since $recents</h4>
									<table style='width:auto;'>
									<tr><th>Role</th>
										<th>Call Sign</th>
										<th>Name</th>
										<th>Email</th>
										<th>Signup</th></tr>";
			sort($registrantArray);
			foreach($registrantArray as $thisValue) {
				$myArray			= explode("&",$thisValue);
				$thisRole			= $myArray[0];
				$thisUser			= $myArray[1];
				$thisName			= $myArray[2];
				$thisEmail			= $myArray[3];
				$signup				= '';

				if ($thisRole == 'administrator') {
					$update		= $thisUser;
				} elseif ($thisRole == 'student') {
					$update		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$thisUser&request_table=wpw1_cwa_consolidated_student&strpass=2' target='_blank'>$thisUser</a>";
					// see if there is a student signup record
					$student_level	= '';
					$student_semester = '';
					$studentSQL		= "select * from $studentTableName 
										where call_sign = '$thisUser'
										order by date_created DESC 
										limit 1";
					$studentResult	= $wpdb->get_results($studentSQL);
					if ($studentResult === FALSE) {
						$lastError	= $wpdb->last_error;
						if ($doDebug) {
							echo "running $studentSQL failed. Error: $lastError<br />";
						}
					} else {
						$numSRows	= $wpdb->num_rows;
						if ($numSRows > 0) {
							foreach($studentResult as $studentResultRow) {
								$student_semester	= $studentResultRow->semester;
								$student_level		= $studentResultRow->level;
								
								$signup				= "signed up for $student_level in $student_semester";
							}
						} else {
							//// no student record. Prepare to be able to delete
							// see if there is a temp data record for this
							$tempSQL			= "select * from $tempTableName 
													where callsign = '$thisUser' 
													and token = 'register'";
							$tempResult			= $wpdb->get_results($tempSQL);
							if ($tempResult	=== FALSE) {
								$lastError		= $wpdb->last_error;
								if ($doDebug) {
									echo "Attempting to run $tempSQL failed. Error: $lastError<br />";
								}
							} else {
								$tempNumRows	= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $tempSQL<br />and retrieved $tempNumRows rows<br />";
								}
								if ($tempNumRows > 0) {		// record exists
								
								} else {				// no record. add it
									$tempInsert				= $wpdb->insert($tempTableName,
																		array('callsign'=>$thisUser,
																			  'token'=>'register',
																			  'temp_data'=>'student',
																			  'date_written'=>$nowDate),
																		array('%s','%s','%s','%s'));
									if ($tempInsert === FALSE) {
										$lastError			= $wpdb->last_error;
										$lastQuery			= $wpdb->last_query;
										if ($doDebug) {
											echo "tried adding $tempDataTable record. Error: $lastError<br />SQL: $lastQuery<br />";
										}
									}
								}
							}
						}
					}
				} elseif ($thisRole == 'advisor') {
					$update		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$thisUser&inp_table=advisor&strpass=2' target='_blank'>$thisUser</a>";
					// see if there is an advisor signup record
					$student_level	= '';
					$student_semester = '';
					$advisorSQL		= "select * from $advisorTableName 
										where call_sign = '$thisUser'
										order by date_created DESC 
										limit 1";
					$advisorResult	= $wpdb->get_results($advisorSQL);
					if ($advisorResult === FALSE) {
						$lastError	= $wpdb->last_error;
						if ($doDebug) {
							echo "running $advisorSQL failed. Error: $lastError<br />";
						}
					} else {
						$numARows	= $wpdb->num_rows;
						if ($numARows > 0) {
							foreach($advisorResult as $advisorResultRow) {
								$advisor_semester	= $advisorResultRow->semester;
								
								$signup				= "signed up $advisor_semester";
							}
						} else {
							//// no advisor record. Prepare to be able to delete
							// see if there is a temp data record for this
							$tempSQL			= "select * from $tempTableName 
													where callsign = '$thisUser' 
													and token = 'register'";
							$tempResult			= $wpdb->get_results($tempSQL);
							if ($tempResult	=== FALSE) {
								$lastError		= $wpdb->last_error;
								if ($doDebug) {
									echo "Attempting to run $tempSQL failed. Error: $lastError<br />";
								}
							} else {
								$tempNumRows	= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $tempSQL<br />and retrieved $tempNumRows rows<br />";
								}
								if ($tempNumRows > 0) {		// record exists
								
								} else {				// no record. add it
									$tempInsert				= $wpdb->insert($tempTableName,
																		array('callsign'=>$thisUser,
																			  'token'=>'register',
																			  'temp_data'=>'advisor',
																			  'date_written'=>$nowDate),
																		array('%s','%s','%s','%s'));
									if ($tempInsert === FALSE) {
										$lastError			= $wpdb->last_error;
										$lastQuery			= $wpdb->last_query;
										if ($doDebug) {
											echo "tried adding $tempTableName record. Error: $lastError<br />SQL: $lastQuery<br />";
										}
									}
								}
							}
						}
					}
				}
			
				$content			.= "<tr><td>$thisRole</td>
											<td>$update</td>
											<td>$thisName</td>
											<td><a href='mailto:$thisEmail' target='_blank'>$thisEmail</a></td>
											<td>$signup</td></tr>";
			}
			$content				.= "</table>
										<p>Clicking on the email address will open a new email message</p>";
										
										
			// see if there are any students or advisor records with no corresponding user record
			// start with advisors
			if ($doDebug) {
				echo "<br /><b>Looking for advisor anomolies</b><br />";
			}
			$firstTime				= TRUE;
			$thisInt				= 0;
			$emailArray				= array();
			$sql					= "select * from $advisorTableName 
									where semester = '$currentSemester' 
											or semester = '$nextSemester' 
											or semester = '$semesterTwo' 
											or semester = '$semesterThree' 
											or semester = '$semesterFour' 
											order by call_sign";
			$advisorResult		= $wpdb->get_results($sql);
			if ($advisorResult === FALSE) {
				$lastError		= $wpdb->last_error;
				if ($doDebug) {
					echo "running $sql failed. Error: $lastError<br />";
				}
			} else {
				$numARows		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br /> and retrieved $numARows rows<br />";
				}
				if ($numARows > 0) {
					foreach($advisorResult as $advisorResultRow) {
						$advisor_call_sign		= $advisorResultRow->call_sign;
						$date_created			= $advisorResultRow->date_created;
						$advisor_semester		= $advisorResultRow->semester;
						$advisor_email			= $advisorResultRow->email;
						
						// see if there is a user record for this callsign
						$ucCallsign				= strtoupper($advisor_call_sign);
						$lcCallsign				= strtolower($advisor_call_sign);
						$userSQL				= "select count(user_login) from wpw1_users 
													where user_login = '$ucCallsign' 
													or user_login = '$lcCallsign'";
						$thisCount				= $wpdb->get_var($userSQL);
						if ($doDebug) {
							echo "checking advisor $ucCallsign ($lcCallsign) and found $thisCount rows ($userSQL)<br />";
						}
						
						if ($thisCount !== NULL && $thisCount == 0) {
							if ($firstTime) {
								$content		.= "<h4>Advisor Signup Anomalies</h4>";
								$firstTime		= FALSE;
							}
							$thisInt++;
							$emailArray[]		= $advisor_email;
							$content			.= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$advisor_call_sign&inp_table=advisor&strpass=2' target='_blank'>$advisor_call_sign</a> 
													has an advisor record created $date_created
													for $advisor_semester semester but no wpw1_user record<br />";
						}
					}
					if ($thisInt > 0) {
						$content				.= "$thisInt advisor anomolies found<br />";
						$content				.= "<br /><p>Advisor Anomoly Emails:<br />";
						foreach($emailArray as $thisValue) {
							$content			.= "$thisValue\n<br />";
						}
						$content				.= "<br />";
					}
				}
			}

			// now do students
			if ($doDebug) {
				echo "<br /><b>Looking for student anomolies</b><br />";
			}
			$proximateSemester		= $initializationArray['proximateSemester'];
			$firstTime				= TRUE;
			$thisInt				= 0;
			$emailArray				= array();
			$sql					= "select * from $studentTableName 
									where semester = '$proximateSemester' 
									and response = 'Y' 
									order by call_sign";
			$studentResult		= $wpdb->get_results($sql);
			if ($studentResult === FALSE) {
				$lastError		= $wpdb->last_error;
				if ($doDebug) {
					echo "running $sql failed. Error: $lastError<br />";
				}
			} else {
				$numSRows		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br /> and retreived $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					foreach($studentResult as $studentResultRow) {
						$student_call_sign		= $studentResultRow->call_sign;
						$student_response		= $studentResultRow->response;
						$student_semester		= $studentResultRow->semester;
						$date_created			= $studentResultRow->date_created;
						$student_email			= $studentResultRow->email;
						
						// see if there is a user record for this callsign
						$ucCallsign				= strtoupper($student_call_sign);
						$lcCallsign				= strtolower($student_call_sign);
						$userSQL				= "select count(user_login) from wpw1_users 
													where user_login = '$ucCallsign' 
													or user_login = '$lcCallsign'";
						$thisCount				= $wpdb->get_var($userSQL);
						if ($doDebug) {
							echo "ran $userSQL<br />and found $thisCount records<br />";
						}
						
						if ($thisCount !== NULL && $thisCount == 0) {
							if ($firstTime) {
								$content		.= "<h4>Student Signup Anomalies</h4>";
								$firstTime		= FALSE;
							}
							$thisInt++;
							$emailArray[]		= $student_email;
							$content			.= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&request_table=wpw1_cwa_consolidated_student&strpass=2' target='_blank'>$student_call_sign</a>
							 has an student record for $student_semester semester 
							 created on $date_created but no wpw1_user record<br />";
						}
					}
					if ($thisInt > 0) {
						$content				.= "$thisInt student anomolies found<br />";
						$content				.= "<br /><p>Student Anomoly Emails:<br />";
						foreach($emailArray as $thisValue) {
							$content			.= "$thisValue\n<br />";
						}
						$content				.= "<br />";
					}
				}
			}
			
			
			// see which users should either be deleted or get an email
			$tempSQL				= "select * from wpw1_cwa_temp_data 
										where token = 'register' 
										order by date_written";
			$tempResult				= $wpdb->get_results($tempSQL);
			if ($tempResult === FALSE) {
				$lastError			= $wpdb->last_error;
				$lastQuery			= $wpdb->last_query;
				if($doDebug) {
					echo "Trying to read wpw1_cwa_temp_data failed. Error $lastError<br />SQL $lastQuery<br />";
				}
			} else {
				foreach($tempResult as $tempResultRow) {
					$temp_record_id		= $tempResultRow->record_id;
					$temp_callsign		= $tempResultRow->callsign;
					$temp_token			= $tempResultRow->token;
					$temp_data			= $tempResultRow->temp_data;
					$temp_date_written	= $tempResultRow->date_written;
					
					// get name and email information
					$sql				= "SELECT id, 
												   user_login, 
												   user_email,
												   display_name 
											FROM `wpw1_users` 
											WHERE user_login = '$temp_callsign'";
					$result				= $wpdb->get_results($sql);
					if ($result === FALSE) {
						$lastError		= $wpdb->last_error;
						if ($doDebug) {
							echo "running $sql returned false. $last_error<br />";
						}
					} else {
						$numRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and retrieved $numRows rows<br />";
						}
						if ($numRows > 0) {
							foreach($result as $resultRow) {
								$id				= $resultRow->id;
								$user_login		= $resultRow->user_login;
								$user_email		= $resultRow->user_email;
								$user_name		= $resultRow->display_name;

								// see who should get email					
								$newTime		 	= strtotime("$temp_date_written +4 days");
								$newDate			= date('Y-m-d H:i:s',$newTime);
								$nowDate			= date('Y-m-d H:i:s');
								$testCallsign		= strtoupper($temp_callsign);
								$deleteThis			= TRUE;
								$emailThis			= TRUE;
								if ($newDate < $nowDate) {		// it's been at least 4 days. See if a signup record
									if ($testCallsign = 'F8TAM' || $testCallsign = 'BOBC') {
										$deleteThis	= FALSE;
									} elseif ($temp_data == 'student') {
										$studentSQL		= "select * from $studentTableName 
															where call_sign = '$testCallsign'
															order by date_created DESC 
															limit 1";
										$studentResult	= $wpdb->get_results($studentSQL);
										if ($studentResult === FALSE) {
											$lastError	= $wpdb->last_error;
											if ($doDebug) {
												echo "running $studentSQL failed. Error: $lastError<br />";
											}
										} else {
											$numSRows	= $wpdb->num_rows;
											if ($numSRows > 0) {
												$deleteThis	= FALSE;
//												if ($doDebug) {
													echo "username of $temp_callsign has signed up. Delete the temp record<br />";
//												}
												$deleteResult	= delete_temp_record($user_login,$temp_token);
											}
										}					
									} elseif ($temp_data == 'advisor') {
										$advisorSQL		= "select * from $advisorTableName 
															where call_sign = '$testCallsign'
															order by date_created DESC 
															limit 1";
										$advisorResult	= $wpdb->get_results($advisorSQL);
										if ($advisorResult === FALSE) {
											$lastError	= $wpdb->last_error;
											if ($doDebug) {
												echo "running $advisorSQL failed. Error: $lastError<br />";
											}
										} else {
											$numARows	= $wpdb->num_rows;
											if ($numARows > 0) {
												$deleteThis	= FALSE;
//												if ($doDebug) {
													echo "username of $temp_callsign has signed up. Delete the temp record<br />";
//												}
												$deleteResult	= delete_temp_record($user_login,$temp_token);
											}
										}
									}
									if ($deleteThis) {
//										if ($doDebug) {
											echo "username of $temp_callsign eligible to be deleted<br />";
//										}
									}
								} else{
									$newTime		 	= strtotime("$temp_date_written +2 days");
									$newDate			= date('Y-m-d H:i:s',$newTime);
									$nowDate			= date('Y-m-d H:i:s');
									if ($newDate < $nowDate) {		// it's been at least 2 days. See if a signup record
										$testCallsign	= strtoupper($temp_callsign);
										if ($testCallsign = 'F8TAM' || $testCallsign = 'BOBC') {
											$emailThis	= FALSE;
										} elseif ($temp_data == 'student') {
											$studentSQL		= "select * from $studentTableName 
																where call_sign = '$testCallsign'
																order by date_created DESC 
																limit 1";
											$studentResult	= $wpdb->get_results($studentSQL);
											if ($studentResult === FALSE) {
												$lastError	= $wpdb->last_error;
												if ($doDebug) {
													echo "running $studentSQL failed. Error: $lastError<br />";
												}
											} else {
												$numSRows	= $wpdb->num_rows;
												if ($numSRows > 0) {
													$emailThis	= FALSE;
//													if ($doDebug) {
														echo "username of $temp_callsign has signed up. Delete the temp record<br />";
//													}
													$deleteResult	= delete_temp_record($user_login,$temp_token);
												}
											}
										}
					
									} elseif ($temp_data == 'advisor') {
										$advisorSQL		= "select * from $advisorTableName 
															where call_sign = '$testCallsign'
															order by date_created DESC 
															limit 1";
										$advisorResult	= $wpdb->get_results($advisorSQL);
										if ($advisorResult === FALSE) {
											$lastError	= $wpdb->last_error;
											if ($doDebug) {
												echo "running $advisorSQL failed. Error: $lastError<br />";
											}
										} else {
											$numARows	= $wpdb->num_rows;
											if ($numARows > 0) {
												$emailThis	= FALSE;
//												if ($doDebug) {
													echo "username of $temp_callsign has signed up. Delete the temp record<br />";
//												}
												$deleteResult	= delete_temp_record($user_login,$temp_token);
											}
										}
									}
									if ($emailThis) {
//										if ($doDebug) {
											echo "username of $temp_callsign eligible for a 2-day email<br />";
//										}
									}
								}
							}
						}
					}
				}
			}
		}
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
add_shortcode ('list_new_registrations','list_new_registrations_func');

