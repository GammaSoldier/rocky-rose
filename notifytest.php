<?php
define ('DAYS_IN_ADVANCE', 100 );
define ('TEST_ADDRESS', 'contact@joekoperski.de' );
//define ('TEST_ADDRESS', 'joachim.koperski@leuze.de' );


include_once 'database.php';
include_once 'notify.php';


// execute();
NotifyUsers( true );

/***************************************************************************************************
***************************************************************************************************/
function execute()
{
	$i = 0;
	
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_USERS." WHERE email='".TEST_ADDRESS."'" );
		while( $Row[$i] = mysql_fetch_array( $Result )) {
			$i++;
		}// while

		DBClose( $Link );

		for( $j = 0; $j < $i; $j++ ) {
			NotifyUser( -1, TEST_ADDRESS, $Row[$j]['hash'], 0, DAYS_IN_ADVANCE );
		}// for $j
	}// if
}// execute

?>