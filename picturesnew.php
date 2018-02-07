<?php
include 'templates.php';
include 'database.php';
include_once 'config.php';


$thumbnail_neuebreite = 140;
$thumbnail_neuehoehe = 140;
$List = ListPictures();


// Content
$Content = array( 
	 'IMAGELIST' => $List
);

$Output = ParseTemplate( 'picturesnew.htm', $Content );

$Content = array( 'CONTENT' => $Output );
$Output = ParseTemplate( 'site.htm', $Content );

echo $Output;

/***************************************************************************************************
***************************************************************************************************/
function ListPictures() 
{
	global $thumbnail_neuehoehe;


    $i = 0;
    if( DBOpen( $Link ) ) {
        $Result = mysql_query( "SELECT * FROM ".TAB_PICTURES." ORDER BY position ASC" );
        if( $Result ) {
            while( $Row = mysql_fetch_assoc( $Result ) ) {
                $FileList[$i] = $Row[ 'name' ];
                $i++;
            }// while
        }// if
        DBClose( $Link );
        
        $Content3 = '';
        for( $i = 0; $i < count($FileList); $i++ ) {
            $Content = array(  	 'IMAGELINK'	=> htmlspecialchars( PIC_IMAGE_DIR.$FileList[$i] )
                                ,'THUMB'		=> htmlspecialchars( PIC_THUMB_DIR.'TN'.$FileList[$i] )
                                ,'IMAGENAME'	=> $FileList[$i]
                                ,'THUMBHEIGHT'	=> $thumbnail_neuehoehe + 40
                              );
            $Content3 .= ParseTemplate( 'picture.htm', $Content );
        }// for $i
   }// if
					 
	return $Content3;
	
}// ListPictures

/***************************************************************************************************
***************************************************************************************************/
function SortFiles($x, $y) 
{
  if( filemtime( PIC_IMAGE_DIR.$x ) > filemtime( PIC_IMAGE_DIR.$y )) {
    return -1;
  }// if
  else if( filemtime( PIC_IMAGE_DIR.$x ) < filemtime( PIC_IMAGE_DIR.$y ) ) {
    return 1;
  }// else if
  else {
    return 0;
  }// else
}// SortFiles




/***************************************************************************************************
***************************************************************************************************/
function PageNavigation( $totalItems, $perPage, $startWith, $actualPage, $numNavPages, $pageName, $pageParam )
{
	$retVal = '';
	
	if( $totalItems > $perPage ) {
		// Let’s count how many items we need per page
		$totalPages = ceil( $totalItems / $perPage );
		
		// prev-tag
		if( $actualPage > $startWith ) {
			$retVal = '<a href="'.$pageName.'?'.$pageParam.'='.( $actualPage - 1 ).'">&lt;&lt;&lt;</a>&nbsp;';
		}// if
		
		if( $numNavPages >= $totalPages ) {
			$start = $startWith;
			$end = $startWith + ($totalPages - 1 );
		}// if
		else {
			$showAbove = ceil(($numNavPages-1) / 2);
		 	$showBelow = floor(($numNavPages-1) / 2);
		 	
		 	if( $actualPage < ($startWith + $showBelow) ) {
		 		$start = $startWith;
		 		$end = $startWith + ($numNavPages - 1 );
		 	}
		 	else if( $actualPage + $showAbove > ($startWith + $totalPages - 1) ) {
		 		$end = ($startWith + $totalPages - 1);
		 		$start = $end - ($numNavPages - 1);
		 	}// else if
		 	else {
		 		$start = $actualPage - $showBelow;
		 		$end = $actualPage + $showAbove;
		 	}// else
		}// else
		
		for( $i = $start; $i <= $end; $i++ ) {
			if( $i == $actualPage ) {
				$retVal .= '<b>'.$i.'</b>&nbsp;';
			}
			else {
				$retVal .= '<a href="'.$pageName.'?'.$pageParam.'='.$i.'">'.$i.'</a>&nbsp;';
			}
		}// for $i
		 
		 
		// next-tag
		if( $actualPage < $startWith + ($totalPages - 1 )) {
			$retVal .= '<a href="'.$pageName.'?'.$pageParam.'='.( $actualPage + 1 ).'">&gt;&gt;&gt;</a>';
		}// if
	}// if

	return $retVal;	
}

?>
