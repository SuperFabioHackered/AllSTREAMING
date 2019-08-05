
<?php
//array nazioni
$nations=array("all","Albania","Arabic","Belgium","Brasil","Canada","Ex-Yu","France","Germany","Italy","Netherland","Poland","Portugal","Russia","Scandinavian","Spain","Sweden","Switzerland","Turkey","Uk","Usa","Ukraine");
//echo form
echo $form='<form method="post" action="index.php"><h3>Seleziona la nazione</h3><select size="10" name="nations[]" multiple="true">';
//ciclo creazione option
foreach( $nations as $nation){ 
	$option.='<option value="'.strtolower($nation).'">'.$nation.'</option><br/><br/>';
}
echo $option.='</select><br><input type="submit" value="Cerca"/></form>';

$nations = isset($_POST['nations']) ? $_POST['nations'] : array();

if (!count($nations)) echo 'Errore! Devi selezionare almeno una nazione!';
else {
	echo 'Ecco i canali delle nazioni da te selezionate:<br>';
	foreach($nations as $nation) {
		echo ucfirst($nation)."<br>";
		channel($nation);
	}
}

function channel($searchString){
	$nation=$searchString;
	// all files in my/dir with the extension 
	// .php 
	
	if($nation=="all"){
		$files = glob('m3u/all/*.m3u');
		$file=$files[0];
	}
	else{
		$files = glob('m3u/*.m3u');
	
		// array populated with files found 
		// containing the search string.
		//$filesFound = array();

		// iterate through the files and determine 
		// if the filename contains the search string.
		$i=0;
		$dates = array();
		foreach($files as $file) {
			$name = pathinfo($file, PATHINFO_FILENAME);
			// determines if the search string is in the filename.
			$pos=strpos(strtolower($name), strtolower($searchString));
			if($pos !== false) {
				// determines if the search string is in the uk and ukraine mistake is in.
				if($searchString=="uk"){
					$pos=strpos(strtolower($name), strtolower("Ukraine"));
					if($pos !== false) echo "";
					else{
						$dates=convertDate($file,$dates,$i);
					} 
				}else{
					// output the results.
					$dates=convertDate($file,$dates,$i);
				}
			}
			$i++;
		}
		
		$recentlyDate=recentlyDate($dates);
		debug_to_console("recentlyDate: ".$recentlyDate);//debug recentlyDate
		//open read file by recently date and by nation 
		//create array with all explode string
		//create each m3u channel file 
		$searchString=ucfirst($searchString);
		if($searchString=="Ex-yu") $searchString="Ex-Yu";
		if($searchString=="Uk" || $searchString=="Usa") $searchString="Uk-Usa";
		// test name nation + recently date: $searchString.$recentlyDate;
		
		//assign to file the name of a file
		
		$file = 'm3u/'.$searchString.$recentlyDate.'_allstreaming.xyz.m3u';
		
	}
	
	debug_to_console("recent file: ".$file);//debug file
	createChannels($file,$nation);
	
	/*
	$dir = '/m3u';
	$files = scandir($dir);
	
	
	$websiteUrl = "m3u/";
	echo $html = file_get_html($websiteUrl);	
	$i=1;
	foreach ($html->find('a') as $a){
		if($i>5)
			if(strpos($a->innertext, ucfirst($nation)) !== false) echo "ok<a href=".$websiteUrl.$a->attr['href']."><br>";
		$i++;
	}
	*/
}

function convertDate($file,$dates,$i){
	$pattern = "/\d{4}\\d{2}\\d{2}/";
	if (preg_match($pattern, $file, $matches)) {
		//test show value matches echo "Found Match on ".$matches[0];
		// The value from the datepicker
		$dpValue = $matches[0];
		// Parse a date using a user-defined format
		$date = DateTime::createFromFormat('dmY', $dpValue);
		// If the above returns false then the date
		// is not formatted correctly
		if ($date === false) {
			header('HTTP/1.0 400 Bad Request');
			die('Invalid date from datepicker!');
		}
		// Using the parsed date we can create a
		// new one with a formatting of out choosing
		$dates[$i]=$date->format('d-m-Y');
	}
	return $dates;
}

function recentlyDate($dates){
	//search most recent m3u playlist
	//test date before order: print_r($dates);
	usort($dates, "sortFunction");
	//test date after order: print_r($dates);
	$recentlyDate = $dates[count($dates)-1];//recentlyDate
	$recentlyDate = str_replace("-", "", $recentlyDate);//recentlyDate after replace string - with space
	return $recentlyDate;
}

function sortFunction( $a, $b ) {
	return strtotime($a) - strtotime($b);
}

function createChannels($file,$nation){
	$acronyms = array("AR: ", "BE: ", "BR: ", "CA: ", "FR|", "FR: ","|IT|", "IT: ", "NL: ", "PL: ", "POL: ", "PT: ", "Se | ", "Dk | ", "Fi | ", "ES: ", "SWISS: ", "TR: ", "UK: ", "USA VIP - ", "US - ", "USA Premium ");
		
	$myfile = fopen($file, "r") or die("Unable to open file!");
	$read=fread($myfile,filesize($file));
	$channels = explode("#", $read);
	//test date before order: print_r($channels);
	foreach($channels as $channel){
		//utf8_decode($channel);
		$pos = strpos($channel, "http");
		if($channel!="" && $channel!=strpos($channel, "EXTM3U") && $channel!=strpos($channel, "#EXTINF:-1,")){
			$channelName = substr($channel, 10, $pos-12)."<br>";  //channelName
			if($nation=="uk" && (strpos($channel, "US - ") || strpos($channel, "USA VIP - ") || strpos($channel, "USA Premium "))){
				if(strpos($channel, "UK: ")){
					echo $channelName = str_replace($acronyms, "", $channelName);
					echo substr($channel, $pos);  //channelLink
				}
			}else
			if ($nation=="usa" && strpos($channel, "UK: ")){
				if(strpos($channel, "US - ") || strpos($channel, "USA VIP - ") || strpos($channel, "USA Premium ")){
					echo $channelName = str_replace($acronyms, "", $channelName);
					echo substr($channel, $pos);  //channelLink
				}
			}
			else{
				echo $channelName = str_replace($acronyms, "", $channelName);
				echo $channelLink = substr($channel, $pos);  //channelLink
				createChannel($channelName,$channelLink);
			}
		} 
	}	
	fclose($myfile);
	
	
}

function createChannel($channelName,$channelLink){
	$channelName = str_replace("<br>", "", $channelName);
	$channelLink = str_replace("<br>", "", $channelLink);
	$myNewfile = fopen("m3u/channels/".$channelName.".m3u", "w") or die("Unable to open file!");
	$txt = "#EXTM3U #EXTINF:-1,".$channelName." ".$channelLink;
	debug_to_console( "contenuto file m3u canale: ".$channelName.$txt );
	debug_to_console( "file: ".$myNewfile );
	
	fwrite($myNewfile, $txt);
	fclose($myNewfile);
}

function debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);
    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}

?>
