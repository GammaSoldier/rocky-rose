<?php
/**
 * Database.php
 * Created on 20.10.2007
 *
 * @author  Joe Koperski 
 * 
 */


include_once( 'config.php' );
 
$WDays = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" );
$Months = array( "Jan", "Feb", "M&auml;r", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" );

/*******************************************************************************
*******************************************************************************/
function DBOpen( &$Link ) 
{
    $RetVal = false;
    
    $Link = mysql_connect( DB_HOST, DB_USER, DB_PASS );
    if (!$Link) {   
        echo 'No connection to DB: ' . mysql_error().'<br>';
    }// if
    if( FALSE == mysql_select_db( DB ) ) {
        echo 'DB not present<br>';
    }// if
    else {
        $RetVal = true;
    }// else
    
    return $RetVal;
}// DBOpen



/*******************************************************************************
*******************************************************************************/
function DBClose( &$Link ) 
{
    if( $Link ) {
        mysql_close($Link);
        $Link = 0;
    }// if
    
    return;
}// DBClose


 
/*******************************************************************************
*******************************************************************************/
function DBDo( $Query )
{
        $Result = mysql_query ($Query);
        
        // debug
        if( !$Result ) {
            echo 'DB Error: '.mysql_error().'<br>';
        }// if

        return $Result;
}// DBDo



?>
