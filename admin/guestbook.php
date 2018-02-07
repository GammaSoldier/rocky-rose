<?php



/***************************************************************************************************
***************************************************************************************************/
function GuestbookShowSite()
{
    $Content = array( 'TITLE'               => 'Guestbook'
                     ,'SITE'                => $_SERVER['PHP_SELF']
					 ,'LISTENTRIES'         => GuestbookListEntries( false )
					 );
					 
	return ParseTemplate( 'guestbook.htm', $Content );


}// GuestbookShowSite



/***************************************************************************************************
***************************************************************************************************/
function GuestbookListEntries()
{
	if( DBOpen( $Link ) ) {
		$LocalOutput = '';
		$Result = mysql_query( "SELECT * FROM ".TAB_GUESTBOOK );

		$ColorCounter = 0;
		while( $Row = mysql_fetch_array( $Result )) {
            $Content = array( 'ODD'         => 'odd'
                             ,'NAME'        => $Row['name']
                             ,'DATE'        => date( 'd.m.y H:i', $Row['timestamp'] )
                             ,'HOMEPAGE'    => $Row['homepage']
                             ,'TEXT'        => $Row[ 'entry' ]
                             ,'IP'          => $Row['ip']
                             ,'ID'          => $Row['id']
					 );

            if( !$ColorCounter ) {
				$Content['ODD'] = 'even';
			}// if
			$ColorCounter = 1 - $ColorCounter;
            $LocalOutput .= ParseTemplate( 'guestbook_entry.htm', $Content );
		}// while
		DBClose( $Link );
	}// if
	else {
		$LocalOutput = 'Could not access DB';
	}// else
    
    return $LocalOutput;
}// GuestbookListEntries

function GuestbookDelete( $Selection ) 
{
	$Output = true;

	if( DBOpen( $Link ) ) {
        $NumEntries = count( $Selection );
		for( $i = 0; $i < $NumEntries; $i++ ) {
            $Result = mysql_query( "DELETE FROM ".TAB_GUESTBOOK." WHERE ID='".$Selection[$i]."'" );
        }// for $i
		DBClose( $Link );
	}// if
	else {
		$Output .= 'Could not access DB';
	}// else
    
    return $Output;
}

?>