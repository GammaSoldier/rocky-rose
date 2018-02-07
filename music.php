<?php

include_once 'templates.php';
include_once 'database.php';

// read setlist
if( DBOpen( $Link ) ) {
	$Result = mysql_query( "SELECT * FROM ".TAB_CONTENT." WHERE nameid = 'setlist'" );
	if( $Result ) {
		$Row = mysql_fetch_assoc( $Result );
		$Content = $Row['content'];
	}// if
	DBClose( $Link );
}// if


$Setlist = array( 'SETLIST' 	=> stripslashes( $Content ) );
$Output = ParseTemplate( 'music.htm', $Setlist );

$Content = array( 'CONTENT' => $Output );
$Output = ParseTemplate( 'site.htm', $Content );

echo $Output;
?>

