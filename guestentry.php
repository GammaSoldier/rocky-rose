<?php

include('database.php');
include('ctracker.php');

$GuestbookSite = "guestbook.php";



DetectBots();
GenerateEntry( $GuestbookSite );



/***************************************************************************************************
***************************************************************************************************/
function GenerateEntry( $RedirectAddress ) 
{
	// Form and user variables
	$name = addslashes( $_REQUEST['soheissisch'] );
	$site = addslashes( $_REQUEST['meiseit'] );
	$text = $_REQUEST['ischsachsja'];
	
	$site = CreateUrl( $site );
	
	if( $name == "" ) {
		$RedirectAddress .= '?error=1&name='.urlencode($name).'&site='.$site.'&text='.urlencode($text);
	}// if
	else if ( $text == "" ) {
		$RedirectAddress .= '?error=2&name='.urlencode($name).'&site='.$site.'&text='.urlencode($text);
	}// if
	else {
		
		
		$text = trim( $text );
		$text = addslashes( $text );
//		$text = htmlspecialchars( $text );
//		die ( $text);
		$text=nl2br($text); 
		if( DBOpen( $Link ) ) {
			DBDo( "INSERT INTO ".TAB_GUESTBOOK." (timestamp, name, homepage, entry, ip ) VALUES (".time().", '".$name."', '".$site."', '".$text."', '".$_SERVER['REMOTE_ADDR']."' )" );
			DBClose( $Link );
		}// if
		else {
			die ('Could not access DB');
		}// else
	}// else
	header('Location: '.$RedirectAddress );
}// GenerateEntry



/***************************************************************************************************
***************************************************************************************************/
function CreateUrl( $Url )
{
	$RetVal = '';
	if( $Url != '' ) {
		$ParseUrl = parse_url($Url);
		// Parse the original URL
		$RetVal =  ((isset($ParseUrl['scheme'])) ? $ParseUrl['scheme'] . '://' : 'http://')
				.((isset($ParseUrl['user'])) ? $ParseUrl['user'] . ((isset($ParseUrl['pass'])) ? ':' . $ParseUrl['pass'] : '') .'@' : '')
				.((isset($ParseUrl['host'])) ? $ParseUrl['host'] : '')
				.((isset($ParseUrl['port'])) ? ':' . $ParseUrl['port'] : '')
				.((isset($ParseUrl['path'])) ? $ParseUrl['path'] : '')
				.((isset($ParseUrl['query'])) ? '?' . $ParseUrl['query'] : '')
				.((isset($ParseUrl['fragment'])) ? '#' . $ParseUrl['fragment'] : '');
	
	}// if
	
	
	return $RetVal;
}// CreateUrl



/***************************************************************************************************
***************************************************************************************************/
function DetectBots() 
{
	$Spamid = 		$_REQUEST['icq'];
	$HPName =	 	$_REQUEST['UserName'];
	$HPEMail =	 	$_REQUEST['EMail'];
	$HPHomepage =	$_REQUEST['Homepage'];
	$HPHomepage2 =	$_REQUEST['heimseit'];
	$HPText	= 		$_REQUEST['Post'];

	// Hiddenfields
	if($Spamid != "" ) {
		Redirect();
	}// if

	if( $HPName != '' ) {
        Redirect();
	}// if

	if( $HPEMail != '' ) {
		Redirect();
	}// if

	if( $HPHomepage != '' ) {
		Redirect();
	}// if

	if( $HPHomepage2 != '' ) {
		Redirect();
	}// if

	if( $HPText != '' ) {
		Redirect();
	}// if

}// DetectBots



/***************************************************************************************************
***************************************************************************************************/
function Redirect()
{
	// addresses to where a spammer is redirected
	$SpamAddress = array(
		 'http://virtuo.myminicity.com/env'
		,'http://virtuo.myminicity.com/ind'
		,'http://virtuo.myminicity.com/ind'
		,'http://virtuo.myminicity.com/sec'
		,'http://virtuo.myminicity.com/tra'
		,'http://virtuo.myminicity.com/com'
		,'http://virtuo.myminicity.com/'
		,'http://virtuo.myminicity.com/'
		,'http://virtuo.myminicity.com/'
	);

	$WayToGo = time() % (count( $SpamAddress ) );	
	$RedirectAddress = $SpamAddress[$WayToGo];

	header('Location: '.$RedirectAddress );
    exit();
}// Redirect

?>