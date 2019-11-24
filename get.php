<?php
$myFile = "data.json";
$arr_data = array(); // create empty array

//Get data from existing json file
$jsondata = file_get_contents($myFile);

// converts json data into array
$arr_data = json_decode($jsondata, true);

//Sort records order
function build_sorter($key) {
    return function ($b, $a) use ($key) {
        return strnatcmp($a[$key], $b[$key]);
    };
}
usort($arr_data, build_sorter('Score'));

// for-Loop for displaying result 
foreach ($arr_data as $key => $val) { 
	$rank = $key + 1;
    echo "Rank $rank: <b>$val[Name]</b>, Score: <b>$val[Score]</b>"; 
    echo"<br>"; 
	if ($rank > 9) {
	break;
	}
} 

?>
