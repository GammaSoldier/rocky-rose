<?php

//session_start();
##################################################################
#                                                                #
#                                                                #
#                            PHPKIT                              #
#          --------------------------------------------          #
#          Copyright (c) 2002-2003 Gersöne & Schott GbR          #
#                                                                #
#                                                                #
# ############################################################## #
#                                                                #
#     Diese Datei / die PHPKIT-Software ist keine Freeware!      #
#   Für weitere Information besuchen Sie bitte unsere Webseite   #
#             oder kontaktieren uns per E-Mail:                  #
#                                                                #
#       This file / the PHPKIT-software is not freeware!         #
#       For further informations please vistit our website       #
#                 or contact us via email:                       #
#                                                                #
#              Website: http://www.phpkit.de                     #
#                   Email: info@phpkit.de                        #
#                                                                #
# ############################################################## #
#                                                                #
#  File:          /include.php                                   #
#  Author:        Pierre Gersöne                                 #
#  Created:       Version 1.6.1 - 2004-05-09                     #
#  Last Modified: Version 1.6.1 - 2004-13-09                     #
#  Description:   not available for this file                    #
#                                                                #
# ############################################################## #
#                                                                #
#     SIE SIND NICHT BERECHTIGT, UNRECHTMÄSSIGE KOPIEN DIESER    #
#  DATEI ZU ERSTELLEN UND/ODER DIESE INFORMATIONEN ZU ENTFERNEN  #
#                                                                #
#    YOU ARE NOT AUTHORISED TO CREATE ILLEGAL COPIES OF THIS     #
#          FILE AND/OR TO REMOVE THIS INFORMATIONS               #
#                                                                #
##################################################################
// $Temp = session_id();
// var_dump( $Temp );

//---> Angriffe blocken
include("hack_block/hack_block.php");
//---> Angriffe blocken

//chdir($_SERVER[DOCUMENT_ROOT]);
//var_dump( $_SERVER[DOCUMENT_ROOT] ); echo getcwd().'<br>';

//session_name($_REQUEST['PHPKITSID']);
//session_start();

ini_set("session.use_trans_sid", true);

if(defined('pkDIRROOT')) {
	return false;
}
if(!defined('pkFRONTEND'))
	{
	define('pkFRONTEND','public');
	define('pkREQUESTEDFILE',basename(__FILE__));
	}

# start config

@error_reporting( E_ERROR  ); 
//@error_reporting( E_ERROR | E_NOTICE  ); 
//@error_reporting(E_ALL);
//ini_set("display_errors", true);

// set some constants
define('pkMICROTIME',microtime());
//define('pkTIME',time());
define('pkTIME',$_SERVER['REQUEST_TIME']);
define('pkTIMETODAY',mktime(0,0,0,date('m',pkTIME),date('d',pkTIME),date('Y',pkTIME)));

define('pkEXT','.php');										// standard file extension

// define serval needed directories-paths
define('pkDIRROOT',dirname(__FILE__).'/');					// root-directory for internal use (f.e. include)
define('pkDIRINC',pkDIRROOT.'inc/');						// base source directory
define('pkDIRADMIN',pkDIRINC.'admin/');						// source directory - admin scripts
define('pkDIRCLASS',pkDIRINC.'class/');						// source directory - classes
define('pkDIRFUNC',pkDIRINC.'func/');						// source directory - functions
define('pkDIRLANG',pkDIRINC.'lang/');						// source directory - language packs
define('pkDIRPUBLIC',pkDIRINC.'public/');					// source directory - public scripts

define('pkWWWROOT',pkFRONTEND=='public' ? './' : './../');	// web-root for use in links
define('pkWWWSELF',basename(__FILE__));


if(!@include_once(pkDIRROOT.'admin/config/inc.sql.php'))
	{
	header('Location: '.pkWWWROOT.'install'.pkEXT);
	exit;
	}

require_once(pkDIRROOT.'admin/config/inc.dbtabs.php');
require_once(pkDIRFUNC.'default'.pkEXT);
require_once(pkDIRROOT.'admin/lib/lib_access'.pkEXT);
require_once(pkDIRROOT.'admin/lib/lib_parse'.pkEXT);
require_once(pkDIRROOT.'admin/lib/lib_forum'.pkEXT);


if(str_replace(".","",phpversion())<410) {
	getpost410vars();
}

if(get_magic_quotes_gpc()) {

	if(is_array($_REQUEST))
		$_REQUEST=stripslashes_array($_REQUEST);
	
	if(is_array($_POST)) 
		$_POST=stripslashes_array($_POST);
	
	if(is_array($_GET))
		$_GET=stripslashes_array($_GET);
	
	if(is_array($_COOKIE)) 
		$_COOKIE=stripslashes_array($_COOKIE);
	
	@set_magic_quotes_runtime(0);
	}
@ini_set("session.use_cookies","1");

pkLoadClass($ENV,'env');
pkLoadClass($SQL,'sql');
pkLoadClass($SESSION,'session');

if(!$SQL->connect()) {
	header('Location: '.pkWWWROOT.'info.php?error=1');
 	exit();
	}

$DB=&$SQL;

if(!$config=$DB->fetch_assoc($DB->query("SELECT * FROM ".$db_tab['config']." WHERE profil_active=1 LIMIT 1"))) 
	{
	header('Location: '.pkWWWROOT.'info.php');
	exit();
	}


$lang=array();
$LANG=&$lang;
pkLoadLang();

$PARSE=new PARSE();
$FORUM=new FORUM();

//Konfigurationswerte zur Erprobung noch nicht über Adminbereich einstellbar 
$config['cookie_path']='/';
$config['cookie_domain']='';
$config['cookie_secure']=0;
$config['smilie_dir']='images/smilies';
$config['image_archive']='content/images';
$config['time_offset']=0;		 				// ausgleich kleinerer Serverzeitabweichung in Sekunden
$config['move_logout']="path=start.php";	// Weiterleitung nach dem Logout
$config['move_login']="path=start.php";	// Weiterleitung nach dem Login falls keine Rückleitung vorhanden
$config['im_max']='50';
$config['forum_threadtitle_cut']=25;
$config['forum_threadautor_cut']=10;
$config['username_cut']=18;	
$config['sidelinkfull_pages']=3;
//---Community
$config['nb_community_box']=2;		//1=classic, 2=login-form
//---Forenticker
$config['nb_newthreads_scut']=0;   	//stringcut
$config['nb_newthreads_break']=5;	//anzahl
//---Neue Forenthemen
$config['nb_curthreads_scut']=0;   	//stringcut
$config['nb_curthreads_break']=5;	//anzahl
//---Zufallsartikel
$config['nb_randarticle_cur']=150; 	//Text kürzen
//---Zufallsartikel
$config['nb_newarticle_cur']=150; 	//Text kürzen
$config['template_dir']='templates'; 
$config['imagedir']='images';
//####################################################

unset($ADMINACCESS);
$event=NULL;
$USER=array();
$thisUSER=array();

//if( strstr( $HTTP_SERVER_VARS["SERVER_SOFTWARE"], 'IIS') ) {
//	// Microsoft-IIS/6
//	$thisUSER['ipaddr'] = $HTTP_SERVER_VARS["REMOTE_ADDR"];
//	$thisUSER['browser']=$HTTP_SERVER_VARS['HTTP_USER_AGENT'];
//	$thisUSER['referer']=$HTTP_SERVER_VARS['HTTP_REFERER'];
//	$current_url=preg_replace('/[&|?]'.session_name().'=[^&]*/',"",preg_replace('/[&|?]nid=[^&]*/',"",$HTTP_SERVER_VARS['REQUEST_URI']));
//	$current_path=preg_replace('/[&|?]'.session_name().'=[^&]*/',"",preg_replace('/[&|?]nid=[^&]*/',"",$HTTP_SERVER_VARS['QUERY_STRING']));
//}
if( strstr( $_SERVER["SERVER_SOFTWARE"], 'IIS') ) {
	// Microsoft-IIS/6
	$thisUSER['ipaddr'] = $_SERVER["REMOTE_ADDR"];
	$thisUSER['browser']=$_SERVER['HTTP_USER_AGENT'];
	$thisUSER['referer']=$_SERVER['HTTP_REFERER'];
	$current_url=preg_replace('/[&|?]'.session_name().'=[^&]*/',"",preg_replace('/[&|?]nid=[^&]*/',"",$_SERVER['REQUEST_URI']));
	$current_path=preg_replace('/[&|?]'.session_name().'=[^&]*/',"",preg_replace('/[&|?]nid=[^&]*/',"",$_SERVER['QUERY_STRING']));
}
else {
	// hopefully Unix
	$thisUSER['ipaddr']=getenv('REMOTE_ADDR');
	$thisUSER['browser']=getenv('HTTP_USER_AGENT');
	$thisUSER['referer']=getenv('HTTP_REFERER');
	$current_url=preg_replace('/[&|?]'.session_name().'=[^&]*/',"",preg_replace('/[&|?]nid=[^&]*/',"",getenv('REQUEST_URI')));
	$current_path=preg_replace('/[&|?]'.session_name().'=[^&]*/',"",preg_replace('/[&|?]nid=[^&]*/',"",getenv('QUERY_STRING')));
}


if( $thisUSER['ipaddr'] == NULL ) {
	$thisUSER['ipaddr'] = 'no IP';
}


$session_expire=1800;
$time_guest=3600*24*30;
$time_now=pkTIME;
$guest_expire=$cookie_expire=pkTIME+$time_guest;
$expire=pkTIME+$session_expire;
$record_expire=pkTIME-(3600*$config['referer_delete']*7);
$present_time=formattime('','','extend');

if(!ipcheck($thisUSER['ipaddr'])) 
	{
	header('Location: '.pkWWWROOT.'info.php?error=3'); 
	exit;
	}

session_name("PHPKITSID");
$dounset=false;

// Bugfix Joe
$DB->query("SET @@session.sql_mode=''");


$DB->query("DELETE FROM ".$db_tab['session']." WHERE session_expire<'".pkTIME."'");

if(isset($_REQUEST['PHPKITSID'])) {
	$session=$DB->fetch_array($DB->query("SELECT session_id, session_userid FROM ".$db_tab['session']." WHERE session_id='".addslashes($_REQUEST['PHPKITSID'])."' LIMIT 1"));
}
else
	$session=array('session_id'=>0);

if(strlen($session['session_id'])=='32') {
	session_id($session['session_id']);
	session_start();
	session_getvars();

	if($USER['status']=='ban') {
		header('Location: '.pkWWWROOT.'info.php?error=3'); 
		exit();
	}

	if($_REQUEST['firstlog']==1 || $_REQUEST['relog']==1)
		$dounset=true;
	elseif($USER['sip']==$thisUSER['ipaddr']) {
		if(($USER['status']=='admin' || 
			$USER['status']=='mod' || 
			$USER['status']=='member' || 
			$USER['status']=='user') && 
			$session['session_userid']>0) {
			$userinfo=$DB->fetch_array($DB->query("SELECT user_status FROM ".$db_tab['user']." WHERE user_name='".$USER['name']."' AND user_pw='".$USER['pass']."' AND user_id='".$session['session_userid']."' LIMIT 1"));
 
 			if($userinfo['user_status']==$USER['status']) {
				$DB->query("UPDATE ".$db_tab['user']." SET logtime='".pkTIME."' WHERE user_name='".$USER['name']."' AND user_pw='".$USER['pass']."' AND user_id=".$session['session_userid']);
			}// if
 			else {
				$dounset=true;
			}
			
		}// if
		elseif($USER['status']=='guest') {
			if($USER['sip']!=$thisUSER['ipaddr']) 
				$dounset=true;
		}// if
		else
			$dounset=true;
	}// else
	else 
		$dounset=true;
 
	if($dounset || $_REQUEST['logout']==1 || $_REQUEST['login']==1) {
		if(session_is_registered("USER")) {
			session_unregister("USER");
			session_unset();
			@session_destroy();
		}// if
		
		$USER=array();
		phpkitcookie("PHPKITSID");
		phpkitcookie("user_id");
		phpkitcookie("user_name");
		phpkitcookie("user_pw");
		
		$DB->query("DELETE FROM ".$db_tab['session']." WHERE session_id='".$session['session_id']."' LIMIT 1");
	}// if
	else {
		$DB->query("UPDATE ".$db_tab['session']." SET session_expire='".$expire."', session_url='".$current_url."' WHERE session_id='".$session['session_id']."'");
		$PHPKITSID=$_REQUEST['PHPKITSID']=session_id();
	}// else
}// if

if(!session_is_registered("USER") || isset($_REQUEST['login']) || isset($_REQUEST['logout']) || isset($_REQUEST['firstlog']) || isset($_REQUEST['relog']) || $dounset)
//if(!isset($_SESSION['USER']) || isset($_REQUEST['login']) || isset($_REQUEST['logout']) || isset($_REQUEST['firstlog']) || isset($_REQUEST['relog']) || $dounset)
{
	$error=0;
	
	if($_REQUEST['relog']==1) {
		if($userinfo=$DB->fetch_array($DB->query("SELECT user_pw FROM ".$db_tab['user']." WHERE user_name='".urldecode($_REQUEST['user'])."' AND uid='".$_REQUEST['uid']."' LIMIT 1")))
			$_REQUEST['login']=1;
		else 
			$error=3;
	}// if
	
	if(isset($_REQUEST['login']) || isset($_REQUEST['firstlog'])) {
		if(trim($_REQUEST['user'])!='') 
			{
			if(isset($_GET['firstlog']) || isset($_GET['relog']))
				$username=urldecode($_GET['user']);
			else
				$username=$_REQUEST['user'];
			
			if(isset($_GET['relog'])) 
				$userpass=$userinfo['user_pw'];
			elseif(trim($_REQUEST['userpw'])!='')
				$userpass=md5($_REQUEST['userpw']);
			else
				$error=2;
			}
		else 
			$error=1;
		
		if($error) 
			{
			header('Location: include.php?path=login/login.php&error='.$error.'&PHPKITSID='.session_id());
			exit();
			}
	}
	else {
		if(isset($_COOKIE['user_id']))
			$userid=intval($_COOKIE['user_id']);
		else 
			$userid=0;
		
		if(isset($_COOKIE['user_name']))
			$username=$_COOKIE['user_name'];
		else 
			$username=NULL;
		
		if(isset($_COOKIE['user_pw'])) 
			$userpass=$_COOKIE['user_pw'];
		else
			$userpass=NULL;
		
		if($userid && $username && $userpass)
			$_REQUEST['login_setcookie']=1;
	}
	
	$userinfo=array();  
	if($userid>0 || isset($_REQUEST['login']) || isset($_REQUEST['firstlog']))  {
		$userinfo=$DB->fetch_array($DB->query("SELECT * FROM ".$db_tab['user']." WHERE user_name='".$username."' AND user_pw='".$userpass."' LIMIT 1"));
		if($userinfo['user_name']!=$username || $userinfo['user_pw']!=$userpass) 
			{
			if(isset($_REQUEST['login']) || isset($_REQUEST['firstlog'])) 
				{
				header('Location: '.pkWWWROOT.'include.php?path=login/login.php&error=3&PHPKITSID='.session_id()); 
				exit;
				}
			unset($userinfo);
			}  
		elseif($userinfo['user_activate']!=1 && $userinfo['user_status']!='admin' && $_REQUEST['event']!=30) 
			{
			header ('Location: '.pkWWWROOT.'include.php?event=27');
			exit;
			}
	}// if
	
	if(empty($userinfo) || isset($_REQUEST['logout'])) {
		srand((double)microtime()*1000000);
		$guest_uid=md5(uniqid(rand()));

		$userinfo=array();		
		$userinfo['user_status']='guest';
		$userinfo['user_id']='0';
		$userinfo['user_name']=$lang['guest_status'];
		$userinfo['user_nick']=$lang['guest_status'];
		$userinfo['user_pw']=$guest_uid;
		$userinfo['user_groupid']=0;
		$userinfo['user_email']='';
		$userinfo['user_sex']='';
		$userinfo['user_hpage']='';
		$userinfo['user_icqid']='';
		$userinfo['user_design']=0;
		$userinfo['user_imoption']=0;
// joe
//		$userinfo['user_sbsmall']=0;
	}// if

	srand((double)microtime()*1000000); 
	$sid=md5(uniqid(rand()));
 

	if(session_is_registered("USER")) {
		session_unregister("USER");
		session_unset();
		@session_destroy();

	}// if
	
	if($config['user_ghost']!=1) 
		$userinfo['user_ghost']=0;
	
	session_id($sid);
	session_start();
	session_register("USER");
	   	
	$HTTP_SESSION_VARS['USER']['sip']=$_SESSION['USER']['sip']=$USER['sip']=$thisUSER['ipaddr'];
	$HTTP_SESSION_VARS['USER']['sbrowser']=$_SESSION['USER']['sbrowser']=$USER['sbrowser']=$thisUSER['browser'];
	$HTTP_SESSION_VARS['USER']['status']=$_SESSION['USER']['status']=$USER['status']=$userinfo['user_status'];
	$HTTP_SESSION_VARS['USER']['id']=$_SESSION['USER']['id']=$USER['id']=$userinfo['user_id'];
	$HTTP_SESSION_VARS['USER']['name']=$_SESSION['USER']['name']=$USER['name']=$userinfo['user_name'];
	$HTTP_SESSION_VARS['USER']['nick']=$_SESSION['USER']['nick']=$USER['nick']=$userinfo['user_nick'];
	$HTTP_SESSION_VARS['USER']['pass']=$_SESSION['USER']['pass']=$USER['pass']=$userinfo['user_pw'];
	$HTTP_SESSION_VARS['USER']['group']=$_SESSION['USER']['group']=$USER['group']=$userinfo['user_groupid'];
	$HTTP_SESSION_VARS['USER']['email']=$_SESSION['USER']['email']=$USER['email']=$userinfo['user_email'];
	$HTTP_SESSION_VARS['USER']['sex']=$_SESSION['USER']['sex']=$USER['sex']=$userinfo['user_sex'];
	$HTTP_SESSION_VARS['USER']['hpage']=$_SESSION['USER']['hpage']=$USER['hpage']=$userinfo['user_hpage'];
	$HTTP_SESSION_VARS['USER']['icqid']=$_SESSION['USER']['icqid']=$USER['icqid']=$userinfo['user_icqid'];
	$HTTP_SESSION_VARS['USER']['design']=$_SESSION['USER']['design']=$USER['design']=$userinfo['user_design'];
	$HTTP_SESSION_VARS['USER']['sigoption']=$_SESSION['USER']['sigoption']=$USER['sigoption']=$userinfo['user_sigoption'];
	$HTTP_SESSION_VARS['USER']['lastlog']=$_SESSION['USER']['lastlog']=$USER['lastlog']=$userinfo['lastlog'];
	$HTTP_SESSION_VARS['USER']['imoption']=$_SESSION['USER']['imoption']=$USER['imoption']=$userinfo['user_imoption'];


// joe
//	$HTTP_SESSION_VARS['USER']['sbsmall']=$_SESSION['USER']['sbsmall']; //=$USER['sbsmall']=$userinfo['user_sbsmall'];
//	session_register("SBSize");
//	$_SESSION['SBSize'] = 0;
//	echo 'session created<br>';
	
	
	if( $userinfo['sid']!='' )
		$HTTP_SESSION_VARS['USER']['logtime']=$_SESSION['USER']['logtime']=$USER['logtime']=$userinfo['logtime'];
	else 
		$HTTP_SESSION_VARS['USER']['logtime']=$_SESSION['USER']['logtime']=$USER['logtime']=pkTIME;
	
	phpkitcookie('user_id');
	phpkitcookie('user_name');
	phpkitcookie('user_pw');
	phpkitcookie('PHPKITSID');
//joe
	phpkitcookie('SBSmall');
	
	if($_REQUEST['login_setcookie']==1 || $_REQUEST['firstlog']==1 || $_REQUEST['relog']==1 || $USER['id']=='0') {
		phpkitcookie('user_id',$userinfo['user_id'],$cookie_expire);
		phpkitcookie('user_name',$userinfo['user_name'],$cookie_expire);
		phpkitcookie('user_pw',$userinfo['user_pw'],$cookie_expire);
		phpkitcookie('PHPKITSID',session_id(),$cookie_expire);

		phpkitcookie('SBSmall', 0, $cookie_expire );

	}// if
	
	$DB->query("INSERT INTO ".$db_tab['session']." (session_id,session_expire,session_userid,session_ip,session_browser,session_url,session_ghost) VALUES ('".session_id()."','".$expire."','".$USER['id']."','".$USER['sip']."','".$USER['sbrowser']."','".$current_url."','".$userinfo['user_ghost']."')");
	$DB->query("DELETE FROM ".$db_tab['session']." WHERE session_userid='".$USER['id']."' AND session_ip='".$USER['sip']."' AND session_browser='".$USER['sbrowser']."' AND session_id!='".session_id()."'");


	if(isset($_REQUEST['login']) && $_REQUEST['remove_page']!='disabled') {
		$DB->query("UPDATE ".$db_tab['user']." SET lastlog='".pkTIME."' WHERE user_id='".$USER['id']."'");
		
		if($_REQUEST['remove_page']=='')
			$remove_page="include.php";
		else 
			$remove_page=$_REQUEST['remove_page'];
		
		header('Location: '.pkWWWROOT.$remove_page.'?event=2&moveto='.urlencode($_REQUEST['remove_path']).'&PHPKITSID='.session_id());
		exit;
	}
	elseif($_REQUEST['event']==30) {
		header('Location: '.pkWWWROOT.'include.php?event=30&PHPKITSID='.session_id());
		exit;
	}
	elseif(isset($_REQUEST['logout'])) {
		if($_REQUEST['remove_path']=='')
			$remove_path=$config['move_logout'];
		else
			$remove_path=$_REQUEST['remove_path'];
		
		header('Location: '.pkWWWROOT.'include.php?event=3&moveto='.urlencode($remove_path).'&PHPKITSID='.session_id());
		exit;
	}
	elseif($_REQUEST['relog']==1 || $_REQUEST['firstlog']==1){
		header('Location: '.pkWWWROOT.'include.php?path=login/profile.php&event=32&PHPKITSID='.session_id());
		exit;
	}// if
	else 
		$PHPKITSID=$_REQUEST['PHPKITSID']=session_id();
		
}// if




unset($session);

if(pkFRONTEND!='public')
	return;

if(!$config['user_design']==1 || !$style=$DB->fetch_array($DB->query("SELECT * FROM ".$db_tab['style']." WHERE style_id='".$USER['design']." AND style_user=1' LIMIT 1"))) 
	{
	$style=$DB->fetch_array($DB->query("SELECT * FROM ".$db_tab['style']." WHERE style_id='".$config['site_style']."' LIMIT 1"));
	}

if(@is_dir($style['style_images'].'/images'))
	$config['imagedir']=$style['style_images'].'/images';

	if(($config['site_eod']!=1 || ($config['forum_eod']!=1 && $config['forum_standalone']==1)) && $USER['status']!="admin") 
		{
		if($config['forum_eod']!=1 && $config['forum_standalone']==1) 
			{
			header('Location: '.pkWWWROOT.'info.php?error=4');
			}
		else 
			{
			header('Location: '.pkWWWROOT.'info.php?error=2');
			}
		exit;
		}

	
$gettemplates=$DB->query("SELECT template_name, template_value FROM ".$db_tab['templates']." WHERE template_packid='".$style['style_template']."'");
while($templates=$DB->fetch_array($gettemplates)) {
	$template_cache[$templates['template_name']]=str_replace("\"","\\\"",$templates['template_value']);
}
	
if($style['style_template']!= -1 && $style['style_template']!= 0) {
	$templatedir=$DB->fetch_array($DB->query("SELECT templatepack_dir FROM ".$db_tab['templatepack']." WHERE templatepack_id=".$style['style_template'].""));
		
	if(@is_dir($templatedir['templatepack_dir']) && $templatedir['templatepack_dir']!='')
		$config['template_dir']=$templatedir['templatepack_dir'];
}


if(pkREQUESTEDFILE!=basename(__FILE__))
	return;

# end config
$site=$site_body=$navigation_top=$navigation_left=$navigation_right=$navigation_bottom=$site_refresh=$path=$file=$src='';
$DB->sqlerrorreport(1);


pkLoadFunc('public');

if(isset($_REQUEST['event']) && !isset($event))
	$event=$_REQUEST['event'];

if($event) 
	include("admin/config/event.php");
	

if(isset($_REQUEST['path']) && !empty($_REQUEST['path']))
	$path=$_REQUEST['path'];
elseif(isset($_REQUEST['file']) && !empty($_REQUEST['file']))
	$file=$_REQUEST['file'];
elseif(isset($_REQUEST['src']) && !empty($_REQUEST['src']))
	$src=$_REQUEST['src'];
else
	$path='start';
	
if($path=='include.php' || $path=='blank.php' || $path=='popup.php')
	{
	unset($path);
	pkEvent('page_not_found');
	}
else {
	$getblacklist=$DB->query("SELECT blacklist_url, blacklist_userstatus FROM ".$db_tab['blacklist']);
	while($blacklist=$DB->fetch_array($getblacklist)) {
		if(eregi($blacklist['blacklist_url'],$current_url) && $blacklist['blacklist_url']!='') 
			{
			if(getrights($blacklist['blacklist_userstatus'])!="true")
				{
				$event=1;
				break;
				}
			}
		}
	
	if($event==1) 
		pkEvent('access_refused');
	else
		{
		ob_start();

		/*try to include via the new source directory (since version 1.6.1)*/
		switch($path)	#exceptions till all links to this file are changed
			{
			case 'login/edtprofil.php' :
				$path='usereditprofile';
				break;
			case 'login/extoption.php' :
				$path='userextoptions';
				break;
			case 'forum/index.php' :
			case 'forum/main.php' :
				$path='forumsdisplay';
				break;
			}	
		
		if(!empty($path))
			$path_filename=pkDIRPUBLIC.(substr($path,-4)=='.php' ? substr(basename($path),0,-4) : $path).pkEXT;				
				
		if(filecheck($path_filename))
			{
			include($path_filename);
			}
		elseif(filecheck($path) && strstr(strtolower($path),'.php') && !strstr(strtolower($path),'http://') && !strstr(strtolower($path),'https://') && !strstr(strtolower($path),'ftp://') && !strstr($path,"../"))
			{
			include($path);
			}
		elseif(!strstr(strtolower($file),'http://') && filecheck($file) && !strstr($file,"../") && file_extension($file)!='php')
			{
			$site_body.=implode('',file($file));
			}
		elseif(!empty($src)) 
			{
			$src=pkEntities($src);
			eval("\$site_body.=\"".getTemplate("site_iframe")."\";");
			}
		else 
			pkEvent('page_not_found');
			
		$site_body.=ob_get_contents();
		ob_end_clean();
		}
	}
pkPublicCalendarUpdate();


$logo_size=@getimagesize($config['site_logo']);
$logo_size=$logo_size[3];
$logo_path=$config['site_logo'];


if($config['site_adview']==1) {
	pkLoadClass($admanage,'admanage');
	$adview=$admanage->get();
}



include("navigation/navigation.php");
include("style.php");

eval("\$site_kopf= \"".getTemplate("site_kopf")."\";");
eval("\$site_metatags= \"".getTemplate("site_metatags")."\";");

$time_stop=pkParsertime();

if(adminaccess('adminarea'))
	eval("\$sitefuss_adminlogin= \"".getTemplate("site_fuss_adminlogin")."\";");
else
	$sitefuss_adminlogin='';

if(empty($config['site_copy']))
	$config['site_copy']=pkEntities($config['site_name']).' &copy '.date('Y');

eval("\$site_fuss= \"".getTemplate("site_fuss")."\";");

/////////////////////////////////////////////////////////////////////
// joe SB eigenschaften 

$ThisSite = $_SERVER['REQUEST_URI'];
if( $USER['name'] == $lang['guest_status'] ) {
	if( $_COOKIE['SBSmall'] == 1 ){
		eval('$ShoutBox= "'. getTemplate("shoutbox/ShoutboxGuestSmall"). '";');
	}// if
	else {
		eval('$ShoutBox= "'. getTemplate("shoutbox/ShoutboxGuest"). '";');
	}


}// if
else {
	if( $_COOKIE['SBSmall'] == 1 ){
		eval('$ShoutBox= "'. getTemplate("shoutbox/ShoutboxSmall"). '";');
	}// if
	else {
		eval('$ShoutBox= "'. getTemplate("shoutbox/Shoutbox"). '";');
	}
}// else

// /joe


eval("\$site_content= \"".getTemplate("site_body")."\";");

eval("echo \$site= \"".getTemplate("site")."\";");

pkPublicRefererLog();

//var_dump( $_SESSION );

?>