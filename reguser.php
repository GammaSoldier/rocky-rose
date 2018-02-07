<?php

include_once 'database.php';
include 'notify.php';
include 'ctracker.php';
include_once 'templates.php';


$NotifyFlag = false;
$Output = '';	

//var_dump($_POST);
//die(  ); 

	global $MailOriginator;
	global $MailSubject;

if( isset( $_REQUEST[ 'email' ] ) ) {
    $EMail = $_REQUEST[ 'email' ];

    // check for exactly one '@' and '.' like 'joe@here.net'
    if( !preg_match( "/^[^@]+@[^@]+\..+$/", $EMail ) ) {
        $Output	= GetTemplate( 'reguser_invalid.htm' );
    }// if 
    else {
        $Hash = md5( $EMail );
        if( DBOpen( $Link ) ) {
            $Result = mysql_query( "SELECT * FROM ".TAB_USERS." WHERE hash='".$Hash."'" );
            $Row = mysql_fetch_array( $Result );
            if( $_REQUEST[ 'subscribe' ] == 'subscribe') {
                if( !$Row ) {
                    DBDo( "INSERT INTO ".TAB_USERS." (email, hash, notified ) VALUES ('".$EMail."', '".$Hash."', '0')" );
                    
                    // do query to get the id for the call to NotifyUser
                    $Result = mysql_query( "SELECT * FROM ".TAB_USERS." WHERE hash='".$Hash."'" );
                    $Row = mysql_fetch_array( $Result );
                    $NotifyFlag = true;

                    $Output	= GetTemplate( 'reguser_acknowledge.htm' );
                }// if
                else {
                    $Output	= GetTemplate( 'reguser_not_acknowledge.htm' );
                }// else
                //if( $NotifyFlag ) {
                //    NotifyUser( $Row['id'], $Row['email'], $Row['hash'], $Row['notified'] );
                //}// if
            }// if
            else {
                //unsubscribe

                $Header  = 'MIME-Version: 1.0'."\r\n";
                $Header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";				
                $Header .= 'From: '.MAIL_ADDRESS."\r\n";
                $Header .= 'Reply-To: '.MAIL_ADDRESS."\r\n";
                $Content = array( 'HASH' => $Hash );
                $Output = ParseTemplate( 'notification_delete.htm', $Content );
                mail( $Row['email'], BAND_NAME.' Infomail', $Output, $Header, '-f'.MAIL_ADDRESS );

                $Output	= GetTemplate( 'unreguser_acknowledge.htm' );

           }// else
           DBClose( $Link );
        }// if
        else {
            $Output = 'Could not access DB';
        }// else
    }// else
}// if






$Content = array( 'OUTPUT' => $Output );
$Content = array( 'CONTENT' => ParseTemplate( 'reguser.htm', $Content ) );
$Output = ParseTemplate( 'site.htm', $Content );

echo $Output;
?>

