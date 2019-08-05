
<?php

//array nazioni
$nations=array("all","Albania","Arabic","Belgium","Brasil","Canada","Ex-Yu","France","Germany","Italy","Netherland","Poland","Portugal","Russia","Scandinavian","Spain","Sweden","Switzerland","Turkey","Uk","Usa","Ukraine");
//echo form
echo $form='<form method="post" action="playlist.php"><h3>Seleziona la nazione</h3><select size="10" name="nations[]" multiple="true">';
//ciclo creazione option
foreach( $nations as $nation){ 
	$option.='<option value="'.strtolower($nation).'">'.$nation.'</option><br/><br/>';
}
echo $option.='</select><br><input type="submit" value="Cerca"/></form>';

$nations = isset($_POST['nations']) ? $_POST['nations'] : array();

if (!count($nations)) echo 'Errore! Devi selezionare almeno una nazione!';
else {
	echo 'Ecco le playlist delle nazioni da te selezionate:<br>';
	foreach($nations as $nation) {
		echo ucfirst($nation)."<br>";
		playlist($nation);
	}
}

function playlist($searchString){
	
	
	// all files in my/dir with the extension 
	// .php 
	$files = glob('m3u/*.m3u');

	// array populated with files found 
	// containing the search string.
	//$filesFound = array();

	// iterate through the files and determine 
	// if the filename contains the search string.
	foreach($files as $file) {
		$name = pathinfo($file, PATHINFO_FILENAME);
		// determines if the search string is in the filename.
		$pos=strpos(strtolower($name), strtolower($searchString));
		if($pos !== false) {
			// determines if the search string is in the uk and ukraine mistake is in.
			if($searchString=="uk"){
				$pos=strpos(strtolower($name), strtolower("Ukraine"));
				if($pos !== false) echo "";
				else echo "<a href='".$file."'>".$name."</a><br>";
			}else{
				// output the results.
				echo "<a href='".$file."'>".$name."</a><br>";
			}
			
		}
	}

	
	
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

?>
