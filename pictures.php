<?php
include 'templates.php';
include 'database.php';
include_once 'config.php';


if( isset( $_GET['Actual'] )) {
	$Actual = $_GET['Actual'];
}// if
else {
	$Actual = 0; 
}// else


$i = 0;
if( DBOpen( $Link ) ) {
	$Result = mysql_query( "SELECT * FROM ".TAB_PICTURES." ORDER BY position ASC" );
	if( $Result ) {
		while( $Row = mysql_fetch_assoc( $Result ) ) {
			$FileList[$i] = $Row[ 'name' ];
			$i++;
		}// while
	}// if
	DBClose( $Link );
}// if



$ThisFile = $_SERVER['SCRIPT_NAME'];



// illegal values
if( $Actual < 0 ) {
	$Actual = 0;
}// if
if( isset($FileList ) && $Actual >= count($FileList) ) {
	$Actual = count($FileList)-1;
}// if


// Navigation 	
if( $Actual > 0) {
	$Content = array( 'LINK' => $ThisFile.'?Actual='.($Actual-1) );
	$OutputBack = ParseTemplate( 'pictures_back.htm', $Content );
}// if 
else {
	$OutputBack = '';
}// else

if( $Actual < count($FileList)-1 ){
	$Content = array( 'LINK' => $ThisFile.'?Actual='.($Actual+1) );
	$OutputForward = ParseTemplate(  'pictures_forward.htm', $Content );
}// if 
else {
	$OutputForward = '';
}// else


// Content
$Content = array( 
	 'BACK' => $OutputBack
	,'FORWARD' => $OutputForward 
	,'IMAGE' => PIC_IMAGE_DIR.$FileList[$Actual] 
	,'ALT' => $FileList[$Actual]
);

$Output = ParseTemplate( 'pictures.htm', $Content );

$Content = array( 'CONTENT' => $Output );
$Output = ParseTemplate( 'site.htm', $Content );

echo $Output;



?>
