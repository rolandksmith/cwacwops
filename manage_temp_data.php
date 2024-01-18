function manage_temp_data_func() {

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$currentTimestamp				= $initializationArray['currentTimestamp'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];
	$userName						= $initializationArray['userName'];
	$userEmail						= $initializationArray['userEmail'];
	$userDisplayName				= $initializationArray['userDisplayName'];
	$userRole						= $initializationArray['userRole'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	
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
		$wpdb->show_errors();
//	} else {
//		$wpdb->hide_errors();
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-manage-temp-data/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Manage Temp Data V$versionNumber";
	$inp_callsign				= '';
	$inp_role					= '';
	$token						= '';

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
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_role") {
				$inp_role	 = $str_value;
				$inp_role	 = filter_var($inp_role,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_action") {
				$inp_action	 = $str_value;
				$inp_action	 = filter_var($inp_action,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_temp_data") {
				$inp_temp_data	 = $str_value;
				$inp_temp_data	 = filter_var($inp_temp_data,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_date_written") {
				$inp_date_written	 = $str_value;
				$inp_date_written	 = filter_var($inp_date_written,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "tempID") {
				$tempID	 = $str_value;
				$tempID	 = filter_var($tempID,FILTER_UNSAFE_RAW);
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
		$tempDataTableName			= "wpw1_cwa_temp_data2";
	} else {
		$extMode					= 'pd';
		$tempDataTableName			= "wpw1_cwa_temp_data";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Action</td>
								<td><input type='radio' class='formInputButton'name='inp_action' value='add' checked required>Add<br />
									<input type='radio' class='formInputButton'name='inp_action' value='delete' required>Delete<br />
									<input type='radio' class='formInputButton'name='inp_action' value='modify' required>Modify</td></tr>
							<tr><td>Username</td>
								<td><input type='text' class='formInputText' size='15' maxlength='30' name='inp_callsign' required></td></tr>
							<tr><td>User Role</td>
								<td><input type='radio' class='formInputButton' name='inp_role' value='student' checked required>Student<br />
									<input type='radio' class='formInputButton' name='inp_role' value='advisor' required>Advisor<br />
									<input type='radio' class='formInputButton' name='inp_role' value='administrator' required>Administrator</td></tr>
							<tr><td>Token</td>
								<td><input type='text' class='formInputText' size='30' maxlength='50' name='token' required></td></tr>
							
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at xpass $strPass<br />
					inp_callsign: $inp_callsign<br />
					inp_role: $inp_role<br />
					token: $token<br />
					inp_action: $inp_action<br />";
		}
		$content	.= "<h3>$jobname</h3>";
		if ($inp_action == 'add') {
			if ($doDebug) {
				echo "adding temp_data record<br />";
			}
			$nowTime		= date('Y-m-d H:i:s');
			$addResult		= $wpdb->insert($tempDataTableName,
									array('callsign'=>$inp_callsign,
										'token'=>$token,
										'temp_data'=>$inp_role,
										'date_written'=>$nowTime),
									array('%s','%s','%s','%s'));
			if ($addResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$content	.= "Temp data for $inp_callsign, $inp_role, $token could not be inserted";
				$lastError	= $wpdb->last_error;
				$lastQuery	= $wpdb->last_query;
				$content	.= "Last query: $lastQuery<br />Last error; $lastError<br />";
			} else {
				$content	.= "Temp data successfully added for $inp_callsign, $inp_role, $token<br />";
				///// if this was a register token, see if there is an ignore token. If so, delete it
				if ($token == 'register') {
					$recordCount		= $wpdb->get_var("select count(record_id) 
											from $tempDataTableName 
											where callsign = '$inp_callsign' 
											and token = 'ignore'");
					if ($recordCount > 0) {
						$readResult			= $wpdb->get_results("select * from $tempDataTableName 
																	where callsign = '$inp_callsign' 
																		and token = 'ignore' 
																		and temp_data = '$inp_role'");
																		
																		
						if ($readResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numRows		= $wpdb->num_rows;
							$lastQuery		= $wpdb->last_query;
							if ($doDebug) {
								echo "ran $lastQuery<br />and retrieved $numRows records<br />";
							}
							if ($numRows > 0) {
								foreach($readResult as $readRow) {
									$tempID				= $readRow->record_id;
									$tempCallsign		= $readRow->callsign;
									$tempToken			= $readRow->token;
									$tempData			= $readRow->temp_data;
									$tempDateWritten	= $readRow->date_written;
									
									if ($temToken == 'ignore') {
										$deleteResult	= $wpdb->delete($tempDataTableName,
																		array('record_id'=>$tempID),
																		array('%d'));
										if ($deleteResult === FALSE) {
											handleWPDBError($jobname,$doDebug);
										}
									}
								}
							}
						}
					}
				}
				
				//// if the token is 'ignore' see if there is a register record. If so, delete it
				if ($token == 'ignore') {
					$recordCount		= $wpdb->get_var("select count(record_id) 
											from $gtempDataTableName 
											where callsign = '$inp_callsign' 
											and token = 'register'");
					if ($recordCount > 0) {
						$readResult			= $wpdb->get_results("select * from $tempDataTableName 
																	where callsign = '$inp_callsign' 
																		and token = 'register' 
																		and temp_data = '$inp_role'");
						if ($readResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numRows		= $wpdb->num_rows;
							$lastQuery		= $wpdb->last_query;
							if ($doDebug) {
								echo "ran $lastQuery<br />and retrieved $numRows records<br />";
							}
							if ($numRows > 0) {
								foreach($readResult as $readRow) {
									$tempID				= $readRow->record_id;
									$tempCallsign		= $readRow->callsign;
									$tempToken			= $readRow->token;
									$tempData			= $readRow->temp_data;
									$tempDateWritten	= $readRow->date_written;
									
									if ($temToken == 'register') {
										$deleteResult	= $wpdb->delete($tempDataTableName,
																		array('record_id'=>$tempID),
																		array('%d'));
										if ($deleteResult === FALSE) {
											handleWPDBError($jobname,$doDebug);
										}
									}
								}
							}
						}
					}
				}
			}
		} elseif ($inp_action == 'delete') {
			if ($doDebug) {
				echo "deleting this record<br />";
			}
			$deleteResult		= $wpdb->delete($tempDataTableName,
												array('callsign'=>$inp_callsign,
													  'token'=>$token),
												array('%s','%s'));
			if ($deleteResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Unable to delete this record<br />
									callsign: $inp_callsign<br />
									token: $inp_token<br />";
			} else {
				$content		.= "Successfully deleted<br />
									callsign: $inp_callsign<br />
									token: $inp_token<br />";
			}
		} elseif ($inp_action == 'modify') {
			if ($doDebug) {
				echo "modifying this record<br />";
			}
			$readResult			= $wpdb->get_results("select * from $tempDataTableName 
														where callsign = '$inp_callsign' 
															and token = '$token' 
															and temp_data = '$inp_role'");
			if ($readResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRows		= $wpdb->num_rows;
				$lastQuery		= $wpdb->last_query;
				if ($doDebug) {
					echo "ran $lastQuery<br />and retrieved $numRows records<br />";
				}
				if ($numRows > 0) {
					foreach($readResult as $readRow) {
						$tempID				= $readRow->record_id;
						$tempCallsign		= $readRow->callsign;
						$tempToken			= $readRow->token;
						$tempData			= $readRow->temp_data;
						$tempDateWritten	= $readRow->date_written;
						
						$content			.= "<h4>Modify Temp Data Record</h4>
												<form method='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='5'>
												<input type='hidden' name='tempID' value='$tempID'>
												<table style='border-collapse:collapse;'>
												<tr><td>Callsign</td>
													<td><input type='text' class='formInputText' size='15' maxlength='50' name='inp_callsign' value='$inp_callsign'></td><tr>
												<tr><td>Token</td>
													<td><input type='text' class='formInputText' size='20' maxlength='20' name='token' value='$token'></td></tr>
												<tr><td>Temp Data</td>
													<td><textarea class='formInputText' rows='5' cols='50' name='inp_temp_data'>$tempData</textarea></td></tr>
												<tr><td>Date Written</td>
													<td><input type='text' class='formInputText' size='20' maxlength= '20' name='inp_date_written' value='$tempDateWritten'></td></tr>
												<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
												</form></p>";
					}
				} else {
					$content	.= "No record found in $tempDataTableName for callsign: $inp_callsign, token: $token, temp_data: $inp_role<br />";
				}
			}
		} else {
			$content	.= "No action requested, none taken";
		}
		
		
		
///// pass 5
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass $strPass<br />";
		}
		$readResult			= $wpdb->get_results("select * from $tempDataTableName 
												  where record_id = $tempID");
		if ($readResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows		= $wpdb->num_rows;
			$lastQuery		= $wpdb->last_query;
			if ($doDebug) {
				echo "ran $lastQuery<br />and retrieved $numRows records<br />";
			}
			if ($numRows > 0) {
				foreach($readResult as $readRow) {
					$tempID				= $readRow->record_id;
					$tempCallsign		= $readRow->callsign;
					$tempToken			= $readRow->token;
					$tempData			= $readRow->temp_data;
					$tempDateWritten	= $readRow->date_written;
					
					
					$doUpdate						= FALSE;
					$updateParams					= array();
					$updateFormat					= array();
					if ($inp_callsign != $tempCallsign) {
						$updateParams['callsign']	= $inp_callsign;
						$updateFormat[]				= '%s';
						$doUpdate					= TRUE;
					}
					if ($token != $tempToken) {
						$updateParams['token']	= $token;
						$updateFormat[]				= '%s';
						$doUpdate					= TRUE;
					}
					if ($inp_temp_data != $tempData) {
						$updateParams['temp_data']	= $inp_temp_data;
						$updateFormat[]				= '%s';
						$doUpdate					= TRUE;
					}
					if ($inp_date_written != $tempDateWritten) {
						$updateParams['date_written'] = $inp_date_written;
						$updateFormat[]				= '%s';
						$doUpdate					= TRUE;
					}
					if ($doUpdate) {
						$updateResult			= $wpdb->update($tempDataTableName,
															$updateParams,
															array('record_id'=>$tempID),
															$updateFormat,
															array('%d'));
						if ($updateResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$content			.= "<h3>$jobname</h3>
													<p>Record has been updated:<br />";
							foreach($updateParams as $thisKey=>$thisData) {
								$content		.= "$thisKey: $thisData<br />";
							}
							
						}
					}
				}
			} else {
				$content		.= "<h3>$jogname</h3>
									<p>No record found to update. Program error</p>";
			}
		}
					

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
add_shortcode ('manage_temp_data', 'manage_temp_data_func');
