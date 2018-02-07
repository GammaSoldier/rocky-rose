<?php

include_once( 'config.php' );
include_once( 'templates.php' );
include_once 'database.php';

if( !isset( $_REQUEST['page'] ) ) {
    $Page = '';
}// if
else {
    $Page = $_REQUEST['page'];
}// else


if( file_exists( TEMPLATE_PATH . $Page.'.htm' ) ) {
    $Content = array( 'CONTENT' => GetTemplate( $Page.'.htm' ) );
}// if
else {
    $Content = array( 'CONTENT' => GetTemplate( 'home.htm' ) );
    
    
    $Output = '';
    $Now = time();
	$TimeDiff = 30 * 24 * 3600; // 30 days given in seconds

    // Get next date
    if( DBOpen( $Link ) ) {
        // display next dates in future
        $result = mysql_query( "SELECT * FROM ".TAB_DATES." WHERE timestamp > ".$Now." AND isnewsletter=1  ORDER BY timestamp ASC" );
        if( $result ) {
            if( mysql_num_rows( $result ) != 0 ) {
                if( $RowTime = mysql_fetch_array( $result ) ) {
                    if( ( $RowTime['timestamp'] - $Now ) < $TimeDiff ) {
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
                        $Output .= ParseTemplate( 'news.htm', $Content );
                    }// if
                }// if
                else {
//                    die( 'could not fetch content from result' );
                }// else
            }// if
            else {
//                die( 'no content from db' );
            }// else
        }// if
        else {
//            die( 'no query result' );
        }// else
    }// if
    else {
//        die( 'could not open db' );
    }// else


    $Content = array( 'NEWS' => $Output);
    $Output = ParseTemplate( 'home.htm', $Content );
        
    $Content = array( 'CONTENT' => $Output );
    
}// else



$Output = ParseTemplate( 'site.htm', $Content );



echo $Output;
?>
