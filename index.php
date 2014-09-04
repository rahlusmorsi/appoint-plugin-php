<?php
session_start();
require_once 'config.php';
require_once 'admin/functions.php';

// get required data
// get host name from URL
//preg_match( "/^(?:http:\/\/)?([^\/]+)(?:\/([^\/]*)\/)?(.*)?/i", $_SERVER['SCRIPT_URI'], $MATCHES );
preg_match( "/^(?:http:\/\/)?([^\/]+)\/([^\/]+)(?:(?:\/)(.*))?$/i", $_SERVER['SCRIPT_URI'], $MATCHES );
$domain = $MATCHES[1];
$dir = $MATCHES[2];
$rest = $MATCHES[3];

$DOMAIN = split( "\.", $domain );

if ( $DOMAIN[0] != 'www' && $DOMAIN[0] != SITE_DOMAIN ) {
  $PROFESSIONAL = get_professional_by_label( $DOMAIN[0] );
}
else {
// if ( !$PROFESSIONAL['id'] )
  $PROFESSIONAL = get_professional_by_label( $dir );

  if ( !$PROFESSIONAL['id'] && $_GET['id'] )
    $PROFESSIONAL = get_professional_by_id( $_GET['id'] );
}

/*
// added for testing
if ( !$PROFESSIONAL['id'] )
  $PROFESSIONAL = get_professional_by_id( 3 );
*/

if ( !$PROFESSIONAL['id'] ) {
  header( "HTTP/1.0 404 Not Found" );
?>
<h1>Virtual Calendar Setup Error:</h1>
<p>Please alert the webmaster of this site that the Virtual Calendar has not been configured correctly.  They can visit <a href='http://www.appoint-plugin.com' target='_BLANK'>www.appoint-plugin.com</a> and click on customer service for more information.</p>
<?php
  exit;
}
  
define( 'SITE_ID', 'CWD_APPOINT-PLUGIN_'.$PROFESSIONAL['label'].'_'.$PROFESSIONAL['id'] );

if ( $_SESSION[SITE_ID][USER]['id'] == $PROFESSIONAL['id'] ) {
  foreach ( (array)$PROFESSIONAL as $key => $value )
    $_SESSION[SITE_ID][USER][$key] = $value;
}
else {
  unset( $_SESSION[SITE_ID][USER] );
  $_SESSION[SITE_ID][USER] = $PROFESSIONAL;
}

if ( !( $pid = $_SESSION[SITE_ID][USER]['id'] ) ) {
//  echo "Domain: $domain<br>\nDir: $dir<br>\nRest: $rest<br>\nDomain:".PRINTR( $DOMAIN )."Rest:<br>".PRINTR( $_SERVER ).PRINTR( $_GET ).PRINTR( $_POST );
  exit; // if someone gets here 'unauthorised'...
}

if ( isset( $_GET['check'] ) ) {
  if ( isset( $_GET['ts'] ) ) {
    $time_check = $_GET['ts'];
    if ( $time_check < time() ) {
      echo "0";
      // echo "UNAVAILABLE\r\n\r\n------\r\nAVAILABLE: 0\r\nERROR: Timestamp is in the past.\r\n";
      exit;
    }
  }
  else
    $time_check = time();
  
  $time_requested = isset( $_GET['seconds'] ) ? $_GET['seconds'] : 30 * 60; // 10 minutes
  
  $time_available = TIMELEFT( $time_check );
  if ( $time_available > $time_requested )
    // echo "AVAILABLE\r\n\r\n------\r\nAVAILABLE: ".HOURS_MINUTES( $time_available / 60 )."\r\n";
    echo "1";
  else
    // echo "UNAVAILABLE\r\n\r\n------\r\nAVAILABLE: ".HOURS_MINUTES( $time_available / 60 )."\r\n";
    echo "0";
  
  // echo "REQUESTED TIME: ".HOURS_MINUTES( $time_requested / 60 )."\n\r";
  exit;
}

// override increment, etc:
$_SESSION[SITE_ID][USER]['slot_type'] = 3;

// load variables for theme
include 'themes/'.$_SESSION[SITE_ID][USER]['theme'].'/variables.php';

list( $user, $rest ) = split( "\/", $rest, 2 );

if ( !empty( $user ) && !isset( $_GET['logout'] ) ) { // && eregi( "[a-zA-Z0-9_-]+", $user ) ) {
  $query =
    "SELECT ".
    "`id` ".
    "FROM ".
    "`users` ".
    "WHERE ".
    "`pid` = $PROFESSIONAL[id] ".
    "AND ".
    ( $_SESSION[SITE_ID][USER]['password_required']
      ? "MD5( CONCAT( `email`, `password` ) ) = '".mysql_escape_string( $user )."' "
      : "`email` = '".mysql_escape_string( $user )."' "
    ).
    "LIMIT 1 ";
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  if ( $USER = mysql_fetch_assoc( $result ) ) {
    LOGIN_USER( $USER['id'] );
    $md5 = true;
  }
  else {
    if ( VALID_EMAIL( $user ) ) { // is the username a valid email address?
      list( $name, $phone, $rest ) = split( "\/", $rest, 3 );
      if ( !$_SESSION[SITE_ID][USER]['password_required'] ) { // only create new users if no password required
        $query =
          "INSERT INTO `users` ( `pid`, `email`, `name`, `phone` ) VALUES ".
          "( $PROFESSIONAL[id], '".mysql_escape_string( $user )."', '".mysql_escape_string( $name )."', '".mysql_escape_string( $phone )."' ) ";
        if ( @mysql_query( $query ) ) { // just in case something funny, hide any error
          LOGIN_USER( mysql_insert_id() );
        }
      }
      else {
        $_SESSION[SITE_ID][USER]['login_string'] = $user;
      }
    }
  }
}
if ( isset( $_SESSION[SITE_ID][USER]['userdata']['id'] ) && !isset( $_GET['logout'] ) && !$md5 ) {
  $query =
    "SELECT ".
    "`id`, ".
    ( $_SESSION[SITE_ID][USER]['password_required']
      ? "MD5( CONCAT( `email`, `password` ) ) AS 'md5' "
      : "`email` "
    ).
    "FROM ".
    "`users` ".
    "WHERE ".
    "`id`= '".(int)$_SESSION[SITE_ID][USER]['userdata']['id']."' ".
    "AND ".
    "`pid` = ".(int)$PROFESSIONAL['id']." ".
    "LIMIT 1 ";
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  if ( $USER = mysql_fetch_assoc( $result ) ) {
    if ( !empty( $_SERVER['QUERY_STRING'] ) )
      $query_string = '?'.$_SERVER['QUERY_STRING'];
    header( "Location:http://www.appoint-plugin.com/".$PROFESSIONAL['label']."/".( $_SESSION[SITE_ID][USER]['password_required'] ? $USER['md5'] : $USER['email'] )."/$rest$query_string" );
    exit;
  }
  else {
    unset( $_SESSION[SITE_ID][USER]['userdata'] );
    setcookie( SITE_ID.$_SESSION[SITE_ID][USER]['id'], '', time() - 3600, "/" ); 
    $login_string = "<form method='POST' name='login' action='?'>Email:<input onFocus=\"this.value=''\" type='text' name='email' value='".$_SESSION[SITE_ID][USER]['login_string']."' class='login_text'>".( $_SESSION[SITE_ID][USER]['password_required'] ? " Password:<input type='password' name='pw' class='login_text password'>" : "" )."<input type='submit' name='LogIn' value='>Login' class='login_button'></form>";
    $logout_string = '';
  }
}

if ( isset( $_GET['month'] ) ) {
  $month = $_GET['month'];
  $year = $_GET['year'];
  $day = $_GET['day'];

  $mode = $_GET['mode'];
}
else if ( ( $ts = $_GET['ts'] ) ) {
  $mode = $_GET['mode'];
  $month = date("m", $ts );
  $year = date("Y", $ts );
  $day = date("d", $ts );  
}
else {
  $month = date("m");
  $year = date("Y");
  $day = date("d");
  if ( $_GET['mode'] != "book" ) {
    $mode = $PROFESSIONAL['default_mode'];
//    'weekly';
  }
}

$current_timestamp = mktime( 0, 0, 0, $month, $day, $year );
$current_timestring = date( "l, F jS, Y", $current_timestamp );

$lastmonth = $month - 1;
$nextmonth = $month + 1;

// figure out prior month & year
$lastyear = $year;
$lastmonth = $month - 1;
if ( $lastmonth == 0 ) {
  $lastmonth = 12;
  $lastyear = $year - 1;
}

// figure out next month & year
$nextyear = $year;
$nextmonth = $month + 1;

if ( $nextmonth == 13 ) {
  $nextmonth = 1;
  $nextyear = $year + 1;
}

$nextweek = $current_timestamp + 7 * 24 * 60 * 60;
$lastweek = $current_timestamp - 7 * 24 * 60 * 60;

if ( VERIFY_PROFESSIONAL() ) {
  $admin = true;
  $admin_login_string = "Logged in as Admin";
  $admin_logout_string = "[<a href='?".SITE_ID."-plogout'>Log Out</a>]";
}

if ( !LOGOUT_USER() ) { // && !$admin ) {
  VERIFY_USER();
}
?>

<html>
<head>
<title>Realtime Virtual Calendar for <?php echo $_SESSION[SITE_ID][USER]['name']; ?> - www.Appoint-Plugin.com</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/themes/<?php echo $_SESSION[SITE_ID][USER]['theme']; ?>/styles/main.css" rel="stylesheet" type="text/css">
<?php include 'themes/'.$_SESSION[SITE_ID][USER]['theme'].'/headcode.php'; ?>



</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<center>
<?php
//echo "<div style='background:#FFF;color:#000;padding:10px;border:2px solid #000'>".SITE_ID."<hr>".PRINTR( $_SESSION[SITE_ID][USER]['userdata'] )."</div>\n";
//echo "<div style='background:#FFF;color:#000;padding:10px;border:2px solid #000'>".PRINTR( $_SESSION[SITE_ID][USER] )."</div>\n";
if ( $cancel_id = $_GET['cancel'] ) {
  
  $cancel_query =
    "SELECT ".
    "`uid`, UNIX_TIMESTAMP( `start` ) ".
    "FROM ".
    "`appointments` ".
    "WHERE ".
    "`id` = '$cancel_id' ".
    "LIMIT 1 ";
    
  $cancel_result = mysql_query( $cancel_query ) or die( MYSQLERROR( $cancel_query ) );
  
  if ( $APPT_CANCEL = mysql_fetch_assoc( $cancel_result ) ) {
    if ( !$admin && $APPT_CANCEL['uid'] != $_SESSION[SITE_ID][USER]['userdata']['id'] ) {
      $general_messages .= DISPLAY_MESSAGEBOX( "You can only cancel your own Appointments.", "Cancel Appointment", "error" );
    }
    else if ( !$admin && $start_time >= time() - $_SESSION[SITE_ID][USER]['cancel_window'] ) {
      $general_messages .= DISPLAY_MESSAGEBOX( "It is best to cancel at least 24 hours in advance of your appointment.", "Cancel Appointment", "error" );
    }
    $general_messages .= SEND_AN_EMAIL( "cancel", $cancel_id );
    
    $delete_query =
      "DELETE ".
      "FROM ".
      "`appointments` ".
      "WHERE ".
      "`id` = '$cancel_id' ".
      "LIMIT 1 ";
      
    mysql_query( $delete_query ) or die( MYSQLERROR( $query ) );
    $general_messages .= DISPLAY_MESSAGEBOX( "If you need to reschedule please select an available time.", "Appointment Canceled", "warn" );
  }
  else {
    $general_messages .= DISPLAY_MESSAGEBOX( "No Appointment to Cancel.", "Cancel Appointment", "error" );
  }
}

$show_form = true;
if ( isset( $_POST['Submit'] ) ) {

  // appointment specific info
  list( $duration, $type ) = split( ':', $_POST['duration'] );
  $public   = mysql_escape_string( $_POST['notes'] );
  $private  = mysql_escape_string( $_POST['private'] );
  $ts = $_GET['ts'];
  
  if ( $ts > time() ) {
    if ( $_SESSION[SITE_ID][USER]['userdata']['id'] || $admin ) {
      $show_form = false;
      
//       if ( $admin ) {
//         $name = $_POST['name'];
//         $phone = $_POST['phone'];
//       }
//      else {
        $name = $_SESSION[SITE_ID][USER]['userdata']['name'];
        $phone = $_SESSION[SITE_ID][USER]['userdata']['phone'];
//      }
      
      if ( empty($_POST['duration']) || empty( $name ) || empty( $phone ) && $type ) {
        $booking .= DISPLAY_MESSAGEBOX( "You must specify an Email, ".( $_SESSION[SITE_ID][USER]['password_required'] ? "a Password, " : '' )."your Name and Phone Number, and an appointment type to book an appointment.", "Book Appointment", "error" );
        $show_form = true;
      }
      else if ( $duration > TIMELEFT( $ts ) ) {
        $general_messages .= DISPLAY_MESSAGEBOX( "Sorry $name. That time slot has already been reserved.", "Appointment Time Conflict", "error" );      // error message
        $show_form = false;
        $mode = 'daily';
      }
      else { // insert it and so on ...
        $query =
          "INSERT INTO ".
          "`appointments` VALUES(NULL, $pid, ".(int)$_SESSION[SITE_ID][USER]['userdata']['id'].", '".mysql_escape_string( $name )."', FROM_UNIXTIME( $ts ), ".(int)$duration.", '$public', '$private', ".(int)$type." )";
      
        mysql_query( $query ) or die( MYSQLERROR( $query ) );
        
        $general_messages .= SEND_AN_EMAIL( "book", mysql_insert_id() );
  
        $general_messages = DISPLAY_MESSAGEBOX( "Thank you $name. Your appointment has been set for: ".date( 'l, F jS, Y \a\t g:ia', $ts ), 'Appointment Confirmed', 'okay' );
        $show_form = false;
        $mode = 'monthly';
      }
    }
  }
  else {
    $booking .= DISPLAY_MESSAGEBOX( 'There is an error with the date and time. Please go back and select a valid date and time.', 'Make Booking', 'error' );
    $mode = 'daily';
  }
}

echo $general_messages;

if ( $mode == 'weekly' ) {
  $day_of_week = date( 'w', $current_timestamp );
  $sunday = $current_timestamp - $day_of_week * 24 * 60 * 60;
  $week_month = date( 'n', $sunday );
  $week_year = date( 'Y', $sunday );
  $week_date = date( 'j', $sunday );
  
  $saturday = $sunday + 6 * 24 * 60 * 60;
  
  if ( date( 'Y', $sunday ) != date( 'Y', $saturday ) )
    $dispmonth = "<a href='?mode=monthly&ts=$sunday'>".date( 'F', $sunday )."</a> ".date( 'jS, Y', $sunday );
  else
    $dispmonth = "<a href='?mode=monthly&ts=$sunday'>".date( 'F', $sunday )."</a> ".date( 'jS', $sunday );
    
  $dispmonth .= " - <a href='?mode=monthly&ts=$saturday'>".date( 'F', $saturday )."</a> ".date( 'jS, Y', $saturday );
  
  $calendar .=
    "<table class='tableborder' cellpadding=0 cellspacing=0>".
    "  <tr class='header'>\n".
    "    <td><a href='?mode=weekly&ts=$lastweek'><img src='/themes/".$_SESSION[SITE_ID][USER]['theme']."/images/arrowleft.gif'></a></td>\n".
    "    <td colspan=5 align=center><B class='monthyear'>$dispmonth</b></td>\n".
    "    <td align='right'><a href='?mode=weekly&ts=$nextweek'><img src='/themes/".$_SESSION[SITE_ID][USER]['theme']."/images/arrowright.gif'></a></td>\n".
    "  </tr>\n".
    "  <tr><td class='weekday' align='center'><B>Sun</b></td><td class='weekday' align='center'><B>Mon</b></td><td class='weekday' align='center'><B>Tue</b></td><td class='weekday' align='center'><B>Wed</b></td><td class='weekday' align='center'><B>Thu</b></td><td class='weekday' align='center'><B>Fri</b></td><td class='weekday' align='center'><B>Sat</b></td></tr>\n";
  
  $calendar .= "  <tr>\n";
  
  $today = date( "d" );
  $todaymonth = date( "m" );
  $todayyear = date( "Y" );
  
  for ( $idate = 0; $idate < 7; $idate++ ) {
    $dts = mktime( 0, 0, 0, $week_month, $week_date + $idate, $week_year );
    $date = date( 'j', $dts );
    $howbooked = HOW_BOOKED( date( 'Y', $dts ), date( 'm', $dts ), $date );
    $link = "?ts=$dts&mode=daily";
    $calendar .= "<td onClick=\"window.location='$link'\" class='day$howbooked' $cellsize  valign='top'>&nbsp;$date<BR><a href='$link'><center><img class='imgpad' src='/themes/".$_SESSION[SITE_ID][USER]['theme']."/images/cut.gif'></center></a></td>"; 
  }  
    
  $calendar .=
    "  </tr>\n".
    "  <tr class='header'>\n".
    "    <td colspan='7' align='center'>$login_string$logout_string</td>\n".
    "  </tr>\n".
    ( $admin
    ?
    "  <tr class='header'>\n".
    "    <td colspan='7' align='center'>$admin_login_string$admin_logout_string</td>\n".
    "  </tr>\n"
    : "" ).
    "</table>\n";
    
  echo $calendar;
}
else if ( $mode == 'monthly' ) {
  $first_of_month = 1 + date( "w", mktime( 0, 0, 0, $month, 1, $year ) ); 
  $daysinmonth = date( "j", mktime( 0, 0, 0, $month + 1, 0, $year ) );
  $dispmonth = date("F",mktime( 0, 0, 0, $month, 1, $year ) );
  $calendar .=
    "<table class='tableborder' cellpadding=0 cellspacing=0>".
    "  <tr class='header'>\n".
    "    <td><a href='?mode=monthly&month=$lastmonth&year=$lastyear'><img src='/themes/".$_SESSION[SITE_ID][USER]['theme']."/images/arrowleft.gif'></a></td>\n".
    "    <td colspan=5 align=center><B class='monthyear'>$dispmonth $year</b></td>\n".
    "    <td align='right'><a href='?mode=monthly&month=$nextmonth&year=$nextyear'><img src='/themes/".$_SESSION[SITE_ID][USER]['theme']."/images/arrowright.gif'></a></td>\n".
    "  </tr>\n".
    "  <tr><td class='weekday' align='center'><B>Sun</b></td><td class='weekday' align='center'><B>Mon</b></td><td class='weekday' align='center'><B>Tue</b></td><td class='weekday' align='center'><B>Wed</b></td><td class='weekday' align='center'><B>Thu</b></td><td class='weekday' align='center'><B>Fri</b></td><td class='weekday' align='center'><B>Sat</b></td></tr>\n";
  
  $calendar .= "  <tr>\n";
  $extradaysneeded = $first_of_month - 1;
  
  for ( $d = 1; $d <= $extradaysneeded; $d++ )
    $calendar .= "<td class='weekday' $cellsize  valign='top'>&nbsp;</td>";
  
  $today = date( "d" );
  $todaymonth = date( "m" );
  $todayyear = date( "Y" );
  
  for ( $date = 1; $date <= $daysinmonth; $date++ ) {
    $howbooked = HOW_BOOKED( $year, $month, $date );
    $dts = mktime( 0, 0, 0, $month, $date, $year );
    $link = "?ts=$dts&mode=daily";
    $calendar .= "<td onClick=\"window.location='$link'\" class='day$howbooked' $cellsize  valign='top'>&nbsp;$date<BR><a href='$link'><center><img class='imgpad' src='/themes/".$_SESSION[SITE_ID][USER]['theme']."/images/cut.gif'></center></a></td>"; 
    
    if ( $first_of_month == 7 ) {
       $calendar .= "</tr>\n  <tr>";
       $first_of_month=0;
    }
    $first_of_month++;
  }  
  
  while ( $first_of_month++ <= 7 )
    $calendar .= "  <td style='border: 0;'></td>\n";
    
  $calendar .=
    "  </tr>\n".
    "  <tr class='header'>\n".
    "    <td colspan='7' align='center'>$login_string$logout_string</td>\n".
    "  </tr>\n".
    ( $admin
    ?
    "  <tr class='header'>\n".
    "    <td colspan='7' align='center'>$admin_login_string$admin_logout_string</td>\n".
    "  </tr>\n"
    : "" ).
    "</table>\n";
    
  echo $calendar;
}
else if ( $mode == 'daily' ) { //daily view
  $beginof_date = $_SESSION[SITE_ID][USER]['day_start'] * 60;
  $endof_date = $_SESSION[SITE_ID][USER]['day_end'] * 60;
    
  $daily .=
    "<table width='380' class='tableborder' cellpadding=0 cellspacing=0>\n".
    "  <tr class='header'><td width='100'><a class='links' href='?ts=$ts&mode=monthly'><img src='/themes/".$_SESSION[SITE_ID][USER]['theme']."/images/arrowleft.gif'>".date( 'F', $ts )."</a></td>\n".
    "    <td width='280' class='links' align='right'><a href='?mode=daily&ts=".( $ts - 60 * 60 * 24 )."'>&lt;&lt; </a>".$current_timestring."<a href='?mode=daily&ts=".( $ts + 60 * 60 * 24 )."'> &gt;&gt;</a></td>\n".
    "  </tr>\n";
 
// disabled at present by overriding slot type
  switch( $_SESSION[SITE_ID][USER]['slot_type'] ) {
    default:
    case 1:
      $increment = 60;
      break;
    case 2:
      $increment = 30;
      break;
    case 3:
      $increment = 15;
      break;
  }

  $daily .= SHOW_DAY( $day, $month, $year, $timeremaining );
  
  $daily .= "</table>";

  echo $daily;
}
else { // Booking View ( 'book' )
  if ( $show_form ) {
    $timeleft = TIMELEFT( $_GET['ts'] );
    
    $email = htmlentities( $_SESSION[SITE_ID][USER]['userdata']['email'] );
    $pw    = htmlentities( $_SESSION[SITE_ID][USER]['userdata']['password'] );
    if ( !empty( $_POST['name'] ) )
      $name = htmlentities( $_POST['name'] );
    else
      $name  = htmlentities( $_SESSION[SITE_ID][USER]['userdata']['name'] );
    if ( !empty( $_POST['phone'] ) )
      $phone = htmlentities( $_POST['phone'] );
    else
      $phone = htmlentities( $_SESSION[SITE_ID][USER]['userdata']['phone'] );
     
    $booking .=
      "<form name='form' method='post' action='' id='booking_form'>\n". // onsubmit='alert(document.frames[\"appoint_plugin\"].document.getElementById(\"duration\")); return false;'
      cwd_vaccinate().
      "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n".
      "  <tr class='header'><td><a class='links' name='booknow' href='?mode=daily&ts=$ts'><img src='/themes/".$_SESSION[SITE_ID][USER]['theme']."/images/arrowleft.gif'>Back to Daily View</a></td><td class='links' align='right'>".date( "l, F jS, Y. g:ia", $ts )."</td></tr>\n".
      "  <tr>\n".
      "    <td class='label'><label for='email'>Email:</label></td>\n".
      "    <td><input id='email' name='email' type='text' class='text' value='$email'></td>\n".
      "  </tr>\n".
      ( $_SESSION[SITE_ID][USER]['password_required']
        ?
          "  <tr>\n".
          "    <td class='label'><label for='pw'>Password:</label></td>\n".
          "    <td><input id='pw' name='pw' class='text' type='password' value='$pw'></td>\n".
          "  </tr>\n"
        :
          ""
      ).
      "  <tr>\n".
      "    <td class='label'><label for='name'>Name:</label></td>\n".
      "    <td><input id='name' name='name' type='text' class='text' value='$name'></td>\n".
      "  </tr>\n".
      "  <tr>\n".
      "    <td class='label'><label for='phone'>Phone Number:</label></td>\n".
      "    <td><input id='phone' name='phone' type='text' class='text' value='$phone'></td>\n".
      "  </tr>\n".
      "  <tr>\n";
      
    if ( $timeleft < MAX_APPOINT_LENGTH() )
      $booking .= "    <td class='label'><label for='duration'>Desired Service:</label><BR>* (Choose ". SECONDS_TO_STRING ( $timeleft, 1 ) ." or less in length. If you need a longer service, please <a class='links' href='?mode=daily&ts=$ts'>go back to Daily View</a> and select a different timeslot).</td>\n";
    else
      $booking .= "    <td class='label'><label for='duration'>Desired Service:</label></td>\n";
      
    $booking .=
      "    <td>".DURATION_DROP( 'duration', $timeleft, $type )."</td>\n".
      "  </tr>\n".
      "  <tr>\n".
      "    <td class='label'><label for='notes'>Notes:</label></td>\n".
      "    <td><textarea id='notes'name='notes'>$_POST[notes]</textarea></td>\n".
      "  </tr>\n";
    
    if ( $admin ) {
      $booking .=
        "  <tr>\n".
        "    <td class='label'><label for='private'>Private Notes:</label></td>\n".
        "    <td><textarea id='private' name='private'>$_POST[private]</textarea></td>\n".
        "  </tr>\n".
        "  <tr>\n";
    }
    $booking .=
      "    <td colspan=2><div align='right'>\n".
      "      <input type='submit' name='Submit' id='Submit' class='button' value='Submit'>\n". //disabled='true'
      "    </div></td>\n".
      "  </tr>\n".
      "</table>\n".
      "</form>\n";
  }
  echo $booking;
}
?>
<!--<a href="http://www.carrolltonwebdesign.com">website design and development by:</a>//-->
</center>
</body>
</html>
<?php
///////////////////////////////////////////////////

function APPOINTMENTS_FOR_DATE( $date, $month, $year ) {
  global $pid;
  
  $day = sprintf( "%d-%02d-%02d", $year, $month, $date );
  
  $query =
    "SELECT ".
    "UNIX_TIMESTAMP( `start` ) AS 'time', ".
    "`duration` ".
    "FROM ".
    "`appointments` ".
    "WHERE ".
    "`pid` = ".(int)$pid." ".
    "AND ".
    "DATE_FORMAT( `start`, '%Y-%m-%d' ) = '$day' ".
    "ORDER BY `start` ";
  
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  while ( $APPOINTMENT = mysql_fetch_assoc( $result ) ) {
    $start_time = $APPOINTMENT['time'];
    $end_time = $APPOINTMENT['time'] + $APPOINTMENT['duration'];

    $buf.= date ( 'h:ia', $start_time )." - ".date ( 'h:ia', $end_time )."<BR>";  
  }
  
  return $buf;
}

function DURATION_DROP ( $name, $timeavailable = 0, $type = -1 ) {
  global $pid, $admin;
  
  $javascript = 
    "<script>\n".
    "function handle_change(selector) {\n".
    "  op = selector.selectedIndex;\n";
    
  
  $query =
    "SELECT ".
    "* ".
    "FROM ".
    "`appointment_types` ".
    "WHERE ".
    "`pid` = ".(int)$pid." ".
    ( $admin ? '' : 'AND `restricted` = 0 ' ).
    "ORDER BY ".
    "`position` ";

  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
//  $num_results = mysql_num_rows( $result );
//  $height = min( $num_results, 4 );
//  $buf .= "<select id='name' onchange=\"handle_change(this);\" name='$name' size=$height>\n";
  $buf .= "<select id='$name' onchange=\"handle_change(this);\" name='$name'>\n";
  $buf .= "<option disabled='disabled'>Select Service</option>\n";      ////////////Added to encourage users to move selection from default option

  while ( $APPOINTMENT_TYPE = mysql_fetch_assoc( $result ) ) {
    $duration = $APPOINTMENT_TYPE['duration'];
    $name = $APPOINTMENT_TYPE['name'];
    $tid = $APPOINTMENT_TYPE['id'];

    if ( $duration <= $timeavailable ) {
	  ////$javascript .= "document.getElementById('Submit').disabled=(this.index>0);\n";  // if a valid item is selected, enable the submit button
      if ( $type >= 0 && $tid == $type )
        $buf.="  <option selected value='$duration:$tid'>$name (".SECONDS_TO_STRING( $duration, 1 ).")</option>\n";
      else        
        $buf.="  <option value='$duration:$tid' class='options'>$name (".SECONDS_TO_STRING( $duration, 1 ).")</option>\n";
    }
    else {
	  //$javascript .= "Submit.disabled=true;\n";  // if an invalid item is selected, disable the submit button
      $javascript .= " if ( op == ".(((int)$i)+1)." ) { alert( 'There is not enough time available to book this appointment.\\nPlease go back and choose a different day/time.' );selector.selectedIndex=-1;}\n";//added +1 to correct for adding a new option to the top of the dropdown list
      $buf .= "<option disabled value='$duration:$tid'>$name (*".SECONDS_TO_STRING( $duration, 1 ).")</option>";
    }
    $i++;
  }
  
  $buf .= "</select>\n";
  
  $javascript .=
    "}\n".
    "</script>\n";

  return $javascript.$buf;
}

function SEND_AN_EMAIL( $type, $id ) {
  global $admin, $pid;
  
  $query =
    "SELECT ".
    "`appointments`.`duration`, ".
    "`appointments`.`notes`, ".
    "`appointments`.`private`, ".
    "`appointments`.`name`, ".
    "UNIX_TIMESTAMP( `start` ) 'start_ts', ".
    "DATE_FORMAT( `appointments`.`start`, '%W, %M %D %Y at %l:%i %p' ) AS 'stringtime', ".
    "DATE_FORMAT( `appointments`.`start`, '%m-%d-%y %l:%i%p' ) AS 'time', ".
    "`users`.`email`, ".
    "`users`.`phone`, ".
    "`appointment_types`.`name` AS 'appointment_type' ".
    "FROM ".
    "`appointments` ".
    "LEFT JOIN ".
    "`users` ".
    "ON ".
    "`appointments`.`uid` = `users`.`id` ".
    "LEFT JOIN ".
    "`appointment_types` ".
    "ON ".
    "`appointments`.`type` = `appointment_types`.`id` ".
    "WHERE ".
    "`appointments`.`pid` = ".(int)$pid." ".
    "AND ".
    "`appointments`.`id` = ".(int)$id." ".
    "LIMIT 1 ";
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  if ( !( $APP = mysql_fetch_assoc( $result ) ) )
    return; // DISPLAY_MESSAGEBOX( "Error identifying appointment for notification email.", "Send Notification Email", "error" );
  
  $professional_email = $_SESSION[SITE_ID][USER]['appoint_email'];
  $PATTERNS = array (
    "VAR_NAME",
    "VAR_DATETIME",
    "VAR_STRINGDATE",
    "VAR_TYPE",
    "VAR_DURATION",
    "VAR_NOTES",
    "VAR_PRIVATE",
    "VAR_PROFFESSIONAL_NAME",
    "VAR_PROFFESSIONAL_PHONE",
  );
  
  $hours = JUST_HOURS( $APP['duration'] / 60 );
  $minutes = JUST_MINUTES( $APP['duration'] / 60 );
  
  if ( $hours > 0 ) {
    $duration = sprintf( "%d hours", $hours );
    if ( $minutes )
      $duration .= ", ";
  }
  
  if ( $minutes > 0 )
    $duration .= sprintf( "%d minutes", $minutes );
  
  $REPLACEMENTS = array (
    $APP['name'],
    $APP['time'],
    $APP['stringtime'],
    $APP['appointment_type'],
    $duration,
    ( !empty( $APP['notes'] ) ? "Your Notes:\n".$APP['notes'] : "" ),
    ( !empty( $APP['private'] ) ? "Private Notes:\n".$APP['private'] : "" ),
    $_SESSION[SITE_ID][USER]['name'],
    $_SESSION[SITE_ID][USER]['appoint_phone'],
  );
  
  $subject_user = "Appointment with ".$_SESSION[SITE_ID][USER]['name']." on ".date( 'D, jS M y', $APP['start_ts'] );
  $subject_professional = "Appointment with ".$APP['name']." on ".date( 'D, jS M y', $APP['start_ts'] );
  if ( $type == "book" ) {
    $from_name = "Booking";
    $from = $professional_email;
//    $subject = "appoint-plugin.com/".$_SESSION[SITE_ID][USER]['label']."/ Confirm Booking";
    $cell_subject = $APP['time'].":".$APP['name'].":".$APP['appointment_type'];
    $body =
      "<link href='http://www.appoint-plugin.com/themes/".$_SESSION[SITE_ID][USER]['theme']."/styles/main.css' rel='stylesheet' type='text/css'>\n".
      "<style>\n".
      "table.messagebox table td {\n".
      "  border: 0px;\n".
      "}\n".
      "</style>\n".
      "<center>\n".
      "<table class='messagebox' align='center' bgcolor='#FFFFFF' bordercolor='#000000' border='1' width='350' cellspacing='0' cellpadding='5'>\n".
      "  <tr>\n".
      "    <td>\n".
      "      <font color=000000 size='-1'>  \n".
      "        <table width='350' border='0'>\n".
      "          <tr>\n".
      "            <td align=right valign='middle' class='message_image'>\n".
      "              <img src='http://www.carrolltonwebdesign.com/images/common/icons/okay.gif' align='left' border='0'>\n".
      "            </td>\n".
      "            <td valign='middle' align='left'>\n".
      "              <font color='000000'><b>Appointment Confirmed:</b></font><br>\n".
      "              <font color='000000' size='-1'>\n".
      "                <b><font color='#FF0000'>VAR_STRINGDATE</font></b><br>\n".
      "                For: <font color='#008822'><b>VAR_TYPE (VAR_DURATION)</b></font><br>\n".
      "                Notes:<br>\n".
      "                VAR_NOTES<br>\n".
      "                <a href='http://www.appoint-plugin.com/".$_SESSION[SITE_ID][USER]['label']."/$APP[email]/?ts=$APP[start_ts]&mode=daily'><u>Click here for More Details</u><br>\n".
      "               </a>\n".
      "              </font>\n".
      "            </td>\n".
      "          </tr>\n".
      "        </table>\n".
      "      </font>\n".
      "    </td>\n".
      "  </tr>\n".
      "</table>\n".
      "\n".
      "<table class='messagebox' align='center' bgcolor='#FFFFFF' bordercolor='#000000' border='1' width='350' cellspacing='0' cellpadding='5'>\n".
      "  <tr>\n".
      "    <td><font size='-1'>\n".
      "    Thank you for booking your appointment with us.<br><br>Based on your choice of time, we expect to make your appointment promptly, and will let you know if that changes.  Please do the same for us.<br><br>For questions, please call VAR_PROFFESSIONAL_NAME at VAR_PROFFESSIONAL_PHONE.  Thank you.\n".
      "    </font></td>\n".
      "  </tr>\n".
      "</table>\n".
      "\n".
      calendar_email( $APP['start_ts'] );
      
    $cell_body = "Booked: ".$APP['appointment_type']." at ".$APP['time']." for ".$APP['name']."\n".$APP['phone']."\n\n".$APP['notes'];
  }
  else if ( $type == "cancel" ) {
    $from_name = "Cancellation";
    $from = $professional_email;
//    $subject = "appoint-plugin.com/".$_SESSION[SITE_ID][USER]['label']."/ Cancelled Booking";
    $subject_prefix = 'Cancelled ';
    $cell_subject = $APP['time'].":".$APP['name'].":".$APP['appointment_type'];
    $body =
      DISPLAY_MESSAGEBOX(
      "<span style='font-size:1.2em'>".
      "VAR_NAME,<br>\n".
      "Your VAR_TYPE on VAR_STRINGDATE has been Cancelled.<br>\n".
      "If you have not already rescheduled, you may do so at http://www.Appoint-Plugin.com/".$_SESSION[SITE_ID][USER]['label']."/$APP[email]/<br>\n<br>\n".
      "Should you have any questions, please call VAR_PROFFESSIONAL_NAME at VAR_PROFFESSIONAL_PHONE.<br>\n<br>\n".
      "</span>".
      "www.Appoint-Plugin.com/".$_SESSION[SITE_ID][USER]['label']."/", "Cancelled Appointment", 'error' );
    $cell_body = "Cancelled: ".$APP['appointment_type']." at ".$APP['time']." for ".$APP['name']."\n".$APP['phone']."\n\n".$APP['notes'];
  }
  else
    return;
    
  $body = str_replace( $PATTERNS, $REPLACEMENTS, $body );
  
  $email_to = $APP['email'];
  
  if ( eregi( "^.[a-zA-Z0-9_.\.-]+@[a-z0-9_-]+\\.[a-z]+", $email_to ) ) {
    cwd_mail( $email_to, $subject_prefix.$subject_user, $body, "From: $from_name <$from>\nReply-To: $from\nContent-type:text/html", "=f".$from, true );
  }
  else
    $note = "Email not sent as provided email address appears invalid.<br>\n";

  $body =
    $note.
    $APP['name']."'s email is: ".$APP['email']."<br>\n".
    $APP['name']."'s phone is: ".$APP['phone']."<br>\n<br>\n".
    ( empty( $APP['private'] )
      ? "No Private Notes.<br>\n"
      : "Private Notes:\n".$APP['private']."<br>\n"
    ).
    "<hr>\n".
    $body;
    
  cwd_mail( $_SESSION[SITE_ID][USER]['email'], $subject_prefix.$subject_professional, $body, "From: $from_name <$from>\nReply-To: $from\nContent-type:text/html", "=f".$from, true );
    
  if ( !$admin && !empty( $_SESSION[SITE_ID][USER]['appoint_sms'] ) )
    cwd_mail( $_SESSION[SITE_ID][USER]['appoint_sms'], $cell_subject, $cell_body, "From: $from_name <$from>\r\nReply-To: $from\r\n\r\n", "=f".$from, true );
  
  return true; // DISPLAY_MESSAGEBOX( "Notification email sent to $email_to as confirmation of booking", "Notification Email", "okay" );
}

//if ( isset( $_GET['test'] ) ) {
//  echo PRINTR( users_monthly_appointments( 2006, 2 ) );
//}
?>