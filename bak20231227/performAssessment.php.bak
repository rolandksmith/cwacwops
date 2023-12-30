function performAssessment_func() {

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
	$theURL						= "$siteURL/cwa-perform-assessment/";
	$jobname					= "Perform Assessment V$versionNumber";
	$parameterArray				= array('Beginner'=>'15&4&5&2&2&5',		// cpm & wpm & questions & word & characters
										'Fundamental'=>'25&6&5&2&2&5',
										'Intermediate'=>'25&10&5&2&3&5',
										'Advanced'=>'25&20&5&2&4&5');
	$inp_mode1					= '';
	$inp_callsign				= '';
	$inp_level					= '';
	$inp_cpm					= '';
	$inp_eff					= '';
	$inp_freq					= '';
	$inp_questions				= '';
	$inp_words					= '';
	$inp_characters				= '';
	$inp_answers				= '';
	$inp_vocab					= '';
	$inp_infor					= '';
	$inp_callsign_count			= 0;
	$inp_makeup					= "";
	
	$vocabConvert				= array('threek'=>'3k Words',
										'original'=>'900 Words');

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
				$inp_callsign	 = $str_value;
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_level") {
				$inp_level	 = $str_value;
				$inp_level	 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode1") {
				$inp_mode1	 = $str_value;
				$inp_mode1	 = filter_var($inp_mode1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_wpm") {
				$inp_wpm	 = $str_value;
				$inp_wpm	 = filter_var($inp_wpm,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_eff") {
				$inp_eff	 = $str_value;
				$inp_eff	 = filter_var($inp_eff,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_freq") {
				$inp_freq	 = $str_value;
//				$inp_freq	 = filter_var($inp_freq,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_questions") {
				$inp_questions	 = $str_value;
				$inp_questions	 = filter_var($inp_questions,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_words") {
				$inp_words	 = $str_value;
				$inp_words	 = filter_var($inp_words,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_characters") {
				$inp_characters	 = $str_value;
				$inp_characters	 = filter_var($inp_characters,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_answers") {
				$inp_answers	 = $str_value;
				$inp_answers	 = filter_var($inp_answers,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_vocab") {
				$inp_vocab	 = $str_value;
				$inp_vocab	 = filter_var($inp_vocab,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_infor") {
				$inp_infor	 = $str_value;
				$inp_infor	 = filter_var($inp_infor,FILTER_UNSAFE_RAW);
				$inp_infor	 = str_replace(" ","%20",$inp_infor);
			}
			if ($str_key 		== "inp_doemail") {
				$inp_doemail	 = $str_value;
				$inp_doemail	 = filter_var($inp_doemail,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign_count") {
				$inp_callsign_count	 = $str_value;
				$inp_callsign_count	 = filter_var($inp_callsign_count,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_makeup") {
				$inp_makeup	 = $str_value;
				$inp_makeup	 = filter_var($inp_makeup,FILTER_UNSAFE_RAW);
			}
		}
	}
	
	
	$content = "<style type='text/css'>
fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
background:#efefef;padding:2px;border:solid 1px #d3dd3;}

legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}

label {font:'Times New Roman', sans-serif;line-height:normal;
text-align:left;margin-right:10px;position:relative;display:block;float:left;}

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

// callsign=wr7q&wpm=25&eff=6&freq=600&questions=5&words=1&characters=3	


	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>Two assessment types are available. The Level type requires only the callsign and level 
are required to be selected. All the other parameters are pre-set. The Specific type needs 
all fields to be selected.</p> 
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;width:auto'>
<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Assessment Type</td>
	<td><table>
		<tr><td style='width:200px;'>
				<input type='radio' class='formInputButton' id='level' name='inp_mode1' value='Level' required>
				<label for 'level'>Level</label></td>
			<td style='width:200px;'>
				<input type='radio' class='formInputButton' id='specific' name='inp_mode1' value='specific' required>
				<label for 'specific'>Specific</label></td>
			<td style='width:200px;></td>
			<td style='width:200px;'></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'><td style='vertical-align:top;width:250px;'>Call Sign</td>
	<td><input type='text' class= 'formInputText' name='inp_callsign' size='20' maxlength='20' required></td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Level</td>
	<td><table>
		<tr><td style='width:200px;'>
				<input type='radio' class='formInputButton' id='Beginner' name='inp_level' value='Beginner'>
				<label for 'Beginner'>Beginner</label></td>
			<td style='width:200px;'>
				<input type='radio' class='formInputButton' id='Fundamental' name='inp_level' value='Fundamental' required>
				<label for 'Fundamental'>Fundamental</label></td>
			<td style='width:200px;'>
				<input type='radio' class='formInputButton' id='Intermediate' name='inp_level' value='Intermediate' required>
				<label for 'Intermediate'>Intermediate</label></td>
			<td style='width:200px;'>
				<input type='radio' class='formInputButton' id='Advanced' name='inp_level' value='Advanced' required>
				<label for 'Advanced'>Advanced</label></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Character Speed</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_wpm' value='15' > 15 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_wpm' value='20' > 20 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_wpm' value='25' checked> 25 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_wpm' value='30' > 30 wpm</td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'><td style='vertical-align:top;'>Effective Speed</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_eff' value='6' > 6 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='8' > 8 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='10' > 10 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='12' > 12 wpm</td></tr>
		<tr><td><input type='radio' class='formInputButton' name='inp_eff' value='15' > 15 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='18' > 18 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='20' > 20 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='22' > 22 wpm</td></tr>
		<tr><td><input type='radio' class='formInputButton' name='inp_eff' value='25' > 25 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='27' > 27 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='30'>  30 wpm</td>
			<td><input type='radio' class='formInputButton' name='inp_eff' value='35'>  35 wpm</td></tr>
		<tr><td><input type='radio' class='formInputButton' name='inp_eff' value='40'>  40 wpm</td>
			<td></td>
			<td></td>
			<td></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Frequency</td>
	<td><table>
		<tr><td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='450' > 450 Hz</td>
			<td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='500' > 500 Hz</td>
			<td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='550' > 550 Hz</td>
			<td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='600' checked> 600 Hz</td></tr>
		<tr><td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='650' > 650 Hz</td>
			<td><input type='checkbox' class='formInputButton' name='inp_freq[]' value='700' > 700 Hz</td>
			<td></td>
			<td></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Questions</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_questions' value='3' > 3 questions</td>
			<td><input type='radio' class='formInputButton' name='inp_questions' value='5' checked> 5 questions</td>
			<td><input type='radio' class='formInputButton' name='inp_questions' value='7' > 7 questions</td>
			<td><input type='radio' class='formInputButton' name='inp_questions' value='8' > 8 questions</td></tr>
		</table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Words per question</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_words' value='1' > 1 word</td>
			<td><input type='radio' class='formInputButton' name='inp_words' value='2' checked> 2 words</td>
			<td><input type='radio' class='formInputButton' name='inp_words' value='3' > 3 words</td>
			<td><input type='radio' class='formInputButton' name='inp_words' value='4' > 4 words</td></tr>
			</table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Max characters per word</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_characters' value='3' checked> up to 3 characters</td>
			<td><input type='radio' class='formInputButton' name='inp_characters' value='4' > up to 4 characters</td>
			<td><input type='radio' class='formInputButton' name='inp_characters' value='5' > up to 5 characters</td>
			<td></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Callsigns to be included</td>
	<td><table>
		<tr><td>How Many<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='1'> One<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='2'> Two<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='3'> Three<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='4'> Four<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='5'> Five<br />
				<input type='radio' class='formInputButton' name='inp_callsign_count' value='6'> Six<br /></td>
			<td>Callsign Makeup<br />
				<input type='radio' class='formInputButton' name='inp_makeup' value='(3-4)'> 3-4 characters<br />
				<input type='radio' class='formInputButton' name='inp_makeup' value='(3-5)'> 3-5 characters<br />
				<input type='radio' class='formInputButton' name='inp_makeup' value='(3-6)'> 3-6 characters</td>
			</table></td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>How many multiple choice answers</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_answers' value='4' > 4 answers</td>
			<td><input type='radio' class='formInputButton' name='inp_answers' value='5' checked> 5 answers</td>
			<td><input type='radio' class='formInputButton' name='inp_answers' value='7' > 7 answers</td>
			<td><input type='radio' class='formInputButton' name='inp_answers' value='8' > 8 answers</td></tr>
		<tr><td></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Which Vocabulary</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_vocab' value='threek' checked> 3000 Words</td>
			<td><input type='radio' class='formInputButton' name='inp_vocab' value='original' > 900 Words</td>
			<td></td>
			<td></td></tr></table>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td style='vertical-align:top;'>Identification Info</td>
	<td><input type='text' class='formInputText' name='inp_infor' size='50' maxlength='50' value='Assessment Text'>
	</td></tr>

<tr style='border-bottom: 1pt solid black;'>
	<td>Send link to person in callsign field?</td>
	<td><table>
		<tr><td><input type='radio' class='formInputButton' name='inp_doemail' value='Yes' > Yes</td>
			<td><input type='radio' class='formInputButton' name='inp_doemail' value='No' checked > No</td>
			<td></td>
			<td></td></tr></table>
	</td></tr>
	
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with inp_mode of $inp_mode1<br />";
		}
		
		$token			= mt_rand();
		$emailAddr		= '';
		$emailCallsign	= '';
		$gotEmail		= FALSE;
		
		$content		.= "<h3>$jobname</h3>";
		
		if ($inp_doemail == 'Yes') {			// send the link via email
			// first see if it's an advisor
			$sql		= "select call_sign, email 
							from wpw1_cwa_consolidated_advisor 
							where call_sign = '$inp_callsign' 
							order by date_created DESC 
							limit 1";
			$advisorResult	= $wpdb->get_results($sql);
			if ($advisorResult === FALSE) {
				$content	.= "Reading wpw1_cwa_consolidated_advisor failed<br />";
			} else {
				$numRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				foreach ($advisorResult as $advisorRow) {
					$emailCallsign		= $advisorRow->call_sign;
					$emailAddr			= $advisorRow->email;
					
					$gotEmail				= TRUE;
				}
			}
			if (!$gotEmail) {			// see if it is a student to get the email
				$sql			= "select call_sign, email 
									from wpw1_cwa_consolidated_student 
									where call_sign = '$inp_callsign' 
									order by date_created DESC 
									limit 1";
				$studentResult	= $wpdb->get_results($sql);
				if ($studentResult === FALSE) {
					$content	.= "Reading wpw1_cwa_consolidated_student failed<br />";
				} else {
					$numRows	=$wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numRows rows<br />";
					}
					if ($numRows > 0) {
						foreach($studentResult as $studentRow) {
							$emailCallsign		= $studentRow->call_sign;
							$emailAddr			= $student_email;
							
							$gotEmail				= TRUE;
						}
					}
				}
			}
		}
		
		if ($inp_mode1 == 'Level') {
			$myStr			= $parameterArray[$inp_level];
			$myArray 		= explode("&",$myStr);
			$inp_cpm		= $myArray[0];
			$inp_wpm		= $myArray[1];
			$inp_questions	= $myArray[2];
			$inp_words		= $myArray[3];
			$inp_characters	= $myArray[4];
			$inp_answers	= $myArray[5];
			
			$url 		= "<a href='https://cw-assessment.vercel.app?mode=$inp_level&callsign=$inp_callsign&token=$token&vocab=$inp_vocab&infor=$inp_infor'";
			$info		= "Mode: $inp_mode1<br />
							Callsign: $inp_callsign</p>";
		} elseif ($inp_mode1 == 'specific') {
			// handle the freq array
			$freq		= "";
			$firstTime	= TRUE;
			foreach($inp_freq as $thisValue) {
				if ($firstTime) {
					$firstTime = FALSE;
					$freq 	= $thisValue;
				} else {
					$freq	= "$freq,$thisValue";
				}
			}

			// put together the callsigns parameter
			if ($inp_callsign_count > 0) {
				$csparam	= $inp_callsign_count . $inp_makeup;
			} else {
				$csparam	= '';
			}

			$url 		= "<a href='https://cw-assessment.vercel.app?mode=specific&callsign=$inp_callsign&cpm=$inp_wpm&eff=$inp_eff&freq=$freq&questions=$inp_questions&words=$inp_words&characters=$inp_characters&callsigns=$csparam&answers=$inp_answers&level=$inp_level&token=$token&vocab=$inp_vocab&infor=$inp_infor";
							
			$info		= "Mode: specific<br />
							Call sign: $inp_callsign<br />
							Level: $inp_level<br />
							WPM: $inp_wpm<br />
							Effective: $inp_eff<br />
							Frequency: $freq<br />
							Questions: $inp_questions<br />
							Words: $inp_words<br />
							Characters: $inp_characters<br />
							Callsigns: $csparam<br />
							Answers: $inp_answers<br />
							Vocabulary: $inp_vocab<br />
							Identification Info: $inp_infor</p>";
			
		}
		if ($inp_doemail == 'Yes') {				// if have an email address, send off the link
			if ($gotEmail) {
				$url			= $url . "' target='_blank'>Perform Assessment</a>";
				$emailContent	= "To: $emailCallsign
									<p>CW Academy has developed a Morse code proficiency 
									evaluation program that is going to be incorporated 
									into the student registration program, replacing the 
									subjective assessment currently being done.</p>
									<p>We are testing the new process and want your 
									feedback on the process.</p>
									<p>Below is a link to the proficiency assessment. 
									When you click on the link and start the assessment, 
									you will be presented $inp_questions questions in Morse code, 
									each question consisting of $inp_words words. The words 
									will have up to $inp_characters characters. 
									The characer speed will be set to $inp_cpm wpm with an 
									effective (Farnsworth) speed of $inp_wpm wpm. This corresponds to 
									an $inp_level Level proficiency assessment.</p>
									<p>We would like your impressions and comments about the 
									assessment. Please send them to 
									<a href='mailto:kcgator@gmail.com,rolandksmith@gmail.com,abunker@gmail.com?subject=Testing the Proficiency Assessment'>Bob Carter WR7Q</a>.</p>
									<p>To do the Morse code proficiency assessment, click 
									$url</p>
									<p>The button to 'Return to CWOps' will have no action 
									upon completion of the assessment. Simply close the tab.</p>";
				$theSubject		= "CW Academy Morse Code Proficiency Assessment Test";
				$mailResult		= emailFromCWA_v2(array('theRecipient'=>$emailAddr,
															'theSubject'=>$theSubject,
															'theContent'=>$emailContent,
															'theCc'=>'',
															'mailCode'=>11,
															'jobname'=>$jobname,
															'increment'=>0,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug));
				if ($mailResult === FALSE) {
					$content	.= "Email failed to send to $emailAddr<br />";
				} else {
					$content	.= "Email sent to $emailAddr<br />
									$info<br />Click on this link to restart the program: 
									<a href='$theURL'>Do It Again</a>";
				}
			}
		} else {
			$myStr		= "$siteURL/cwa-perform-assessment/?strpass=3&inp_callsign=$inp_callsign&token=$token";
			$returnurl	= urlencode($myStr);
			$url		= "$url" . "&returnurl=$returnurl'>Perform Assessment</a>";
			$content	.= "$info						
							<p>Click on this link to perform the assessment: $url</p>
							<br /><br /><p>Click on this link to restart the program: 
							<a href='$theURL'>Do It Again</a></p>";

		}



	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass3 with <br />
					inp_callsign: $inp_callsign<br />
					token: $token<br />";
		}
		
		$doProceed	= TRUE;
		
		$content	.= "<h3>$jobname</h3>
						<h4>Database Info</h4>";
		$bestResultBeginner		= 0;
		$didBeginner			= FALSE;
		$bestResultFundamental	= 0;
		$didFundamental			= FALSE;
		$bestResultIntermediate	= 0;
		$didIntermediate		= FALSE;
		$bestResultAdvanced		= 0;
		$didAdvanced			= FALSE;
		$retVal			= displayAssessment('',$token,$doDebug);
		if ($retVal[0] === FALSE) {
			if ($doDebug) {
				echo "displayAssessment returned FALSE. Called with $inp_callsign, $inp_token<br />";
			}
			$content	.= "No data to display.<br />Reason: $retVal[1]";
		} else {
			$content	.= $retVal[1];
			$myArray	= explode("&",$retVal[2]);
			foreach($myArray as $thisValue) {
				$myArray1	= explode("=",$thisValue);
				$thisKey	= $myArray1[0];
				$thisData	= $myArray1[1];
				$$thisKey	= $thisData;
				if ($doDebug) {
					echo "$thisKey = $thisValue<br />";
				}
			}
			$content		.= "<p>You have completed the Morse Code Proficiency 
								assessment.<br />";
			if ($didBeginner) {
				$content	.= "Your Beginner Level assessment score was $bestResultBeginner<br />";
			}
			if ($didFundamental) {
				$content	.= "Your Fundamental Level assessment score was $bestResultFundamental<br />";
			}
			if ($didIntermediate) {
				$content	.= "Your Intermediate Level assessment score was $bestResultIntermediate<br />";
			}
			if ($didAdvanced) {
				$content	.= "Your Advanced Level assessment score was $bestResultAdvanced<br />";
			}
			$content				.= "<br /><br /><p>Click on this link to restart the program: 
										<a href='$theURL'>Do It Again</a></p>";
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
add_shortcode ('performAssessment', 'performAssessment_func');
