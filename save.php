<?php
//Start session
session_start();
//Striping strings down
$post_name = substr($_POST['Name'],0,10);
$post_score = substr($_POST['Score'],0,10);
//Sanitizing the string
$post_name = filter_var($post_name, FILTER_SANITIZE_STRING);
$post_score = filter_var($post_score, FILTER_SANITIZE_NUMBER_INT);

$myFile = "data.json";
$arr_data = array(); // create empty array

// 20 seconds delay for next post by this user
if ($_SESSION["time"]+20 < time() || $_SESSION["time"] == False){
  	try{
	   	//Get form data
	   	$formdata = array(
			'Time'=> time(),	      	
			'Name'=> $post_name,
	      		'Score'=> $post_score
	   	);

	   	//Get data from existing json file
	   	$jsondata = file_get_contents($myFile);

		// converts json data into array
		$arr_data = json_decode($jsondata, true);

		$records = sizeof($arr_data);
		
		//Minimize file size, reset file at your choosen record amount
		if ($records >= 100){
			// converts json data into array
			unset($arr_data);
			$arr_data = array();
		}

	   	// Push user data to array
	   	array_push($arr_data,$formdata);

       		//Convert updated array to JSON
	   	$jsondata = json_encode($arr_data, JSON_PRETTY_PRINT);
	   
	   	//write json data into data.json file
	   	if(file_put_contents($myFile, $jsondata)) {
	        	echo "$post_name got a score of: $post_score";
	    	} else {
	        	echo "error";
		}
	}
   	catch (Exception $e) {
		echo "Caught an exception";
		//For debugging echo the exception message:            	
		//echo 'Caught exception: ',  $e->getMessage(), "\n";
   	}
}


$_SESSION["time"] = time();

?>
