<?php

include 'functions.php';
include_once '../config.php';
include_once '../templates.php';
include_once '../database.php';





/***************************************************************************************************
***************************************************************************************************/
function ListThumbs( $page )
{	
	global $Output;

	// read all filenames
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_PICTURES." ORDER BY position ASC" );
		if( $Result ) {
			while( $Row = mysql_fetch_assoc( $Result ) ) {
				$verzeichnis_daten[] = $Row[ 'name' ];
			}// while
		}// if
		DBClose( $Link );
	}// if

    if( isset( $verzeichnis_daten ) ) {
        $NumVerzeichnisdaten = count( $verzeichnis_daten );
    }// if
    else {
        $NumVerzeichnisdaten = 0;
    }
	if( $NumVerzeichnisdaten ) {
		$MaxPage = ceil( $NumVerzeichnisdaten / PIC_THUMBS_PER_PAGE ) -1;
		if( $page > $MaxPage ) {
			$page = $MaxPage;
		}// if
	}// if
	else {
		$MaxPage = 0;
		$page = $MaxPage;
	}
	$offset = $page * PIC_THUMBS_PER_PAGE;

	// sort filelist
//	if( $NumVerzeichnisdaten ) {
//		$verzeichnis_daten = array_reverse( $verzeichnis_daten );
//		usort($verzeichnis_daten, 'SortFiles');			
//	}// if
		
	$Content3 = '';
		
	for( $i = $offset; $i < $offset+PIC_THUMBS_PER_PAGE AND $i < $NumVerzeichnisdaten; $i++ ) {
		// create thumbnail if it doesn't exist
		if( !file_exists( PIC_FILE_ROOT.PIC_THUMB_DIR.PIC_THUMB_PREFIX.$verzeichnis_daten[$i] ) ) {
			thumbnail( $verzeichnis_daten[$i], PIC_FILE_ROOT.PIC_IMAGE_DIR, PIC_FILE_ROOT.PIC_THUMB_DIR, PIC_THUMB_PREFIX,PIC_THUMB_WIDTH, PIC_THUMB_HEIGHT, false );
		}// if
		
		$Content = array(  	 'IMAGELINK'	=> htmlspecialchars( PIC_ROOT.PIC_IMAGE_DIR.$verzeichnis_daten[$i] )
							,'THUMB'		=> htmlspecialchars( PIC_ROOT.PIC_THUMB_DIR.PIC_THUMB_PREFIX.$verzeichnis_daten[$i] )
							,'THUMBWIDTH'	=> PIC_THUMB_WIDTH
							,'IMAGENAME'	=> $verzeichnis_daten[$i]
							,'DELETETEXT'	=> 'delete'
							,'PAGE'			=> $page
						  );
		$Content3 .= ParseTemplate( 'pictureentry.htm', $Content );
	}// for $i
	if( $NumVerzeichnisdaten == 0 ) {
		$Content3 = '';
	}// if
	
	
	$Content2 = array( 'TITLE'		=> 'Picture List'
					  ,'NAVI'		=> PageNavigation( $NumVerzeichnisdaten, PIC_THUMBS_PER_PAGE, 0, $page, PIC_NAV_PAGES, htmlspecialchars($_SERVER['PHP_SELF']), 'action=menupictures&page' )
					  ,'NAVFILE'	=> 'File'
					  ,'NAVACTION'	=> 'Action'
					  ,'ENTRIES' 	=> $Content3
					 );

	$TextMaxFilesize ='Maximum allowed filesize: '.( GetMaxUploadSize() / 1024 ).' KB.';
	
	$Content = array( 'TITLE' 		=> 'Upload'
					 ,'FILESIZE' 	=> $TextMaxFilesize
					 ,'SAVE'		=> 'Send'
					 );
	
	$Content = array( 'FORM'		=> ParseTemplate( 'pictureform.htm', $Content )
					 ,'LIST'		=> ParseTemplate( 'picturelist.htm', $Content2 )
					 );
					 
	$Output = ParseTemplate( 'pictures.htm', $Content );
	
}// ListThumbs	


/***************************************************************************************************
***************************************************************************************************/
function PictureDeleteAsk()
{
	global $Output;
	if( !isset($_REQUEST['page']) ) {
		$_REQUEST['page'] = 0;
	}// if
	
	$Content = array( 'LINKBACK' 	=> '<a href="index.php?action=menupictures&page='.$_REQUEST['page'].'"> Back </a>'
					 ,'LINKDEL'		=> '<a href="index.php?action=picturedelete&amp;file='.$_REQUEST['file'].'&amp;tn='.$_REQUEST['tn'].'&amp;page='.$_REQUEST['page'].'" > Delete </a>'
					 ,'IMAGE'		=> htmlspecialchars( PIC_ROOT.PIC_IMAGE_DIR.$_REQUEST['file'] )
					 ,'IMAGENAME'	=> $_REQUEST['file']
					);

	$Output = ParseTemplate( 'picturedelete_ask.htm', $Content );
}// PictureDeleteAsk



/***************************************************************************************************
***************************************************************************************************/
function PictureDelete()
{
	if( !isset($_REQUEST['page']) ) {
		$_REQUEST['page'] = 0;
	}// if
	
	$error = '';

	if( $_REQUEST['file']!='') {
		if( @file_exists( PIC_FILE_ROOT.PIC_IMAGE_DIR.$_REQUEST["file"] ) == true ) {
			$type = (explode(".", PIC_FILE_ROOT.PIC_THUMB_DIR.$_REQUEST["tn"]));

			foreach($type as $key => $value) {
				$type[$key] = strtolower($value);
			}// foreach

			// Delete Thumbnail
			if( ($type[count($type)-1] == 'gif') OR ($type[count($type)-1] == 'jpeg') OR ($type[count($type)-1] == 'png') OR ($type[count($type)-1] == 'jpg')) {
				if( @unlink(PIC_FILE_ROOT.PIC_THUMB_DIR.$_REQUEST["tn"]) == false ) {
					$error = 'fehler_file_delete';
				}// if
			}// if

			// Delete picture
			if(@unlink( PIC_FILE_ROOT.PIC_IMAGE_DIR.$_REQUEST["file"]) == false ) {
				$error = 'no_file_delete';
			}// if
			
			//Delete DB entry
			if( DBOpen( $Link ) ) {
				mysql_query( "DELETE FROM ".TAB_PICTURES." WHERE name='".$_REQUEST["file"]."'" );
				DBClose( $Link );
			}// if
			
		}// if
		else {
			$error = 'file_delete_no_directory';
		}// else
	}// if
	
	return $error;
}// PictureDelete




/***************************************************************************************************
***************************************************************************************************/
function PictureError() 
{
	global $Output;
	
	$Output = $_REQUEST['no'];

}// PictureError

/***************************************************************************************************
***************************************************************************************************/
function SortFiles($x, $y) 
{
	if( filemtime( PIC_FILE_ROOT.PIC_IMAGE_DIR.$x ) > filemtime( PIC_FILE_ROOT.PIC_IMAGE_DIR.$y )) {
		return -1;
	}// if
	else if( filemtime( PIC_FILE_ROOT.PIC_IMAGE_DIR.$x ) < filemtime( PIC_FILE_ROOT.PIC_IMAGE_DIR.$y ) ) {
		return 1;
	}// else if
	else {
		return 0;
	}// else
}// SortFiles




/***************************************************************************************************
***************************************************************************************************/
function PictureUpload()
{
	$reg_exp = "/^[a-z0-9_]([a-z0-9\(\)_-]*\.?[a-z0-9\(\)_-])*\.[a-z]{3,4}$/i";
	$filename = '';
	$error = '';
	
	if( $_FILES['userfile']['tmp_name'] == '' ) {
		if( $_POST['action'] == '') {
			// workaround: $_POST  and $_FILES are empty when file-size > post_max_size in PHP.ini
			$error='fehler_upload_groesse';
		}// if
		else {
			$error='file_auswaehlen';
		}// if
	}// if
	else {
		$file = $_FILES['userfile']['name'];
		$temp = $_FILES['userfile']['tmp_name'];
		$path_parts = @pathinfo($file);
		
		if( !isset($path_parts["extension"]) ) {
			$path_parts["extension"]='';
		}// if
		
		if( $_FILES['userfile']['type'] != 'image/x-png' 
			&& $_FILES['userfile']['type'] != 'image/gif' 
			&& $_FILES['userfile']['type'] != 'image/jpeg' 
			&& $_FILES['userfile']['type'] != 'image/png' 
			&& $_FILES['userfile']['type'] != 'image/jpeg' 
			&& $_FILES['userfile']['type'] != 'image/pjpeg') {
			$ist_bild=0; 
		}// if
		else { 
			$ist_bild=1;
		}// else
		
		if( function_exists( "exif_imagetype" ) == true ) {
			if(exif_imagetype($temp) == (IMAGETYPE_GIF OR IMAGETYPE_JPEG OR IMAGETYPE_PNG) ) {
				$ist_bild=1;
			}// if
			else {
				$ist_bild=0;
			}// else
		}// if
		
		if($upload_erlaubnis == 0) {
			$ist_bild=1;
		}// if
		
		if( $ist_bild==0 ) {
			// none-image-file selcted
			$error = 'file_img';
		}// if 
		else {
			if($path_parts["extension"]!=""){
				if($dateiname_dynamisch==1){
					$filename = basename( $file, '.'.$path_parts['extension'] ) . time() . "." . $path_parts["extension"];
				}// if 
				else {
					$filename = $file;
					if(preg_match($reg_exp, $filename)==false){
						$error = 'file_name';
					}// if
				}// else
				
				$filename = str_replace( ' ', '_', $filename );
				
				$MaxUploadSize = GetMaxUploadSize();
				if($_FILES['userfile']['size'] <= $MaxUploadSize*1024) {
					if(decoct(fileperms(PIC_FILE_ROOT.PIC_IMAGE_DIR))==40777) {
						if( file_exists(PIC_FILE_ROOT.PIC_IMAGE_DIR.$filename)) {
							$filename = basename( $file, '.'.$path_parts['extension'] ).'_' . time() . "." . $path_parts["extension"];
						}// if
						if( @copy($temp, PIC_FILE_ROOT.PIC_IMAGE_DIR.$filename) ) {

							chmod (PIC_FILE_ROOT.PIC_IMAGE_DIR.$filename, 0777);
							// resize picture
							thumbnail( $filename, PIC_FILE_ROOT.PIC_IMAGE_DIR , PIC_FILE_ROOT.PIC_IMAGE_DIR, '', PIC_MAX_WIDHT, PIC_MAX_HEIGHT, true );
							//thumbnail( $filename, PIC_FILE_ROOT.PIC_IMAGE_DIR , PIC_FILE_ROOT.PIC_IMAGE_DIR, 'TEMP', PIC_MAX_WIDHT, PIC_MAX_HEIGHT, true );
							//if(@unlink( PIC_FILE_ROOT.PIC_IMAGE_DIR.$filename) == false ) {
							//	$error = 'no_file_delete';
							//}// if
							//else {
							//	rename( PIC_FILE_ROOT.PIC_IMAGE_DIR.'TEMP'.$filename, PIC_FILE_ROOT.PIC_IMAGE_DIR.$filename );
							//}// else
							
							// create thumbnail
							thumbnail( $filename, PIC_FILE_ROOT.PIC_IMAGE_DIR , PIC_FILE_ROOT.PIC_THUMB_DIR, PIC_THUMB_PREFIX, PIC_THUMB_WIDTH, PIC_THUMB_HEIGHT, false );
							
							chmod (PIC_FILE_ROOT.PIC_IMAGE_DIR.$filename, 0755);
							
							$FreePosition = PicturesGetFirstFreePosition();
							if( DBOpen($Link) ) {
								$Query = "INSERT INTO ".TAB_PICTURES." (name, position, gallery) VALUES ('".$filename."', ".$FreePosition.", 0)";
								$Result = mysql_query( $Query );
								DBClose($Link);
							}// if
						}// if 
						else {
							$error = 'fehler_upload';
						}// else
					}// if 
					else {
						$error = 'fehler_upload_rechte';
					}// else
				}// if 
				else {
					$error = 'fehler_upload_groesse';
				}// else
			}// if
		}// else
	}// else

	return $error;
}//PictureUpload


/***************************************************************************************************
***************************************************************************************************/
function PicturesUpdate()
{
	// check if all files are in DB
	// read directory
	$FreePosition = PicturesGetFirstFreePosition();
	$fp = opendir( PIC_FILE_ROOT.PIC_IMAGE_DIR );
	if( DBOpen( $Link ) && $fp ) {

		while (false !== ($file =readdir($fp) ) ) {
			if( filetype( PIC_FILE_ROOT.PIC_IMAGE_DIR.$file ) != "dir" ) {
				$Result = mysql_query( "SELECT * FROM ".TAB_PICTURES." WHERE name='".$file."'" );
				if( $Result ) {
					if( !mysql_num_rows($Result) ){
						$Query = "INSERT INTO ".TAB_PICTURES." (name, position, gallery) VALUES ('".$file."', ".$FreePosition.", 0)";
						$Result = mysql_query( $Query );
						if( $Result ) {
							$FreePosition++;
						}// if
					}// if
				}// if
			}// if
		}// while
		DBClose( $Link );
        closedir($fp);
	}// if

	// check if all DB entries are present in filsystem
	$FreePosition = PicturesGetFirstFreePosition();
	if( DBOpen( $Link ) ) {
		if( $FreePosition != false ) {
			$Result = mysql_query( "SELECT * FROM ".TAB_PICTURES );
			if( $Result ) {
				while( $Row = mysql_fetch_array( $Result ) ) {
					if( !file_exists( PIC_FILE_ROOT.PIC_IMAGE_DIR.$Row['name'] ) ) {
						mysql_query( "DELETE FROM ".TAB_PICTURES." WHERE name='".$Row['name']."'" );
						if( file_exists( PIC_FILE_ROOT.PIC_THUMB_DIR.PIC_THUMB_PREFIX.$Row['name'] ) ) {
							unlink( PIC_FILE_ROOT.PIC_THUMB_DIR.PIC_THUMB_PREFIX.$Row['name'] );
						}// if
					}// if
				}// while
			}// if
		}// if	
		DBClose( $Link );
	}// if
	
	// renumber DB
	PicturesRenumber();
}// PicturesUpdate



/***************************************************************************************************
***************************************************************************************************/
function PicturesRenumber() 
{
	// renumber DB
	$FreePosition = 1;
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_PICTURES." ORDER BY position ASC" );
		if( $Result ) {
			while( $Row = mysql_fetch_assoc( $Result ) ) {
				mysql_query( "UPDATE ".TAB_PICTURES." SET position=".$FreePosition." WHERE id=".$Row['id'] );
				$FreePosition++;
			}// while
		}// if
		DBClose( $Link );
	}// if
}// PicturesRenumber


/***************************************************************************************************
***************************************************************************************************/
function PicturesGetFirstFreePosition() 
{
	$RetVal = false;
	
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT MAX(position) AS position FROM ".TAB_PICTURES );
		if( $Result ) {
			$Row = mysql_fetch_array( $Result );
			if( ($Row[ 'position' ] == NULL) || ($Row == false)  ) {
				$Row[ 'position' ] = 0;
			}// if
			$RetVal = $Row[ 'position' ] + 1;
		}// if
		DBClose( $Link );
	}// if
	return $RetVal;
}// GetFirstFreePosition


/***************************************************************************************************
***************************************************************************************************/
function PictureMoveTop( $name ) 
{
	$FreePosition = PicturesGetFirstFreePosition();
	if( DBOpen( $Link ) ) {
		mysql_query( "UPDATE ".TAB_PICTURES." SET position=0 WHERE name='".$name."'" );
		DBClose( $Link );
	}// if
	PicturesRenumber();

}// PictureMoveTop


/***************************************************************************************************
***************************************************************************************************/
function PictureMoveUp( $name ) 
{
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_PICTURES." WHERE name='".$name."'" );
		if( $Result ) {
			if( $Row = mysql_fetch_assoc( $Result ) ) {
				if( $Row['position'] > 1 ) {
					mysql_query( "UPDATE ".TAB_PICTURES." SET position=".$Row['position']." WHERE position=".($Row['position']-1) );
					mysql_query( "UPDATE ".TAB_PICTURES." SET position=".($Row['position']-1)." WHERE name='".$name."'" );
				}// if
			}// if
		}// if
		DBClose( $Link );
	}// if
	
}// PictureMoveTop


/***************************************************************************************************
***************************************************************************************************/
function PictureMoveDown( $name ) 
{
	$FreePosition = PicturesGetFirstFreePosition();
	
	if( DBOpen( $Link ) ) {
		$Result = mysql_query( "SELECT * FROM ".TAB_PICTURES." WHERE name='".$name."'" );
		if( $Result ) {
			if( $Row = mysql_fetch_assoc( $Result ) ) {
				if( $Row['position'] < $FreePosition - 1 ) {
					mysql_query( "UPDATE ".TAB_PICTURES." SET position=".$Row['position']." WHERE position=".($Row['position']+1) );
					mysql_query( "UPDATE ".TAB_PICTURES." SET position=".($Row['position']+1)." WHERE name='".$name."'" );
				}// if
			}// if
		}// if
		DBClose( $Link );
	}// if
}// PictureMoveTop


/***************************************************************************************************
***************************************************************************************************/
function PictureMoveBottom( $name ) 
{
	$FreePosition = PicturesGetFirstFreePosition();
	if( DBOpen( $Link ) ) {
		mysql_query( "UPDATE ".TAB_PICTURES." SET position=".$FreePosition." WHERE name='".$name."'" );
		DBClose( $Link );
	}// if

}// PictureMoveTop



	
?>