<?php

include_once 'config.php';
include_once 'database.php';
include_once 'templates.php';

/***************************************************************************************************
***************************************************************************************************/
function NotifyUsers( $Test = false )
{
    $MailOriginator = MAIL_ADDRESS;
    $MailSubject = BAND_NAME.' Infomail';
    
    $semi_rand = md5(time());
    $mime_boundary = "==MULTIPART_BOUNDARY_$semi_rand";
    $mime_boundary_header = chr(34) . $mime_boundary . chr(34);
	
    $i = 0;
	
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_USERS );
		while( $Row[$i] = mysql_fetch_array( $Result )) {
			$i++;
		}// while
		DBClose( $Link );
       
        $Now = time();
        $Text = GenerateText( $mime_boundary, $Now, $FarestDate, DAYS_IN_ADVANCE );
        if( $Text ) {
            $BCC ='';

           	if( DBOpen( $Link ) ) {
                if( $Test ) {
                    $BCC .= 'test@joekoperski.de'.',';
                    
                    var_dump( $BCC );
                }// if
                else {
                    // generate address list
                    for( $j = 0; $j < $i; $j++ ) {
                       if( ($FarestDate - $Row[$j]['notified']) >= DAYS_IN_ADVANCE * 24 * 3600 ) {
                            mysql_query( "UPDATE ".TAB_USERS." SET notified=".$Now." WHERE id=".$Row[$j]['id'] );
                            $BCC .= $Row[$j]['email'].',';
                        }// if
                    }// for $j
                }// else
//                $BCC .= 'Ende@joekoperski.de';
                $Header  = 'MIME-Version: 1.0'."\r\n";
                $Header .= 'Content-type: multipart/alternative; charset=iso-8859-1; boundary=' . $mime_boundary_header  . "\r\n";				
                $Header .= 'From: '.$MailOriginator."\r\n";
                $Header .= 'Reply-To: '.$MailOriginator."\r\n";
                $Header .= 'Bcc: '.$BCC."\r\n";
                DBClose( $Link );
                $MailResult = mail( NEWSLETTER_ADDRESS, $MailSubject, $Text, $Header,  '-f'.$MailOriginator  );
                
                if( $Test ) {
                    $BCC .= 'test@joekoperski.de'.',';
                    
                    var_dump( $MailResult);
                }// if

            }// if
        }// if
	}// if
}// NotifyUsers

/***************************************************************************************************
***************************************************************************************************/
function GenerateText( $mime_boundary, $Now, &$FarestDate, $DaysInAdvance = 5 )
{
    $Output = false;
    
	$TimeDiff = $DaysInAdvance * 24 * 3600; // Seven days given in seconds

	$WDays = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" );
	$Months = array( "Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember" );
	
	$Date = '';
	$PlainDate = '';
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_DATES." WHERE isnewsletter=1 ORDER BY timestamp ASC" );
		while( $RowTime = mysql_fetch_array( $Result ) ) {
 			if(  $RowTime['timestamp'] > $Now ) {
				if(( $RowTime['timestamp'] - $Now ) < $TimeDiff ) {
                    $FarestDate = $RowTime['timestamp'];
                    $Result2 = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE ID=".$RowTime['location'] );
                    $RowLocation = mysql_fetch_array( $Result2 );

                    $GoogleLink = str_replace( ' ', '+', $RowLocation['address'] );

                    $Content = array( 
                         'WEEKDAY' => $WDays[ strftime("%w", $RowTime['timestamp'] ) ]
                        ,'DAY' => strftime("%d", $RowTime['timestamp'] )				
                        ,'MONTH' => $Months[ strftime("%m", $RowTime['timestamp'] ) - 1 ]				
                        ,'YEAR' => strftime("%Y", $RowTime['timestamp'] )				
                        ,'TIME' => 	strftime("%H:%M", $RowTime['timestamp'] )			
                        ,'LOCATION' => $RowLocation['name']				
                        ,'ADDRESS' => $RowLocation['address']				
                        ,'GOOGLELINK' => $GoogleLink				
                    );
                    $Date .= ParseTemplate( 'notification_date.htm', $Content );
                    $PlainDate .= ParseTemplate( 'notification_date_plain.htm', $Content );

                    $Content = array( 'DATE' => $Date
                        ,'PLAINDATE' => $PlainDate
                        ,'MIMEBOUNDARY' => $mime_boundary
                    );
                    $Output = ParseTemplate( 'notification.htm', $Content );
				}// if
			}// if
		}// while
		DBClose( $Link );
	}// if
	else {
	}// else

    return $Output;
}// GenerateText


?>