<?php
session_start();
require_once '../config.php';
$admin_page = true;
require_once 'functions.php';
/*
if ( !$_SESSION[SITE_ID][PROFESSIONAL]['basic_setup'] ) {
  header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/setup.php" );
  exit;
}
else if ( !$_SESSION[SITE_ID][PROFESSIONAL]['planner_setup'] ) {
  header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/weekly_modifier.php" );
  exit;
}
*/
?>
<html>
<head>
<title>Professional</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../themes/admin/styles/main.php" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<ul style='list-style-type:none'>Manage your Appointment Calendar:
  <li><a href='setup.php'>Setup</a></li>
  <li><a href='planner.php'>Time Planner</a></li>
  <li><a href='appointments.php'>Appointment Types</a></li>
  <li><a href='../<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['label']; ?>/?pid=<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['pid']; ?>'>Administrate Plugin</a></li>
  <li>&nbsp;</li>
<!--
  <li><a href='../<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['label']; ?>/?<?php echo SITE_ID; ?>-plogout'>Admin Logout</a></li>
-->
  <li><a href='?<?php echo SITE_ID; ?>-plogout'>Admin Logout</a></li>
</ul>
</body>
</html>