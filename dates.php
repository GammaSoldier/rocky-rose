<?php

include 'database.php';
include 'templates.php';
 
// $WDays = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" );
// $Months = array( "Jan", "Feb", "M&auml;r", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" );

$Now = time();

$Output = '';

if( DBOpen( $Link ) ) {
	// display dates in future
    $result = mysql_query( "SELECT * FROM ".TAB_DATES." WHERE timestamp > ".$Now." ORDER BY timestamp ASC" );
	if( !$result ) {
		// No dates found
		$Output = GetTemplate( 'dates_list_empty.htm' );
	}// if
	else {
		if( mysql_num_rows( $result ) != 0 ) {
			while( $RowTime = mysql_fetch_array( $result ) ) {
				if( $RowTime['timestamp'] >= $Now ) {
					$result2 = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE ID=".$RowTime['location'] );
					$RowLocation = mysql_fetch_array( $result2 );
                    
					$Ausgabe = strtok( ";" );
					$GoogleLink = str_replace( ' ', '+', $RowLocation['address'] );
                    
					// display date
					$Content = array(
						 'WEEKDAY' => $WDays[ strftime('%w', $RowTime['timestamp'] ) ]
						,'DAY' => strftime("%d", $RowTime['timestamp'] )
						,'MONTH' => $Months[ strftime("%m", $RowTime['timestamp'] ) - 1 ]
						,'YEAR' => strftime("%Y", $RowTime['timestamp'] )
						,'TIME' => strftime("%H:%M", $RowTime['timestamp'] )
						,'LOCATION' => $RowLocation['name']
						,'ADDRESS' => $RowLocation['address']
						,'GOOGLELINK' => $GoogleLink
					);
					$Output .= ParseTemplate( 'dates_list.htm', $Content );
				}// if
			}// while
		}// if
		else {
			// No dates found
			$Output = ParseTemplate( 'dates_list_empty.htm', array('' => '') );
		}// else
	}// else

    // display past dates
	$result = mysql_query( "SELECT * FROM ".TAB_SETTINGS." WHERE nameid = 'showpastdates'" );
	if( $result ) {
		$Row = mysql_fetch_array( $result );
        $NumEntriesToDisplay = $Row[ 'value' ];
        
        if( $NumEntriesToDisplay != 0 ) {
            $result = mysql_query( "SELECT * FROM ".TAB_DATES." WHERE timestamp <= ".$Now." ORDER BY timestamp DESC" );
            if( !$result ) {
                // No dates found
                $Output2 = GetTemplate( 'dates_list_past_empty.htm' );
            }// if
            else {
                $i = 0;
                if( ($Temp = mysql_num_rows( $result )) != 0 ) {
                    if( $NumEntriesToDisplay < 0 ) {
                        $NumEntriesToDisplay = $Temp;
                    }// if 
                    $Output2 = '';
                    while( ($RowTime = mysql_fetch_array( $result )) && ($i < $NumEntriesToDisplay )) {
                        if( $RowTime['timestamp'] <= $Now ) {
                            $i++;
                            $result2 = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE ID=".$RowTime['location'] );
                            $RowLocation = mysql_fetch_array( $result2 );

                            $Ausgabe = strtok( ";" );
                            $GoogleLink = str_replace( ' ', '+', $RowLocation['address'] );
                            
                            // display date
                            $Content = array(
                                 'WEEKDAY' => $WDays[ strftime('%w', $RowTime['timestamp'] ) ]
                                ,'DAY' => strftime("%d", $RowTime['timestamp'] )
                                ,'MONTH' => $Months[ strftime("%m", $RowTime['timestamp'] ) - 1 ]
                                ,'YEAR' => strftime("%Y", $RowTime['timestamp'] )
                                ,'LOCATION' => $RowLocation['name']
                                ,'ADDRESS' => $RowLocation['address']
                                ,'GOOGLELINK' => $GoogleLink
                            );
                            $Output2 .= ParseTemplate( 'dates_list_past.htm', $Content );
                        }// if
                    }// while
                }// if
                else {
                    // No dates found
                    $Output2 = ParseTemplate( 'dates_list_empty.htm', array('' => '') );
                }// else
            }// else
            $Content = array( 'DATELISTPAST' => $Output2 );
            $Output2 = ParseTemplate( 'dates_past.htm', $Content );
        }// if
        else {
            $Output2 = '';
        }// else
	}// if

	DBClose( $Link );
}// if

else {
}// else

$Content = array( 'DATELIST' => $Output
                 ,'DATESPAST' => $Output2);
$Output = ParseTemplate( 'dates.htm', $Content );


$Content = array( 'CONTENT' => $Output );
$Output = ParseTemplate( 'site.htm', $Content );

echo $Output;


?>




