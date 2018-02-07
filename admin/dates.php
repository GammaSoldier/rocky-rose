<?php


/***************************************************************************************************
***************************************************************************************************/
function ShowLists() 
{
    $Content = array( 'TITLE'               => 'Dates'
                     ,'FORMLOCATION'        => FormLocation( 'enternewlocation', '', '', '', 0 )
                     ,'FORMSETTINGS'        => FormSettings()
					 ,'LISTLOCATIONS'       => ListLocations()
					 ,'LISTDATES'           => ListDates( false )
					 ,'LISTDATESPAST'       => ListDates( true )
					 );
					 
	return ParseTemplate( 'locations.htm', $Content );
   
}// ShowLists



/***************************************************************************************************
***************************************************************************************************/
function DeleteDate()
{
	$Output = true;

	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "DELETE FROM ".TAB_DATES." WHERE ID='".$_REQUEST['dateid']."'" );
		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else
    return $Output;
}// DeleteDate


/***************************************************************************************************
***************************************************************************************************/
function CancelDate()
{
	$Output = true;;

	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_DATES." WHERE ID='".$_REQUEST['dateid']."'" );
		if( $Result ) {
			$Row = mysql_fetch_array( $Result );
		}// if
		// send e-mail to all users and delete date from db
		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else
    return $Output;
}// CancelDate



/***************************************************************************************************
***************************************************************************************************/
function StoreSettings()
{
	$Output = true;;

    $Num = $_REQUEST['pastdates'];
    if( $_REQUEST['pastdates'] == 'All' )  {
        $Num = -1;
    }// if
    else{
        $Num = $_REQUEST['pastdates'];
    }// else
 
	if( DBOpen( $Link ) ) {
		$Result = mysql_query(   "UPDATE ".TAB_SETTINGS. " SET value='"
                               . $Num
				               . "' WHERE nameid='showpastdates'" );

        if( !$Result ) {
			$Output = 'Could not access DB';
		}// if
		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else
    return $Output;
}// StoreSettings




/***************************************************************************************************
***************************************************************************************************/
function ConfirmDateDeletion()
{
	if( DBOpen( $Link ) ) {
		$result = mysql_query( "SELECT * FROM ".TAB_DATES." WHERE id=".$_REQUEST['date'] );
		$RowTime = mysql_fetch_array( $result );

		$result2 = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE ID=".$RowTime['location'] );
		$RowLocation = mysql_fetch_array( $result2 );

		if( !isset($RowLocation['name']) ) {
			$RowLocation['name'] = 'Location deleted';
		}// else

		$Date = strftime("%d", $RowTime['timestamp'] ).". ";
		$Date .= strftime("%m", $RowTime['timestamp'] ).". ";
		$Date .= strftime("%Y", $RowTime['timestamp'] );
        
        $Content = array( 'DATE'            => $Date
                         ,'NAME'            => $RowLocation['name']
                         ,'ADDRESS'         => $RowLocation['address']
                         ,'SITE'             => $_SERVER['PHP_SELF']
                         ,'REQUEST_DATE'     => $_REQUEST['date']
                         );
                         
        $Output = ParseTemplate( 'locationdeletedateconfirm.htm', $Content );
		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else
    
    return $Output;
}// ConfirmDateDeletion


/***************************************************************************************************
***************************************************************************************************/
function NewDate()
{
	$Output = true;
	$Newsletter = 0;
    
	$Timestamp = mktime( $_REQUEST['hour']
						,$_REQUEST['minute']
						,0
						,$_REQUEST['month']
						,$_REQUEST['day']
						,$_REQUEST['year']
						);
	
    if( isset($_REQUEST['newsletter']) && $_REQUEST['newsletter'] != NULL ) {
           $Newsletter = 1;
    }// if

	// date in the past
	if( $Timestamp < time() ) {
		$Output .= 'This date is already in the past. Try one in the future.';
	}//if
	else {
		if( DBOpen( $Link ) ) {
			DBDo( "INSERT INTO ".TAB_DATES." (timestamp, location, cancelled, isnewsletter ) VALUES ('".$Timestamp."', '".$_REQUEST['location']."', 0, '".$Newsletter."')" );
			DBClose( $Link );
		}// if
		else {
			$Output .= 'Could not access DB';
		}// else
	}// else
	
	return $Output;
}// NewDate



/***************************************************************************************************
***************************************************************************************************/
function EnterNewDate()
{

	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE ID='".$_REQUEST['location']."'" );
		if( $Result ) {
			$Row = mysql_fetch_array( $Result );
		}// if

		$Now = getdate(time());

		$DaySelector = '';
        for( $i=1; $i<=31; $i++ ) {
			$DaySelector .= '<option';
			if( $i == $Now['mday'] ) {
				$DaySelector .= ' selected';
			}// if
			$DaySelector .= '>'.$i.'</option>';
		}// for $i

		$MonthSelector = '';
		for( $i=1; $i<=12; $i++ ) {
			$MonthSelector .= '<option';
			if( $i == $Now['mon'] ) {
				$MonthSelector .= ' selected';
			}// if
			$MonthSelector .= '>'.$i.'</option>';
		}// for $i

		$YearSelector = '';
        for( $i=$Now['year']; $i <= $Now['year']+9; $i++ ) {
			$YearSelector .= '<option';
			if( $i == $Now['year'] ) {
				$YearSelector .= ' selected';
			}// if
			$YearSelector .= '>'.$i.'</option>';
		}// for $i
        $Content = array( 'NAME'      => $Row['name']
                         ,'ADDRESS'        => $Row['address']
                         ,'SITE'        => $_SERVER['PHP_SELF']
                         ,'DAYSELECTOR'        => $DaySelector
                         ,'MONTHSELECTOR'        => $MonthSelector
                         ,'YEARSELECTOR'        => $YearSelector
                         ,'LOCATION'        => $_REQUEST['location']
                         );
                         
        $Output= ParseTemplate( 'locationenterdate.htm', $Content );

		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else

	return $Output;
}// EnterNewDate



/***************************************************************************************************
***************************************************************************************************/
function DeleteLocationConfirm() 
{
	
    $Content = array( 'SITE'      => $_SERVER['PHP_SELF']
                     ,'ID'        => $_REQUEST['locationid']
					 );
					 
	return ParseTemplate( 'locationdeleteconfirm.htm', $Content );
    
    
}// DeleteLocationConfirm


/***************************************************************************************************
***************************************************************************************************/
function DeleteLocation()
{
	$Output = true;

	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "DELETE FROM ".TAB_LOCATIONS." WHERE ID='".$_REQUEST['locationid']."'" );
		DBClose( $Link );
	}// if
	else {
		$Output .= 'Could not access DB';
	}// else
    
    return $Output;
}// DeleteLocation



/***************************************************************************************************
***************************************************************************************************/
function EditLocation()
{
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE ID='".$_REQUEST['location']."'" );
		if( $Result ) {
			$Row = mysql_fetch_array( $Result );
			$Output = FormLocation( 'modifylocation', $Row['name'], $Row['address'], $Row['remarks'], $_REQUEST['location'] );
		}// if
		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else
    
    return $Output;
}// EditLocation


/***************************************************************************************************
***************************************************************************************************/
function ModifyLocation() 
{
	$Output = true;

	if( DBOpen( $Link ) ) {
		$Query = "UPDATE ".TAB_LOCATIONS." SET name='"
				.mysql_escape_string($_REQUEST['locationname'])
				."', address='"
				.mysql_escape_string($_REQUEST['locationaddress'])
				."', remarks='"
				.mysql_escape_string($_REQUEST['locationremarks'])
				."' WHERE id="
				.$_REQUEST['locationid'];
				
		DBDo( $Query );
		DBClose( $Link );
	}// if
	else {
		$Output = '<br>Could not access DB';
	}// else
	
    return $Output;
}// ModifyLocation



/***************************************************************************************************
***************************************************************************************************/
function NewLocation() 
{
	$Output = true;

	if( DBOpen( $Link ) ) {
		DBDo( "INSERT INTO ".TAB_LOCATIONS." (name, address, remarks) VALUES ('"
				.mysql_escape_string($_REQUEST['locationname'])
				."', '"
				.mysql_escape_string($_REQUEST['locationaddress'])
				."', '"
				.mysql_escape_string($_REQUEST['locationremarks'])
				."')" );
		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else
	return $Output;
    
}// NewLocation


/***************************************************************************************************
***************************************************************************************************/
function CheckNewLocation()
{
	$Output = '';
    
    if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE name='".$_REQUEST['locationname']."'" );
		DBClose( $Link );

		if( $Result ) {
			$Row = mysql_fetch_array( $Result );
			if( $Row['name'] == $_REQUEST['locationname'] ) {
                $Content = array( 'NAME'            => $Row['name']
                                 ,'ADDRESS'         => $Row['address']
                                 ,'REMARKS'         => $Row['remarks']
                                 ,'NAME_NEW'        => $_REQUEST['locationname']
                                 ,'ADDRESS_NEW'     => $_REQUEST['locationaddress']
                                 ,'REMARKS_NEW'     => $_REQUEST['locationremarks']
                                 ,'SITE'            => $_SERVER['PHP_SELF']
                                 );
                                 
                $Output = ParseTemplate( 'locationexists.htm', $Content );
			}// if
			else {
				$Output = NewLocation();
			}// else
		}
		else {
			$Output = NewLocation();
		}// else
			
	}// if
	else {
		$Output = 'Could not access DB';
	}// else

	if( $Output == '' ) { 
        return true;
    }// if
 
    return $Output;
}// CheckNewLocation

/***************************************************************************************************
***************************************************************************************************/
function FormLocation( $Action, $Name, $Address, $Remarks, $ID )
{
    $Content = array( 'TITLE'           => 'New Location'
                     ,'SITE'            => $_SERVER['PHP_SELF']
                     ,'ACTION'          => $Action
                     ,'NAME'            => $Name
                     ,'ADDRESS'         => $Address
                     ,'REMARKS'         => $Remarks
                     ,'ID'              => $ID
                     ,'BUTTONDELETE'    => ''
                    );
	if( $Action != 'enternewlocation' ) {
        $Content['TITLE'] = 'Location';
        $Content['BUTTONDELETE'] = ' <input type="submit" name="btnsend" value="Delete" style="width: 100px;"/>';
	}// if
 					 
	$Output = ParseTemplate( 'locationsform.htm', $Content );
   
    return $Output;
}// FormLocation


/***************************************************************************************************
***************************************************************************************************/
function FormSettings()
{

    $SelectorValues = array ( 0, 1, 3, 5, 10, 15, 20 );


	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_SETTINGS." WHERE nameid='showpastdates'" );
		if( $Result ) {
			$Row = mysql_fetch_array( $Result );
		}// if

		$PastDatesSelector = '';
        for( $i=0; $i < count( $SelectorValues ); $i++ ) {
			$PastDatesSelector .= '<option';
			if( $SelectorValues[$i] == $Row['value'] ) {
				$PastDatesSelector .= ' selected';
			}// if
			$PastDatesSelector .= '>'.$SelectorValues[$i] .'</option>';
		}// for $i
 
        if( $Row['value'] < 0 ) {
            $PastDatesSelector .= '<option selected>All</option>';
            $Num = 'All';
        }// if
        else {
            $PastDatesSelector .= '<option>All</option>';
            $Num = $Row['value'];
        }// else

        
        $Content = array( 'SITE'                => $_SERVER['PHP_SELF']
                         ,'NUMPASTDATES'        => $Num
                         ,'PASTDATESSELECTOR'   => $PastDatesSelector
                        );

                         
        $Output = ParseTemplate( 'pastdatesform.htm', $Content );
        

		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else




   
    return $Output;
}// FormSettings




/***************************************************************************************************
***************************************************************************************************/
function ListLocations() 
{
	if( DBOpen( $Link ) ) {
		$LocalOutput = '';
		$Result = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." ORDER BY name ASC" );

		$ColorCounter = 0;
		while( $Row = mysql_fetch_array( $Result )) {
			
            $Content = array( 'ODD'     => 'odd'
                             ,'SITE'    => $_SERVER['PHP_SELF']
                             ,'ID'      => $Row['id']
                             ,'NAME'    => $Row['name']
                             ,'ADDRESS' => $Row['address']
                             ,'REMARKS' => ''
                             );

            if( !$ColorCounter ) {
				$Content['ODD'] = 'even';
			}// if
			
			$Remarks = htmlentities( mysql_real_escape_string( $Row['remarks'] ) );
			$Remarks = str_replace ('\n', '<br>', $Remarks);
			$Remarks = str_replace ('\r', '', $Remarks);
            $Content['REMARKS'] = $Remarks;
			
			$ColorCounter = 1 - $ColorCounter;

            $LocalOutput .= ParseTemplate( 'locationentry.htm', $Content );
		}// while

        $Content = array( 'LOCATIONS' => $LocalOutput );
        $Output = ParseTemplate( 'locationlist.htm', $Content );

		DBClose( $Link );
	}// if
	else {
		$Output = 'Could not access DB';
	}// else
    
    return $Output;
}// ListLocations



/***************************************************************************************************
***************************************************************************************************/
function ListDates( $Past )
{
	$Output = '';
    $NewsletterInfo = array( '-', 'Newsletter' );
    $NewsletterInfoIndex;
 
	if( DBOpen( $Link ) ) {
		$Now = time();
		
		$ColorCounter = 0;
		$DateCounter = 0;
		
		$LocalOutput = '';
		$result = mysql_query( "SELECT * FROM ".TAB_DATES." ORDER BY timestamp ASC" );
		while( $RowTime = mysql_fetch_array( $result ) ) {
			$DateCounter++;
			$result2 = mysql_query( "SELECT * FROM ".TAB_LOCATIONS." WHERE ID=".$RowTime['location'] );
			$RowLocation = mysql_fetch_array( $result2 );
			
			// location id doesn't exist, may have been deleted
			if( !isset($RowLocation['name']) ) {
				$RowLocation['name'] = 'Location deleted';
			}// else
			$NewsletterInfoIndex = $RowTime['isnewsletter'] ? 1: 0;
 
            $Content = array( 'ODD'             => 'odd'
                             ,'FIRST_COLUMN'    => $_SERVER['PHP_SELF']
                             ,'DATE'            => ''
                             ,'TEXT_CLASS'      => ''
                             ,'NAME'            => $RowLocation['name']
                             ,'ADDRESS'         => $RowLocation['address']
                             ,'NEWSLETTER'      => $NewsletterInfo[$NewsletterInfoIndex]
                             );
            

			// set color and deletion link for dates in future
			$Content['TEXT_CLASS'] = '';
			$TextClass = '';
			if( $RowTime['timestamp'] >= $Now ) {
				$Content['FIRST_COLUMN'] = '<a href="'.$_SERVER['PHP_SELF'].'?action=confirmdatedeletion&date='.$RowTime['id'].'">delete</a>';
				$Content['TEXT_CLASS'] = 'Future';
 			}// if
			else{
				$Content['FIRST_COLUMN'] =  $DateCounter;
			}// else
			
			$Content['DATE'] =  strftime("%d", $RowTime['timestamp'] ).". ";
			$Content['DATE'] .= strftime("%m", $RowTime['timestamp'] ).". ";
			$Content['DATE'] .= strftime("%Y", $RowTime['timestamp'] );
 
			if( $Past == true ) {
				if( ($RowTime['timestamp'] < $Now) && ($RowTime['cancelled'] == 0)) {
                    // toggle background color per table row
                    if( !$ColorCounter ) {
                        $Content['ODD'] = 'even';
                    }// if
                    $ColorCounter = 1 - $ColorCounter;
					$LocalOutput .= ParseTemplate( 'locationdateentry.htm', $Content );
				}// if
			}// if
			else {
				if( $RowTime['timestamp'] >= $Now ) {
                    // toggle background color per table row
                    if( !$ColorCounter ) {
                        $Content['ODD'] = 'even';
                   }// if
                    $ColorCounter = 1 - $ColorCounter;
					$LocalOutput .= ParseTemplate( 'locationdateentry.htm', $Content );
				}// if
			}// else
		}// while

 		if( $Past ) {
           $Content = array( 'DATES' => $LocalOutput
                            ,'TITLE' => 'Past Dates: ' );
		}// if
		else {
           $Content = array( 'DATES' => $LocalOutput
                            ,'TITLE' => 'Next Dates: ' );
		}// else
        $Output = ParseTemplate( 'locationdateslist.htm', $Content );


		DBClose( $Link );
	}// if
	else {
		$Output .= 'Could not access DB';
	}// else
    
    return $Output;
}// ListDates 

?>