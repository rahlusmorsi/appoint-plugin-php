<?php
session_start();
require_once '../config.php';
$admin_page = true;
require_once 'functions.php';

if ( isset( $_POST['submit_setup'] ) ) {
  $_SESSION[SITE_ID][PROFESSIONAL]['name'] = $_POST['name'];
  $_SESSION[SITE_ID][PROFESSIONAL]['email'] = $_POST['email'];
  $_SESSION[SITE_ID][PROFESSIONAL]['phone'] = $_POST['phone'];
  $_SESSION[SITE_ID][PROFESSIONAL]['theme'] = $_POST['theme'];
  $_SESSION[SITE_ID][PROFESSIONAL]['appoint_email'] = $_POST['appoint_email'];
  $_SESSION[SITE_ID][PROFESSIONAL]['appoint_sms'] = $_POST['appoint_sms'];
  $_SESSION[SITE_ID][PROFESSIONAL]['day_start'] = $_POST['day_start'];
  $_SESSION[SITE_ID][PROFESSIONAL]['day_end'] = $_POST['day_end'];
  $_SESSION[SITE_ID][PROFESSIONAL]['slot_type'] = $_POST['slot_type'];
  $_SESSION[SITE_ID][PROFESSIONAL]['appoint_phone'] = $_POST['appoint_phone'];
  $_SESSION[SITE_ID][PROFESSIONAL]['password_required'] = $_POST['password_required'];

  $query =
    "UPDATE ".
    "`professionals` ".
    "SET ".
    "`name` = '".mysql_escape_string( $_SESSION[SITE_ID][PROFESSIONAL]['name'] )."', ".
    "`email` = '".mysql_escape_string( $_SESSION[SITE_ID][PROFESSIONAL]['email'] )."', ".
    "`phone` = '".mysql_escape_string( $_SESSION[SITE_ID][PROFESSIONAL]['phone'] )."', ".
    "`theme` = '".mysql_escape_string( $_SESSION[SITE_ID][PROFESSIONAL]['theme'] )."', ".
    "`appoint_email` = '".mysql_escape_string( $_SESSION[SITE_ID][PROFESSIONAL]['appoint_email'] )."', ".
    "`appoint_sms` = '".mysql_escape_string( $_SESSION[SITE_ID][PROFESSIONAL]['appoint_sms'] )."', ".
    "`appoint_phone` = '".mysql_escape_string( $_SESSION[SITE_ID][PROFESSIONAL]['appoint_phone'] )."', ".
    "`day_start` = '".(int)( $_SESSION[SITE_ID][PROFESSIONAL]['day_start'] )."', ".
    "`day_end` = '".(int)( $_SESSION[SITE_ID][PROFESSIONAL]['day_end'] )."', ".
    "`slot_type` = '".(int)( $_SESSION[SITE_ID][PROFESSIONAL]['slot_type'] )."', ".
    "`password_required` = '".(int)( $_SESSION[SITE_ID][PROFESSIONAL]['password_required'] )."', ".
    "`basic_setup` = 1 ".
    "WHERE ".
    "`id` = ".(int)$_SESSION[SITE_ID][PROFESSIONAL]['id']." ".
    "LIMIT 1 ";
  
  mysql_query( $query ) or die( MYSQLERROR( $query ) );
  $_SESSION[SITE_ID][PROFESSIONAL]['basic_setup'] = 1;
}

list( $day_start_select, $day_end_select ) = get_day_selects();
?>
<html>
<head>
  <title>Professional Setup</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <link href="../themes/admin/styles/main.php" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
if ( $_SESSION[SITE_ID][PROFESSIONAL]['basic_setup'] ) {
  if ( !$_SESSION[SITE_ID][PROFESSIONAL]['planner_setup'] ) {
    echo "[ <a href='./weekly_planner.php'>Weekly Template Setup</a> ]<br>\n";
  }
  else
    echo "[ <a href='./'>Main Menu</a> ]<br>\n";
  $submit_text = 'Update';
}
else {
  echo "Even if you wish no changes, please Confirm this setup.<br>\n";
  $submit_text = 'Confirm';
}
?>
<form method='POST' name='<?php echo SITE_ID; ?>-setup'>
<table class='form'>
  <tr>
    <td colspan='2'>The Fields below are used by us to contact and identify you:</td>
  </tr>
  <tr>
    <td class='label'><label for='name'>Name</label></td>
    <td><input type='name' name='name' id='' value='<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['name']; ?>'></td>
  </tr>
  <tr>
    <td class='label'><label for='email'>Email</label></td>
    <td><input type='text' name='email' id='email' value='<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['email']; ?>'></td>
  </tr>
  <tr>
    <td class='label'><label for='phone'>Phone Number</label></td>
    <td><input type='text' name='phone' id='phone' value='<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['phone']; ?>'></td>
  </tr>
  <tr>
    <td colspan='2'>The Fields below change how your appointment calendar and admin setup tool displays:</td>
  </tr>
  <tr>
    <td class='label'><label for='theme'>Theme</label></td>
    <td><?php echo theme_dropdown(); ?></td>
  </tr>
  <tr>
    <td class='label'><label for='password_required'>Require Password Creation</label></td>
    <td><span style='font-size:14px'>[ <input type='radio' name='password_required' id='password_required' value=1<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['password_required'] ? ' checked' : ''; ?>> Yes ] [ <input type='radio' name='password_required' value=0<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['password_required'] ? '' : ' checked'; ?>> No ]</span></td>
  </tr>
  <tr>
    <td class='label'><label for='day_start'>Hour Day Starts</label></td>
    <td><?php echo $day_start_select; ?></td>
  </tr>
  <tr>
    <td class='label'><label for='day_end'>Hour Day Ends</label></td>
    <td><?php echo $day_end_select; ?></td>
  </tr>
  <tr>
    <td class='label'><label for='slot_type'>Interval Granularity</label></td>
    <td><?php echo slot_type_select(); ?></td>
  </tr>
  <tr>
    <td colspan='2'>The Fields below relate to the information used to create confirmation, alert, and cancellation emails:</td>
  </tr>
  <tr>
    <td class='label'><label for='appoint_email'>Appointment Alert Email</label></td>
    <td><input type='text' name='appoint_email' id='appoint_email' value='<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['appoint_email']; ?>'></td>
  </tr>
  <tr>
    <td class='label'><label for='appoint_phone'>User contact phone number</label></td>
    <td><input type='text' name='appoint_phone' id='appoint_phone' value='<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['appoint_phone']; ?>'></td>
  </tr>
  <tr>
    <td class='label'><label for='appoint_sms'>SMS Alert Email</label></td>
    <td><input type='text' name='appoint_sms' id='appoint_sms' value='<?php echo $_SESSION[SITE_ID][PROFESSIONAL]['appoint_sms']; ?>'></td>
  </tr>
  <tr>
    <td colspan='2' class='button'><input type='submit' name='submit_setup' value='<?php echo $submit_text; ?>' class='button'></td>
  </tr>
</table>
</form>
</body>
</html>
<?php
function theme_dropdown ( ) {
  global $THEMES;
  
  $buf = "<select name='theme' id='theme'><option> - Select a theme - </option>";
  
  foreach ( (array)$THEMES as $theme )
    $buf .= "<option value='$theme'".( ( $theme == $_SESSION[SITE_ID][PROFESSIONAL]['theme'] ) ? ' selected' : '' ).">".ucwords( $theme )."</option>";
  
  $buf .= "</select>";
  
  return $buf;
}

function get_day_selects ( ) {
  $start = "<select name='day_start' id='day_start'>"; // onChange=\"javascript:sync_day_hours('start')\">";
  $end   = "<select name='day_end'   id='day_end'  >"; // onChange=\"javascript:sync_day_hours('end')\">";
  
  for ( $hour = 0; $hour < 24; $hour++ ) {
    $option = "<option value='$hour'%s>".( ( $hour % 12 ) ? ( $hour % 12 ) : '12' ).( ( $hour < 12 ) ? 'am' : 'pm' )."</option>";
    $start .= sprintf( $option, ( ( $hour == $_SESSION[SITE_ID][PROFESSIONAL]['day_start'] ) ? ' selected' : '' ) );
    $end   .= sprintf( $option, ( ( $hour == $_SESSION[SITE_ID][PROFESSIONAL]['day_end'] ) ? ' selected' : '' ) );
  }
  $start .= "</select>";
  $end   .= "</select>";
  
  return array( $start, $end );
}

function slot_type_select ( ) {
  global $SLOT_TYPES;
  
  $buf = "<select name='slot_type' id='slot_type'>";
  
  foreach ( (array)$SLOT_TYPES as $slot_type => $slot_name )
    $buf .= "<option value='$slot_type'".( ( $slot_type == $_SESSION[SITE_ID][PROFESSIONAL]['slot_type'] ) ? ' selected' : '' ).">$slot_name</option>";
  
  $buf .= "</select>";
  
  return $buf;
}
?>