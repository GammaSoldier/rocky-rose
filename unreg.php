<?php

include_once 'config.php';
include 'templates.php';
include_once 'database.php';

$Output = '';

$Output = execute();

$Content = array( 'CONTENT' => $Output );
$Output = ParseTemplate( 'unreg.htm', $Content );

$Content = array( 'CONTENT' => $Output );
$Output = ParseTemplate( 'site.htm', $Content );

echo $Output;




/***************************************************************************************************
***************************************************************************************************/
function execute() 
{
	$Output = '';
    
	if( isset( $_REQUEST['user'] )) {
		if( DBOpen( $Link ) ) {
			$Result = mysql_query( "SELECT * FROM ".TAB_USERS." WHERE hash='".$_REQUEST['user' ]."'" );
			$Row = mysql_fetch_array( $Result );

			if( $Row ) {
				DBDo( "DELETE FROM ".TAB_USERS." WHERE hash='".$_REQUEST['user' ]."'" );

				$Output	.= '<br><br><br><br>Du wurdest vom Verteiler der '.BAND_NAME.' Info-Mail gel&ouml;scht.';
			}// if
			else {
				$Output	.= '<br><br><br><br>Deine E-Mail-Adresse ist nicht im Verteiler der '.BAND_NAME.' Info-Mail enthalten. <br>Solltest du weiterhin unerwünschte Post von uns erhalten, wende dich bitte per Mail an uns über den Contact-Link oben auf der Seite.';
			}// else

			DBClose( $Link );

			if( $NotifyFlag ) {
				NotifyUser( $Row['id'], $Row['email'], $Row['hash'], $Row['notified'] );
			}// if

		}// if
		else {
			$Output .= 'Could not access DB';
		}// else

	}// if
	
	return $Output;
}// execute



?>