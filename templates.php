<?php
/**
 * templates.php
 * Created on 17.10.2007
 *
 * @author  Joe Koperski 
 * 
 */

include_once( 'config.php' );

$Error['template_not_found'] = 'Fehler: Template nicht gefunden: ';


/*******************************************************************************
	Parses a HTML-Template and sets the given content.
	A Variable inside a template is encapsuled in <%% %%>, e.g.: <%%CONTENT%%>
******************************************************************************/
function ParseTemplate( $FilePath, $Values ) { 

    $tpl = GetTemplate( $FilePath );

	if(is_array($Values)) {
		foreach($Values as $key => $value) { 
		$suchmuster = "/<%%(".strtoupper($key).")%%>/si";
			// replace found variables
			$tpl = preg_replace($suchmuster, $value, $tpl); 
		}// foreach
		
		// remove left variables
		$tpl = preg_replace("/((<%%)(.+?)(%%>))/si", '', $tpl);
	}
	return $tpl; 
}// ParseTemplate

 
/*******************************************************************************
 * Reads a HTML-Template.
 ******************************************************************************/
function GetTemplate( $tpl ) 
{
	global $Error;

    
	if( CheckMobile() ) {
        $tpl = TEMPLATE_PATH_MOBILE . $tpl;
    }// if
    else {
        $tpl = TEMPLATE_PATH . $tpl;
    }// else

	if( file_exists( $tpl )) {
		$template = implode('',file($tpl));
	}// if
	else {
		$template = $Error['template_not_found'].$tpl;
	}// else
	
	return $template;
}// GetTemplate




/*******************************************************************************
 Select Template for mobile devices
*******************************************************************************/
function CheckMobile() {
    $agents = array(
        'Windows CE', 'Pocket', 'Mobile',
        'Portable', 'Smartphone', 'SDA',
        'PDA', 'Handheld', 'Symbian',
        'WAP', 'Palm', 'Avantgo',
        'cHTML', 'BlackBerry', 'Opera Mini',
        'Nokia'
    );

    for ( $i=0; $i < count($agents); $i++ ) {
        if( isset( $_SERVER["HTTP_USER_AGENT"] ) && strpos( $_SERVER["HTTP_USER_AGENT"], $agents[$i] ) !== false ) {
            return true;
        }// if
    }// for $i

    return false;
    
//    return true;
    
}// CheckMobile


?>
