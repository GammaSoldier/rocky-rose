<?php

include_once '../config.php';
include_once '../database.php';
include_once '../templates.php';
include_once 'dates.php';
include_once 'pictures.php';
include_once 'setlist.php';
include_once 'guestbook.php';



$Output = '';

session_start();
if( !isset( $_SESSION['LoggedIn'] ) ) {
	$_SESSION['LoggedIn'] = false;
}// if

execute();
$Content = array( 'CONTENT' => $Output );
$Output = ParseTemplate( 'site.htm', $Content );

echo $Output;


/***************************************************************************************************
***************************************************************************************************/
function execute() 
{
	global $Output;
    
	if( !isset($_REQUEST['action']) ) {
		$Action = '';
	}// if
	else {
		$Action = $_REQUEST['action'];
	}// else

	if( $_SESSION['LoggedIn'] == false ) {
		if( $Action == 'login' ) {
			if( LoginConfirm() == true ) {
				PicturesUpdate();
				header( 'Location: '.$_SERVER['PHP_SELF'] );
			}// if
		}// if
		else {
			Login();
		}// else
	}// if
	else {
		switch( $Action ) {
		case 'logout':
			$_SESSION['LoggedIn'] = false;
			header( 'Location: index.php' );
			break;
			
		// l o c a t i o n s ///////////////////////////////////////////////////////////////////////
		case 'settings':
            $Result = StoreSettings();
            if( $Result === true ) {
				header( 'Location: '.$_SERVER['PHP_SELF'] );
			}// if
            else {
                $Output .= $Result;
            }// else
			break;
			
		case 'enternewlocation':
            $Result = CheckNewLocation();
            if( $Result === true ) {
				header( 'Location: '.$_SERVER['PHP_SELF'] );
			}// if
            else {
                $Output .= $Result;
            }// else
			break;
			
		case 'confirmnewlocation':
			if( $_REQUEST['btnsend'] == 'Yes' ) {
                $Result = NewLocation();
                if( $Result === true ) {
                    header( 'Location: '.$_SERVER['PHP_SELF'] );
                }// if
                else {
                    $Output .= $Result;
                }// else
			}// if
			break;
			
		case 'editlocation':
			$Output.= EditLocation();
			break;
			
		case 'modifylocation':
			if( $_REQUEST['btnsend'] == 'Submit' ) {
                $Result = ModifyLocation();
                if( $Result === true ) {
                    header( 'Location: '.$_SERVER['PHP_SELF'] );
                }// if
                else {
                    $Output .= $Result;
                }// else
			}// if
			else {
				$Output .= DeleteLocationConfirm();
			}// else
			break;
			
		case 'confirmlocationdeletion':
			if( $_REQUEST['btnsend'] == 'Delete' ) {
                $Result = DeleteLocation();
                if( $Result === true ) {
                    header( 'Location: '.$_SERVER['PHP_SELF'] );
                }// if
                else {
                    $Output .= $Result;
                }// else
			}// if
			break;

		case 'enternewdate':
			$Output .= EnterNewDate();
			break;
			
		case 'newdate':
            $Result = NewDate();
            if( $Result === true ) {
                header( 'Location: '.$_SERVER['PHP_SELF'] );
            }// if
            else {
                $Output .= $Result;
            }// else
			break;
			
		case 'deldate':
			if( $_REQUEST['btnsend'] == 'Delete' ) {
                $Result = DeleteDate();
                if( $Result === true ) {
                    header( 'Location: '.$_SERVER['PHP_SELF'] );
                }// if
                else {
                    $Output .= $Result;
                }// else
			}// if
			else if( $_REQUEST['btnsend'] == 'Cancel' ) {
                $Result = CancelDate();
                if( $Result === true ) {
                    header( 'Location: '.$_SERVER['PHP_SELF'] );
                }// if
                else {
                    $Output .= $Result;
                }// else
			}// if
			else{
                header( 'Location: '.$_SERVER['PHP_SELF'] );
			}// else
			break;
			
		case 'confirmdatedeletion':
			$Output .= ConfirmDateDeletion();
			break;
		
		// p i c t u r e s /////////////////////////////////////////////////////////////////////////
		case 'menupictures':
			$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
			ListThumbs( $page );
			break;
		
		case 'picturedelete_ask':
			PictureDeleteAsk();
			break;
			
		case 'picturedelete':
			$error = PictureDelete();
			if( $error != '' ) {
				header( 'Location: index.php?action=pictureerror&no='.$error.'&page='.$_REQUEST['page'] );
			}// if
			else {
				header( 'Location: index.php?action=menupictures&page='.$_REQUEST['page'] );
			}// else
			break;

		case 'pictureerror':
			PictureError();
			break;
			
		case 'Send':
			$error = PictureUpload();
			if( $error != '' ) {
				header( 'Location: index.php?action=pictureerror&no='.$error );
			}// if
			else {
				header( 'Location: index.php?action=menupictures&page=9999' );
			}// else
			break;
		
		case 'picturetop':
			PictureMoveTop( $_REQUEST['file'] );
			header( 'Location: '.$_SERVER['PHP_SELF'].'?action=menupictures&page=0' );
			
			break;
		case 'pictureup':
			$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
			PictureMoveUp( $_REQUEST['file'] );
			header( 'Location: '.$_SERVER['PHP_SELF'].'?action=menupictures&page='.$_REQUEST['page'] );
			break;
			
		case 'picturedown':
			$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
			PictureMoveDown( $_REQUEST['file'] );
			header( 'Location: '.$_SERVER['PHP_SELF'].'?action=menupictures&page='.$_REQUEST['page'] );
			break;
			
		case 'picturebottom':
			PictureMoveBottom( $_REQUEST['file'] );
			header( 'Location: '.$_SERVER['PHP_SELF'].'?action=menupictures&page=9999' );
			break;
		
		// s e t l i s t ///////////////////////////////////////////////////////////////////////////
		case 'setlist':
			SetListEdit();
			break;

		case 'sendsetlist':
			header( 'Location: '.$_SERVER['PHP_SELF'].'?action=setlist&result='.SetListSave( $_REQUEST['setlist']) );
			
			break;

		// g u e s t b o o k ///////////////////////////////////////////////////////////////////////
		case 'guestbook':
			$Output .= GuestbookShowSite();
			break;
		
		case 'editguestbook':
			if( isset( $_REQUEST['selection'] )) {
                if( $_REQUEST['btnsend'] == 'Delete' ) {
                    $Result = GuestbookDelete( $_REQUEST['selection'] );
                    if( $Result === true ) {
                        header( 'Location: '.$_SERVER['PHP_SELF'].'?action=guestbook' );
                    }// if
                    else {
                        $Output .= $Result;
                    }// else
                }// if
            }// if
            else {
                header( 'Location: '.$_SERVER['PHP_SELF'].'?action=guestbook' );

            }// else
			break;
		
		default:
			$Output .= ShowLists();
		}// switch
	}// else
}// execute


/***************************************************************************************************
***************************************************************************************************/
function LoginConfirm() 
{
	global $Output;
	$RetVal = false;
	
	if( ($_REQUEST[ 'username' ] == ADMIN_USER ) && ($_REQUEST[ 'userpass' ] == ADMIN_PASSWORD )) {
		$_SESSION['LoggedIn'] = true;
		$RetVal = true;
	}// if
	else {
		$Output .= 'Username or password not correct. Please retry.';
	}// else
	return $RetVal;
}// Login



/***************************************************************************************************
***************************************************************************************************/
function Login() 
{
	global $Output;
	$Output .= '<table class="listing" align="center"><tr><th colspan="2">';
	$Output .= 'Admin-Login: </th></tr>';
	$Output .= '<tr><td ><br>';
	
	$Output .= '<form method="post" action="index.php?action=login">
				<b>Name:</b>		<input type="text" name="username" maxlength="50" />
				<b>Password:</b>	<input type="password" name="userpass" />
				<input type="submit" name="btnsend" value="Submit" />
				</form>';
	$Output .= '<br></td></tr>';
}// Login


?>
