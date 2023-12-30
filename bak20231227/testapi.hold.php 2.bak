<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");

if (isset( $_SERVER['REQUEST_METHOD'] )
  	&& $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	  // need preflight here
	  header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept' );
	  // add cache control for preflight cache
	  // @link https://httptoolkit.tech/blog/cache-your-cors/
	  header( 'Access-Control-Max-Age: 86400' );
	  header( 'Cache-Control: public, max-age=86400' );
	  header( 'Vary: origin' );
	  header("HTTP/1.1 200 OK");
	  // just exit and CORS request will be okay
	  // NOTE: We are exiting only when the OPTIONS preflight request is made
	  // because the pre-flight only checks for response header and HTTP status code.
	  exit( 0 );
}


 echo "api called\n";

$doProceed = TRUE;
$cs = '';
$level = '';
$score = 0.0;
$thisdate = date("Y-m-d H:i:s");
$passwd = "7B-m)p7d2S";

//  connect to the database
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$link = mysqli_connect(NULL,"cwacwops_wp540",$passwd,"cwacwops_wp540");

$thisStuff = print_r($_POST,TRUE);
echo "contents of POST: $thisStuff\n";

// get the data being posted
$inpVariable =  $_POST['variable'];

// stuff what was received into wpw1_cwa_assessment_log table
$query = "insert into wpw1_cwa_assessment_log 
			(base64data,date_written,program) 
			values ('$inpVariable','$thisdate','testapi')";
 echo "$query\n";			
$result = mysqli_query($link, $query);


$myVariable = base64_decode($inpVariable);

echo "Decoded variable: $myVariable\n";

$myInt = strpos($myVariable,'cs=');
if ($myInt === FALSE) {
	$doProceed = FALSE;
}
$myInt = strpos($myVariable,'level=');
if ($myInt === FALSE) {
	$doProceed = FALSE;
}
$myInt = strpos($myVariable,'score=');
if ($myInt === FALSE) {
	$doProceed = FALSE;
}

if ($doProceed) {
	echo "cs, level, and score found in the variable\n";

	// parse the input
	$myArray = explode(",",$myVariable);
	foreach($myArray as $thisSet) {
		$setArray = explode("=",$thisSet);
		$thisKey = $setArray[0];
		$thisValue = $setArray[1];
		${$thisKey}	= $thisValue;
	}
	$cs = strtoupper($cs);
//	echo "input parsed\n";
	
	$query = "insert into wpw1_cwa_assessment_testing (call_sign,score,level,date_written) 
				values ('$cs',$score,'$level','$thisdate')";
				
	echo "$query\n";
				
	$result = mysqli_query($link, $query);

	if ($result === FALSE) {
		header("HTTP/1.1 400 NO");
	} else {
		header("HTTP/1.1 200 OK");
	}
	$link->close();
} else {
	header("HTTP/1.1 400 NO");
}
 exit(0);

?>