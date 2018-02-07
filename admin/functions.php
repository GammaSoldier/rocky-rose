<?php
/**
 * -----------------------------------------
 * @author Nico Schubert / www.php-space.info
 * @copyright Das Script kann unter Verwendung des Copyright uneingeschränkt genutzt / verändert werden. Das Copyright muss im Code sowie in der Ausgabe erhalten bleiben.
 * @version Datei Upload Version 1.06 - 18.06.2009
 * @abstract Das Script läuft erst ab der Php Version 5.0 oder höher, wenn Sie Thumbnail erstellen wollen, benötigen Sie GD Bibliothek in der Version 2.0.1 oder höher. Wenn Sie Probleme mit den Einrichten haben, so schauen Sie bitte in die Anleitung -> anleitung_1.06.txt
 * -----------------------------------------
 */
 
define ( 'JPG_QUALITY', '100' );
define ( 'BG_COLOR', '0xffffff' );
define ( 'FONT_COLOR', '0x000000' );

define ( 'THUMBNAIL_TEXT_HEIGHT', 0 ); 
 

//if (eregi("functions.php",$_SERVER["PHP_SELF"])) {
//	exit;
//}


/***************************************************************************************************
***************************************************************************************************/
function fs_convert ($datei, $nachkommastellen = 0) {
	$size = @filesize($datei);
	if($size >= 1073741824) {
		return round($size/(1073741824), $nachkommastellen)." GB";
	}

	if($size >= 1048576) {
		return round($size/(1048576), $nachkommastellen)." MB";
	}

	if($size >= 1024) {
		return round($size/(1024), $nachkommastellen)." KB";
	}
	return $size." Byte";
}


/***************************************************************************************************
***************************************************************************************************/
function last_change ($site) {
	if(empty($site)) {
		$site = $_SERVER['DOCUMENT_ROOT'];
		$site.= $_SERVER['PHP_SELF'];
	}
	return filemtime($site);
}


/***************************************************************************************************
***************************************************************************************************/
function uploadmoeglichkeitpruefen(){
	$uploadmoeglichkeit=true;
	if(strtolower(@ini_get('file_uploads'))=='off' || @ini_get('file_uploads')==0){
		$uploadmoeglichkeit=false;
	}
	return $uploadmoeglichkeit;
}


/***************************************************************************************************
***************************************************************************************************/
function GetMaxUploadSize() {
	$maximaledateiuploadgroesse=0;
	if($dateigroesse=ini_get('upload_max_filesize')){
		$maximaledateiuploadgroesse=phpiniwertumwandeln($dateigroesse);
	}
	if($postgroesse=ini_get('post_max_size')){
		$postgroesse=phpiniwertumwandeln($postgroesse);
		if($postgroesse<$maximaledateiuploadgroesse){
			$maximaledateiuploadgroesse=$postgroesse;
		}
	}
	return $maximaledateiuploadgroesse;
}


/***************************************************************************************************
***************************************************************************************************/
function phpiniwertumwandeln($groesse){
	$werte['MB'] = 1048576;
	$werte['Mb'] = 1048576;
	$werte['M'] = 1048576;
	$werte['m'] = 1048576;
	$werte['KB'] = 1024;
	$werte['Kb'] = 1024;
	$werte['K'] = 1024;
	$werte['k'] = 1024;

	while(list($schluessel)=each($werte)){
		if((strlen($groesse)>strlen($schluessel)) && (substr($groesse, strlen($groesse)-strlen($schluessel))==$schluessel))		{
			$groesse=substr($groesse, 0, strlen($groesse)-strlen($schluessel))*$werte[$schluessel];
			break;
		}
	}
	return $groesse;
}


/***************************************************************************************************
***************************************************************************************************/
function thumbnail( $bild='', $bilder_path_orginalbild='', $bilder_path_thumbnail='', $prefix, $thumbnail_neuebreite, $thumbnail_neuehoehe, $overwrite ){

	$size= getimagesize($bilder_path_orginalbild.$bild) OR die('Unknown filesize');
	
	$breite=$size[0];
	$hoehe=$size[1];

	$filesize = round(filesize( $bilder_path_orginalbild.$bild )/1024).' KB';

	if( file_exists($bilder_path_thumbnail.$prefix.$bild ) && !$overwrite ) {
		return $size[2];
	}// if

	if( $breite >= $hoehe ) {
		if( $breite > $thumbnail_neuebreite ) {
			$neuebreite = $thumbnail_neuebreite;
			$neuehoehe = intval($hoehe * $neuebreite/$breite );
		}// if
		else {
			$neuebreite = $breite;
			$neuehoehe = $hoehe;
		}// else
	}// if
	else {
		if( $hoehe > $thumbnail_neuehoehe ) {
			$neuehoehe = $thumbnail_neuehoehe;
			$neuebreite = intval($breite * $neuehoehe/$hoehe );
		}// if
		else {
			$neuebreite = $breite;
			$neuehoehe = $hoehe;
		}// else
	}// else

//	var_dump( $neuebreite );
	$neuesbild= imagecreatetruecolor( $neuebreite, $neuehoehe + THUMBNAIL_TEXT_HEIGHT );
	imagefill( $neuesbild, 0, 0, BG_COLOR );

	if( THUMBNAIL_TEXT_HEIGHT > 0 )  {
		$font_size = 2;
		$text_width = imagefontwidth( $font_size ) * strlen( $filesize );
		imagestring ( $neuesbild, $font_size, ($neuebreite - $text_width) / 2, $neuehoehe + 1, $filesize, FONT_COLOR );
	}// if
	
	if($size[2]==1) {
		// GIF
		$altesbild= imagecreatefromgif($bilder_path_orginalbild.$bild);
		imagecopyresampled($neuesbild,$altesbild,0,0,0,0,$neuebreite,$neuehoehe,$breite,$hoehe);
//		imagerectangle( $neuesbild, 0, 0, $neuebreite -1, $neuehoehe + THUMBNAIL_TEXT_HEIGHT -1, FONT_COLOR );
		imagegif($neuesbild,$bilder_path_thumbnail.$prefix.$bild);
	}

	if($size[2]==2) {
		// JPG
		$altesbild= imagecreatefromjpeg($bilder_path_orginalbild.$bild);
		imagecopyresampled($neuesbild,$altesbild,0,0,0,0,$neuebreite,$neuehoehe,$breite,$hoehe);
//		imagerectangle( $neuesbild, 0, 0, $neuebreite -1, $neuehoehe + THUMBNAIL_TEXT_HEIGHT -1, FONT_COLOR );
		imagejpeg($neuesbild,$bilder_path_thumbnail.$prefix.$bild, 90 );
	}

	if($size[2]==3) {
		// PNG
		$altesbild= imagecreatefrompng($bilder_path_orginalbild.$bild);
		imagecopyresampled($neuesbild,$altesbild,0,0,0,0,$neuebreite,$neuehoehe,$breite,$hoehe);
//		imagerectangle( $neuesbild, 0, 0, $neuebreite -1, $neuehoehe + THUMBNAIL_TEXT_HEIGHT -1, FONT_COLOR );
		imagepng($neuesbild,$bilder_path_thumbnail.$prefix.$bild);
	}
	return $size[2];
}



/***************************************************************************************************
***************************************************************************************************/
function PageNavigation( $totalItems, $perPage, $startWith, $actualPage, $numNavPages, $pageName, $pageParam )
{
	$retVal = '';
	
	if( $totalItems > $perPage ) {
		// Let’s count how many items we need per page
		$totalPages = ceil( $totalItems / $perPage );
		
		// prev-tag
		if( $actualPage > $startWith ) {
			$retVal = '<a href="'.$pageName.'?'.$pageParam.'='.( $actualPage - 1 ).'">&lt;&lt;&lt;</a>&nbsp;';
		}// if
		
		if( $numNavPages >= $totalPages ) {
			$start = $startWith;
			$end = $startWith + ($totalPages - 1 );
		}// if
		else {
			$showAbove = ceil(($numNavPages-1) / 2);
		 	$showBelow = floor(($numNavPages-1) / 2);
		 	
		 	if( $actualPage < ($startWith + $showBelow) ) {
		 		$start = $startWith;
		 		$end = $startWith + ($numNavPages - 1 );
		 	}
		 	else if( $actualPage + $showAbove > ($startWith + $totalPages - 1) ) {
		 		$end = ($startWith + $totalPages - 1);
		 		$start = $end - ($numNavPages - 1);
		 	}// else if
		 	else {
		 		$start = $actualPage - $showBelow;
		 		$end = $actualPage + $showAbove;
		 	}// else
		}// else
		
		for( $i = $start; $i <= $end; $i++ ) {
			if( $i == $actualPage ) {
				$retVal .= '<b>'.$i.'</b>&nbsp;';
			}
			else {
				$retVal .= '<a href="'.$pageName.'?'.$pageParam.'='.$i.'">'.$i.'</a>&nbsp;';
			}
		}// for $i
		 
		 
		// next-tag
		if( $actualPage < $startWith + ($totalPages - 1 )) {
			$retVal .= '<a href="'.$pageName.'?'.$pageParam.'='.( $actualPage + 1 ).'">&gt;&gt;&gt;</a>';
		}// if
	}// if

	return $retVal;	
}



?>