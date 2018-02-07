<?php

include 'database.php';

execute();
//import();


/*******************************************************************************
*******************************************************************************/
function execute() 
{
	$Errors = 0;
	
	// connect to DB
	$dblink = mysql_connect( DB_HOST, DB_USER, DB_PASS );
	if (!$dblink) {
	   die('Keine Verbindung zur Datenbank.');
	}
	
	// select DB
	$dbselected = mysql_select_db( DB, $dblink );
	if (!$dbselected) {
	   die ('Kann Datenbank nicht erreichen.');
	}// if

	// Insert table
	$Query = "CREATE TABLE IF NOT EXISTS ".TAB_DATES." (
				  id 			int(11) NOT NULL auto_increment
				, timestamp     int(11)
				, location	    int(11)
				, cancelled	    int(11)
                , isnewsletter  int(11)
				, PRIMARY KEY	(id)
				)";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_DATES.' Not created: '.mysql_error().'<br>';
		$Errors++;
	}// if

	// Insert table
	$Query = "CREATE TABLE IF NOT EXISTS ".TAB_LOCATIONS." (
				  id 		int(11) NOT NULL auto_increment
				, name		varchar(255)
				, address 	varchar(255)
				, remarks	varchar(1000)
				, PRIMARY KEY	(id)
				)";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_LOCATIONS.' Not created: '.mysql_error().'<br>';
		$Errors++;
	}// if

	// Insert table
	$Query = "CREATE TABLE IF NOT EXISTS ".TAB_USERS." (
				  id 		int(11) NOT NULL auto_increment
				, email		varchar(255)
				, hash		varchar(32)
				, notified	int(11)
				, PRIMARY KEY	(id)
				)";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_USERS.' Not created: '.mysql_error().'<br>';
		$Errors++;
	}// if

	// Insert table
	$Query = "CREATE TABLE IF NOT EXISTS ".TAB_GUESTBOOK." (
				  id 		int(11) NOT NULL auto_increment
				, timestamp int(11)
				, name		varchar(100)
				, homepage	varchar(200)
				, entry		varchar(1000)
				, ip		varchar(32)
				, PRIMARY KEY	(id)
				)";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_GUESTBOOK.' Not created: '.mysql_error().'<br>';
		$Errors++;
	}// if


	// Insert table
	$Query = "CREATE TABLE IF NOT EXISTS ".TAB_PICTURES." (
				  id 		int(11) NOT NULL auto_increment
				, name		varchar(100)
				, position	int(11)
				, gallery	int(11)
				, PRIMARY KEY	(id)
				)";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_PICTURES.' Not created: '.mysql_error().'<br>';
		$Errors++;
	}// if


	// Insert table
	$Query = "CREATE TABLE IF NOT EXISTS ".TAB_CONTENT." (
				  nameid	varchar(100)
				, content	text
				, PRIMARY KEY	(nameid)
				)";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_CONTENT.' Not created: '.mysql_error().'<br>';
		$Errors++;
	}// if
	
	// Insert Content
	$Query = "INSERT INTO ".TAB_CONTENT." ( nameid, content ) VALUES ( 'setlist', '' )
			  ON DUPLICATE KEY UPDATE content=content";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_CONTENT.' content "setlist" not created: '.mysql_error().'<br>';
		$Errors++;
	}// if
	
	
    
    	// Insert table
	$Query = "CREATE TABLE IF NOT EXISTS ".TAB_SETTINGS." (
				  nameid	varchar(100)
				, value	    integer
				, content	text
				, PRIMARY KEY	(nameid)
				)";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_SETTINGS.' Not created: '.mysql_error().'<br>';
		$Errors++;
	}// if
	
	// Insert Content
	$Query = "INSERT INTO ".TAB_SETTINGS." ( nameid, value, content ) VALUES ( 'showpastdates', 0, '' )
			  ON DUPLICATE KEY UPDATE value=value";
	
	$result = mysql_query( $Query );
	if( !$result ) {
		echo TAB_SETTINGS.' content "setlist" not created: '.mysql_error().'<br>';
		$Errors++;
	}// if
	

	echo 'Installation done. '. $Errors .' errors.<br>';

	// Verbindung zur Datenbank schlie?n
	mysql_close($dblink);
}// execute



/*******************************************************************************
*******************************************************************************/
function import()
{
	$Dates = file( 'dates.txt' );

	if( DBOpen( $Link )) {
	
		// Locations
		for( $i = 0; $i < count( $Dates ); $i++ ) {
			$Entry = explode( ';', $Dates[ $i ] );
				
			$Location = mysql_escape_string( $Entry[2] );
			$Address= trim( mysql_escape_string( $Entry[3] ));

			$result = mysql_query( "SELECT COUNT(name) AS Location FROM ".TAB_LOCATIONS." WHERE name='".$Location."'" );
			$row = mysql_fetch_row( $result );
			if( $row[0] == 0 ) {
				DBDo( "INSERT INTO ".TAB_LOCATIONS." (name, address) VALUES ('".$Location."', '".$Address."')" );
			}
		}// for $i

		// Dates
		for( $i = 0; $i < count( $Dates ); $i++ ) {
			$Entry = explode( ';', $Dates[ $i ] );
		
			$Location = mysql_escape_string( $Entry[2] );
			
			$TimeString = explode( ' ', $Entry[1] );
			$Timestamp = strtotime( $Entry[0].' '.$TimeString[0]  );
			
			echo '<br>';
			$result = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE name='".$Location."'" );
			$row = mysql_fetch_row( $result );
			
			var_dump( $row );
			echo '<br>';
			
			DBDo( "INSERT INTO ".TAB_DATES." (timestamp, location, cancelled ) VALUES ('".$Timestamp."', '".$row[0]."', 0)" );
		}// for $i
	
		DBClose( $Link );
	}// if
}// import 




?>
