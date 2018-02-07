<?php

if ($config['forum_eod']!=1) {
 if ($config['forum_standalone']==1) {header ("location: info.php?event=28&PHPKITSID=".session_id()); exit();}
 else {header("location: include.php?event=28&PHPKITSID=".session_id()); exit();}
 }
else {
 if (intval($_REQUEST['threadid'])>0) $threadid=$_REQUEST['threadid'];
 else unset($threadid);
 if (intval($_REQUEST['catid'])>0) $catid=$_REQUEST['catid'];
 else unset($catid);

 $getallcats=forencats();
 $forumcat_cache=$getallcats[0]; 
 $forumcat_cache_byname=$getallcats[1]; 
 $catcount=$getallcats[2]; 
 $threadcount=$getallcats[3]; 
 $postcount=$getallcats[4];

 if (intval($USER['id'])>0) {
  if (intval($_REQUEST['newposttime'])>0) {
   session_unregister("isreaded_thread_id"); 
   session_unregister("isreaded_cat_id"); 
   session_unregister("posttime"); 
   session_register("posttime"); 
   $HTTP_SESSION_VARS['posttime']=$_SESSION['posttime']=$posttime=$_REQUEST['newposttime'];
   $DB->query("UPDATE ".$db_tab['user']." SET lastlog='".$posttime."' WHERE user_id='".$USER['id']."'");
   }
  elseif (!$_SESSION['posttime']>0) {
   session_unregister("posttime");
   session_register("posttime");
   $HTTP_SESSION_VARS['posttime']=$_SESSION['posttime']=$posttime=$USER['lastlog'];
   }
  else $posttime=$_SESSION['posttime'];
  
  if ($path=="forum/main.php" || $path=="forum/showcat.php") {
   session_register("isreaded_cat_id");
   $isreaded_cat_id=$_SESSION['isreaded_cat_id'];
   }
  if ($path=="forum/showthread.php") session_register("isreaded_thread_id"); 
  $isreaded_thread_id=$_SESSION['isreaded_thread_id'];

  $user_nick=$USER['nick'];
  $online_time=formattime($USER['logtime'],'','time');

  $favstatus=$DB->fetch_array($DB->query("SELECT COUNT(*) FROM ".$db_tab['forumfav']." WHERE forumfav_userid='".$USER['id']."' LIMIT 1"));
  if ($favstatus[0]>0) eval ("\$kopf_favorit= \"".getTemplate("forum/kopf_favorit")."\";");

  $newposts=array();
  $newposts[0]=0;

  $sqlcommand="SELECT ".$db_tab['forumpost'].".forumpost_threadid, ".$db_tab['forumpost'].".forumpost_id FROM ".$db_tab['forumpost']." LEFT JOIN ".$db_tab['forumthread']." ON ".$db_tab['forumthread'].".forumthread_id=".$db_tab['forumpost'].".forumpost_threadid LEFT JOIN ".$db_tab['forumcat']." ON ".$db_tab['forumcat'].".forumcat_id=".$db_tab['forumthread'].".forumthread_catid WHERE (".sqlrights($db_tab['forumcat'].".forumcat_rrights")." OR ".$db_tab['forumcat'].".forumcat_mods LIKE '%-".$USER['id']."-%' OR ".$db_tab['forumcat'].".forumcat_user LIKE '%-".$USER['id']."-%') AND ".$db_tab['forumpost'].".forumpost_time>'".$posttime."'";
  if ($isreaded_thread_id) {
   foreach ($isreaded_thread_id as $x) {
    $sqlcommand.=" AND (".$db_tab['forumpost'].".forumpost_threadid!='".$x[0]."' OR ".$db_tab['forumpost'].".forumpost_time>'".$x[1]."')";
	}
   }
  $getnewposts=$DB->query($sqlcommand);
  while($newpost=$DB->fetch_array($getnewposts)) {
   $newposts[0]++; 
   if ($path=='forum/searchresult.php' && $_REQUEST['show']=='new') $rposts[]=array($newpost['forumpost_id'],$newpost['forumpost_threadid']);
   }

  if ($newposts[0]>0) {
   if ($newposts[0]==1) $newpostlang=$lang['unreaded_thread'];
   else $newpostlang=$lang['unreaded_threads'];
   eval ("\$new_posts= \"".getTemplate("forum/kopf_newposts_true")."\";");
   }
   else {  
    session_unregister("posttime");  
    session_register("posttime");  
    $HTTP_SESSION_VARS['posttime']=$_SESSION['posttime']=$posttime=time();  
    $DB->query("UPDATE ".$db_tab['user']." SET lastlog='".time()."' WHERE user_id='".$USER['id']."'");  
    eval ("\$new_posts= \"".getTemplate("forum/kopf_newposts_false")."\";");  
   }  
  $new_posts.=' '.formattime($posttime);
  if (intval($imstatus_info=imstatus())>0) eval ("\$new_im_msg= \"".getTemplate("forum/kopf_newim")."\";");

  if ($newposts[0]>0) eval ("\$kopf_unsetnew= \"".getTemplate("forum/kopf_unsetnew")."\";");
  if (adminaccess()) eval ("\$kopf_admin= \"".getTemplate("forum/kopf_admin")."\";");
  eval ("\$kopf_userinfo= \"".getTemplate("forum/kopf_userinfo")."\";");
  eval ("\$kopf_logreg= \"".getTemplate("forum/kopf_logout")."\";");
  }
 else {
  eval ("\$kopf_logreg= \"".getTemplate("forum/kopf_login")."\";");
  eval ("\$kopf_login= \"".getTemplate("forum/kopf_login_small")."\";");
  }


$phpkit_status=phpkitstatus();
// var_dump( $phpkit_status['bd_user'] );
if (is_array($phpkit_status['bd_user'])) {
 unset($bd_user);
 foreach($phpkit_status['bd_user'] as $status) {
  unset($age);
  $age=getAge($status['user_bd_day'],$status['user_bd_month'],$status['user_bd_year']);
  if ($bd_user) $bd_user.=', ';
  eval ("\$bd_user.= \"".getTemplate("forum/fuss_bduser")."\";");
  }
 eval ("\$kopf_adds_inner= \"".getTemplate("forum/fuss_adds_bduser")."\";");
 }
//
//
//var_dump( $posttime );
//var_dump( time() );
//var_dump( $newposts[0] );
//var_dump( $_SESSION );
//
//
//



 if ($threadid>0) {
  $forumthread=$DB->fetch_array($DB->query("SELECT * FROM ".$db_tab['forumthread']." WHERE forumthread_id='".$threadid."'"));
  $catid=$forumthread['forumthread_catid'];
  $forumcat=$forumcat_cache[$forumthread['forumthread_catid']];
  if (userrights($forumcat['forumcat_mods'],$forumcat['forumcat_rrights'])=="true" || userrights($forumcat['forumcat_user'],$forumcat['forumcat_rrights'])=="true" || getrights($forumcat['forumcat_rrights'])=="true") {
   if ($path=="forum/newpost.php" || $path=="forum/editpost.php" || $path=="forum/moderate.php") {
    $thread_title=htmlentities($forumthread['forumthread_title']);
    eval ("\$forum_path= \"".getTemplate("forum/kopf_threadlink")."\";");
	}
   else $forum_path.=' &#187; '.htmlentities($forumthread['forumthread_title']);
   }
  else $forum_path.=' &#187; '.$lang['access_refuse'];
  if ($forumthread['forumthread_status']==0) eval ("\$forum_action= \"".getTemplate("forum/action_closed")."\";");
  }

 if ($catid>0) {
  $forumcat=$FORUM->getcats($catid);
  if ($path=="forum/showcat.php") $forum_path=" &#187; ".$forumcat['forumcat_name'];
  else {
   eval ("\$forum_pathadd= \"".getTemplate("forum/kopf_catlink")."\";");
   $forum_path=$forum_pathadd.$forum_path;
   }
  if ($forumcat['forumcat_subcat']>0) {
   while ($forumcat['forumcat_subcat']>0) {
    $forumcat=$FORUM->getcats($forumcat['forumcat_subcat']);
	eval ("\$forum_pathadd= \"".getTemplate("forum/kopf_catlink")."\";"); 
	$forum_path=$forum_pathadd.$forum_path; 
	}
   $forumcat=$FORUM->getcats($catid);
   }
  if ($path=="forum/showthread.php") {
   if ($forumcat['forumcat_status']!=1) eval ("\$forum_action= \"".getTemplate("forum/action_closed")."\";");
   elseif ($forumthread['forumthread_status']==0 || $forumthread['forumthread_status']==3) eval ("\$forum_action= \"".getTemplate("forum/action_closed")."\";");
   elseif (getrights($forumcat['forumcat_wrights'])=="true" || userrights($forumcat['forumcat_mods'],$forumcat['forumcat_wrights'])=="true" || userrights($forumcat['forumcat_user'],$forumcat['forumcat_wrights'])=="true") {
    if ((getrights($forumcat['forumcat_trights'])=="true" || userrights($forumcat['forumcat_mods'],$forumcat['forumcat_trights'])=="true" || userrights($forumcat['forumcat_user'],$forumcat['forumcat_trights'])=="true") && $forumcat['forumcat_threads_option']==1) eval ("\$forum_action.= \"".getTemplate("forum/action_thread")."\";");
	eval ("\$forum_action.= \"".getTemplate("forum/action_answer")."\";");
	}
   }
  elseif ($path=="forum/showcat.php") {
   if ($forumcat['forumcat_status']!=1) eval ("\$forum_action.= \"".getTemplate("forum/action_closed")."\";");
   elseif ((getrights($forumcat['forumcat_trights'])=="true" || userrights($forumcat['forumcat_mods'],$forumcat['forumcat_trights'])=="true" || userrights($forumcat['forumcat_user'],$forumcat['forumcat_trights'])=="true") && $forumcat['forumcat_status']==1 && $forumcat['forumcat_threads_option']==1) eval ("\$forum_action.= \"".getTemplate("forum/action_thread")."\";");
   }
  }
	
 if ($path=="forum/newpost.php") {
  if (intval($threadid)>0) {
   $forum_path.=' &#187; '.$lang['forum_new_answer'];
   $action_type=$lang['forum_new_answer'].' '.$lang['in'].' '.htmlentities($forumthread['forumthread_title']);
   }
  else {
   $forum_path.=' &#187; '.$lang['forum_new_thread']; 
   $action_type=$lang['forum_new_thread'].' '.$lang['in'].' '.$forumcat['forumcat_name'];
   }
  }
 elseif ($path=="forum/editpost.php") $forum_path.=' &#187; '.$lang['forum_editpost'];
 elseif ($path=="forum/favorits.php") $forum_path.=' &#187; '.$lang['forum_favorits'];
 elseif ($path=="forum/moderate.php") $forum_path.=' &#187; '.$lang['forum_moderate'];
 elseif ($path=="forum/report.php") $forum_path.=' &#187; '.$lang['forum_report'];
 elseif ($path=="forum/search.php") $forum_path.=' &#187; '.$lang['forum_search'];
 elseif ($path=="forum/searchresult.php") $forum_path.=' &#187; '.$lang['forum_searchresult'];
 elseif ($path=="forum/showinfo.php") $forum_path.=' &#187; '.$lang['forum_showinfo'];
 elseif ($path=="forum/topuser.php") $forum_path.=' &#187; '.$lang['forum_topuser'];
 elseif ($path=="forum/team.php") $forum_path.=' &#187; '.$lang['forum_team'];
// elseif ($path=="forum/help.php") {$forum_path.=" &#187; Foren-Hilfe"; eval ("\$kopf_help= \"".getTemplate("forum/kopf_help","")."\";");}

 if ($config['forum_standalone']==1) {
  eval ("\$kopf_extension= \"".getTemplate("forum/kopf_extension")."\";");
  eval ("\$navigation_top= \"".getTemplate("forum/kopf")."\";");
  }
 else eval ("\$site_body.= \"".getTemplate("forum/kopf")."\";");
 }




if (isset($_REQUEST['setsig'])) {
 session_unregister("user_sigoption");
 session_register("user_sigoption");
 $HTTP_SESSION_VARS['USER']['sigoption']=$_SESSION['USER']['sigoption']=$USER['sigoption']=$_REQUEST['setsig'];
 }

if (isset($_REQUEST['changestyle'])) {
 session_register("forum_style");
 if ($_REQUEST['changestyle']==1) $HTTP_SESSION_VARS['forum_style'][0]=$_SESSION['forum_style'][0]=$forum_style[0]=1;
 else $HTTP_SESSION_VARS['forum_style'][0]=$_SESSION['forum_style'][0]=$forum_style[0]=0;
 }
elseif (session_is_registered("forum_style")) $forum_style[0]=$_SESSION['forum_style'][0];
else {
 session_register("forum_style");
 $HTTP_SESSION_VARS['forum_style'][0]=$_SESSION['forum_style'][0]=$forum_style[0]=$config['forum_structur'];
 }
 
 
//var_dump( $_SESSION ); 
?>