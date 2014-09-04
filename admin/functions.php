<?php
// get from template settings

$DAYS = array ( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
$THEMES = array( 'default', 'cwebdes' );
$SLOT_TYPES = array(
              1 => 'Hour',
              2 => 'Half Hour',
              3 => 'Quarter Hour',
              );

if ( $admin_page ) {
  define( 'SITE_ID', 'app-plug' );
  if ( !VERIFY_PROFESSIONAL( true ) ) {
?>
<html>
<head>
<title>Professional Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../themes/admin/styles/main.php" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form method='POST' action='./'>
  <table class='form'>
    <tr>
      <th>&nbsp;</th>
      <th align='left'>Please Log In:</th>
    </tr>
    <tr>
      <td class='label'><label for='un'>Username:</label></td>
      <td><input type='text' name='<?php echo SITE_ID; ?>-un' id='un' value='<?php echo htmlentities( $_POST[SITE_ID.'-un'] ); ?>'></td>
    </tr>
    <tr>
      <td class='label'><label for='pw'> Password:</label></td>
      <td><input type='password' name='<?php echo SITE_ID; ?>-pw' id='pw'></td>
    </tr>
    <tr>
      <td colspan='2' align='right'><input type='submit' name='<?php echo SITE_ID; ?>-login' value='Log In' class='button'></td>
    </tr>
  </table>
</form>
</body>
</html>
<?php
    exit;
  }
  
  // get professional info
  $pid               = $_SESSION[SITE_ID][PROFESSIONAL]['id'];
  $first_hour_in_day = $_SESSION[SITE_ID][PROFESSIONAL]['day_start']; // 6;
  $last_hour_in_day  = $_SESSION[SITE_ID][PROFESSIONAL]['day_end']; // 20;
  $increment         = $_SESSION[SITE_ID][PROFESSIONAL]['slot_type']; // 2
  
  if ( isset( $_POST['ts'] ) )
    $timestamp = $_POST['ts'];
  else if ( isset( $_GET['ts'] ) )
    $timestamp = $_GET['ts'];
  else
    $timestamp = time();
  
  $year  = date( 'Y', $timestamp );
  $month = date( 'n', $timestamp );
  $date  = date( 'j', $timestamp );
  $weekday = date( 'w', $timestamp );
  
  // reset to start of week
  $start_of_week = mktime( 0, 0, 0, $month, $date - $weekday, $year );
  $end_of_week = mktime( 0, 0, 0, $month, ( $date - $weekday + 7 ), $year );
  $start_of_month = mktime( 0, 0, 0, $month, 1, $year );
  
  list( $multiplier, $devisor ) = get_mul_dev( $increment );
}

function get_mul_dev ( $increment ) {
  switch ( $increment ) {
    case 1:
    case 'hour':
      $devisor = 60;
      $multiplier = 1;
      break;
    case 2:
    default:
    case 'half':
      $devisor = 30;
      $multiplier = 2;
      break;
    case 3:
    case 'quarter':
      $devisor = 15;
      $multiplier = 4;
      break;
  }
  return array( $multiplier, $devisor );
}

function get_weekly_template ( $pid ) {
  // get underlying template
  $query =
    "SELECT ".
    "* ".
    "FROM ".
    "`time_templates` ".
    "WHERE ".
    "`pid` = ".(int)$pid." ";
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  while ( $SLOT = mysql_fetch_assoc( $result ) )
    $SLOTS[$SLOT['day']][$SLOT['hour']] = $SLOT['status'];
  
  return $SLOTS;
}

function get_day_template ( $pid, $day ) {
  // get underlying template
  $query =
    "SELECT ".
    "* ".
    "FROM ".
    "`time_templates` ".
    "WHERE ".
    "`pid` = ".(int)$pid." ";
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  while ( $SLOT = mysql_fetch_assoc( $result ) ) {
    if ( $SLOT['day'] == $day )
      $SLOTS[$SLOT['day']][$SLOT['hour']] = $SLOT['status'];
  }
  
  return $SLOTS;
}

function slot_value( $SLOTS, $day, $hour, $multiplier ) {
  switch ( $multiplier ) {
    default:
    case 4:
      return $SLOTS[$day][$hour];
    case 2:
      $first = $hour * 2;
      return $SLOTS[$day][$first] | $SLOTS[$day][$first + 1];
    case 1:
      $first = $hour * 4;
      return $SLOTS[$day][$first] | $SLOTS[$day][$first + 1] | $SLOTS[$day][$first + 2] | $SLOTS[$day][$first + 3];
  }
}

function get_type_by_id ( $tid ) {
  $query =
    "SELECT ".
    "* ".
    "FROM ".
    "`appointment_types` ".
    "WHERE ".
    "`pid` = ".(int)$_SESSION[SITE_ID][USER]['id']." ".
    "AND ".
    "`id` = ".(int)$tid." ".
    "LIMIT 1 ";
  
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  return mysql_fetch_assoc( $result );
}

function MAX_APPOINT_LENGTH ( ) {
  global $admin;
  $query =
    "SELECT ".
    "`duration` ".
    "FROM ".
    "`appointment_types` ".
    "WHERE ".
    "`pid` = ".(int)$_SESSION[SITE_ID][USER]['id']." ".
    ( $admin ? '' : 'AND `restricted` = 0 ' ).
    "ORDER BY ".
    "`duration` DESC ".
    "LIMIT 1 ";
  
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  $DURATION = mysql_fetch_row( $result );
  return $DURATION[0];
}

function MIN_APPOINT_LENGTH ( ) {
  $query =
    "SELECT ".
    "`duration` ".
    "FROM ".
    "`appointment_types` ".
    "WHERE ".
    "`pid` = ".(int)$_SESSION[SITE_ID][USER]['id']." ".
    "ORDER BY ".
    "`duration` ".
    "LIMIT 1 ";
  
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  $DURATION = mysql_fetch_row( $result );
  return $DURATION[0];
}

function vert_time ( $time ) {
  if ( empty( $time ) )
    return '';
  
  list( $hour, $end ) = split( ':', $time );
  $minute = substr( $end, 0, 2 );
  $am_pm = substr( $end, 2 );
  
  return "<img src='../images/admin/timesvert/$am_pm.gif'><br><img src='../images/admin/timesvert/$minute.gif'><br><img src='../images/admin/timesvert/$hour.gif'>";
}

function move_entry( $table, $move_id, $dir ) {
  $query =
    "SELECT ".
    "`id`, ".
    "`position` ".
    "FROM ".
    "`$table` ".
    "WHERE ".
    "`id` = '$move_id' ".
    "LIMIT 1";
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  if ( $ROW_CAT = mysql_fetch_assoc( $result ) ) {
    $order_query =
      "SELECT ".
      "`id`, ".
      "`position` ".
      "FROM ".
      "`$table` ".
      "ORDER BY ".
      "`position` ";
      
    $order_result = mysql_query( $order_query ) or die( MYSQLERROR( $order_query ) );
    $position = 1;
    
    while ( $ORDERED = mysql_fetch_assoc( $order_result ) ) {
      if ( $ORDERED['id'] == $move_id ) {
        if ( $dir == "up" ) {
          $query =
            "UPDATE ".
            "`$table` ".
            "SET ".
            "`position` = '$position' ".
            "WHERE ".
            "`id` = '".$ORDERED['id']."' ".
            "LIMIT 1 ";
          $position++;
          mysql_query ( $query ) or die( MYSQLERROR( $query ) );
          
          if ( isset( $LAST ) ) {  
            $query =
              "UPDATE ".
              "`$table` ".
              "SET ".
              "`position` = '$position' ".
              "WHERE ".
              "`id` = '".$LAST['id']."' ".
              "LIMIT 1 ";
            $position++;
            mysql_query ( $query ) or die( MYSQLERROR( $query ) );
            unset( $LAST );
          }
        }
        else { // down
          $NEXT = $ORDERED;
        }
      }
      else if ( $dir == "up" ) {
        if ( isset( $LAST ) ) {
          $query =
            "UPDATE ".
            "`$table` ".
            "SET ".
            "`position` = '$position' ".
            "WHERE ".
            "`id` = '".$LAST['id']."' ".
            "LIMIT 1 ";
          $position++;
          mysql_query ( $query ) or die( MYSQLERROR( $query ) );
        }            
        $LAST = $ORDERED;
      }
      else { // down
        $query =
          "UPDATE ".
          "`$table` ".
          "SET ".
          "`position` = '$position' ".
          "WHERE ".
          "`id` = '".$ORDERED['id']."' ".
          "LIMIT 1 ";
        $position++;
        mysql_query ( $query ) or die( MYSQLERROR( $query ) );
        if ( isset( $NEXT ) ) { // did we set the faq we are modifying to next on the previous cycle?
          $query =
            "UPDATE ".
            "`$table` ".
            "SET ".
            "`position` = '$position' ".
            "WHERE ".
            "`id` = '".$NEXT['id']."' ".
            "LIMIT 1 ";
          $position++;
          mysql_query ( $query ) or die( MYSQLERROR( $query ) );
          unset( $NEXT ); // it's been handled so we're done with it.
        }            
      }
    }
    if ( $dir == "up" && isset( $LAST ) ) {
      $LEFTOVER = $LAST;
    }
    else if ( $dir == "down" && isset( $NEXT ) ) {
      $LEFTOVER = $NEXT;
    }
    
    if ( isset( $LEFTOVER ) ) {
      $query =
        "UPDATE ".
        "`$table` ".
        "SET ".
        "`position` = '$position' ".
        "WHERE ".
        "`id` = '".$LEFTOVER['id']."' ".
        "LIMIT 1 ";
      mysql_query ( $query ) or die( MYSQLERROR( $query ) );
    }
  }
}

function VERIFY_PROFESSIONAL ( $redirect = false ) {
  if ( isset( $_REQUEST[SITE_ID.'-plogout'] ) ) { // log professional out
    setcookie( SITE_ID.'-pid', '', time() - 3600, '/' );
    
    $_SESSION[SITE_ID][PROFESSIONAL] = array();
    unset( $_SESSION[SITE_ID][PROFESSIONAL] );
    
    $_REQUEST[SITE_ID.'-login'] = $_POST[SITE_ID.'-login'] = $_COOKIE[SITE_ID.'-pid'] = '';
    
    return false;
    if ( $redirect ) {
      header("Location: http://".$_SERVER['HTTP_HOST'].dirname( $_SERVER['PHP_SELF'] )."/../" );
      exit;
    }
  }
  else if ( isset( $_REQUEST['pid'] ) ) // get an encoded string for login
    $encode_string = $_REQUEST['pid'];
  else if ( isset( $_POST[SITE_ID.'-login'] ) ) // get standard login info
    $encode_string = MD5( $_POST[SITE_ID.'-un'].$_POST[SITE_ID.'-pw'] );
  else if ( isset( $_SESSION[SITE_ID][PROFESSIONAL]['id'] ) ) // professional already logged in
    return true;
  else if ( isset( $_COOKIE[SITE_ID.'-pid'] ) ) // get an encoded string for login
    $encode_string = $_COOKIE[SITE_ID.'-pid'];
    
  if ( isset( $encode_string ) ) {
    $query =
      "SELECT ".
      "*, ".
      "MD5( CONCAT( `un`, `pw` ) ) 'pid' ".
      "FROM ".
      "`professionals` ".
      "WHERE ".
      "MD5( CONCAT( `un`, `pw` ) ) = '".mysql_escape_string( $encode_string )."' ".
      "LIMIT 1 ";
    
    $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
    
    if ( $PROFESSIONAL = mysql_fetch_assoc( $result ) ) {
      $_SESSION[SITE_ID][PROFESSIONAL] = $PROFESSIONAL;
      setcookie( SITE_ID.'-pid', $encode_string, EXPIRE_TIME, '/' );
      return true;
    }
  }
  return false;
}

function VALID_EMAIL( $email ) {
  return preg_match( "/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $email );
}

function VERIFY_USER ( ) {
  global $login_string, $logout_string, $general_messages;
  
  $pid = $_SESSION[SITE_ID][USER]['id'];
  
  if ( isset( $_REQUEST['email'] ) ) {
    $email = $_REQUEST['email'];
    
    if ( VALID_EMAIL( $email ) && $email != $_SESSION[SITE_ID][USER]['login_string'] ) {
      $pw = $_POST['pw'];

      $query =
        "SELECT ".
        "`id`, `password` ".
        "FROM ".
        "`users` ".
        "WHERE ".
        "`email` = '".mysql_escape_string( $email )."' ".
        "AND ".
        "`pid` = ".(int)$pid." ".
        "LIMIT 1 ";
      
      $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
      
      if ( $USER = mysql_fetch_assoc( $result ) ) {
        if ( !$_SESSION[SITE_ID][USER]['password_required'] || $pw == $USER['password'] ) {
          
          if ( !empty( $_REQUEST['phone'] ) ) {
            $UFIELDS[] = 'phone';
            $UVALUES[] = $_REQUEST['phone'];
          }
          
          if ( !empty( $_REQUEST['name'] ) ) {
            $UFIELDS[] = 'name';
            $UVALUES[] = $_REQUEST['name'];
          }
          
          if ( count( $UFIELDS ) )
            UPDATE_USER( $USER['id'], $UFIELDS, $UVALUES );
            
          if ( !LOGIN_USER( $USER['id'] ) ) {
            $general_messages .= DISPLAY_MESSAGEBOX( "Error Creating User.", "Login", "error" );
          }
        }
        else
          $general_messages .= DISPLAY_MESSAGEBOX( "Password is incorrect.", "Login", "error" );
      }
      else {
        if ( $_SESSION[SITE_ID][USER]['password_required'] && empty( $pw ) )
          $global_messages .= DISPLAY_MESSAGEBOX( '', 'Please specify a password.', 'error' );
        else {
          $query =
            "INSERT INTO ".
            "`users` ".
            "( ".
              "`pid`, ".
              "`email`, ".
              ( $_SESSION[SITE_ID][USER]['password_required']
                ? "`password`, "
                : ''
              ).
              "`name`, ".
              "`phone` ".
            ") ".
            "VALUES ".
            "( ".
              (int)$pid.", ".
              "'".mysql_escape_string( $email )."', ".
              ( $_SESSION[SITE_ID][USER]['password_required']
                ? "'".mysql_escape_string( $pw )."', "
                : ''
              ).
              "'".mysql_escape_string( $_REQUEST['name'] )."', ". // may be empty, but may be set, doesnt hurt to set.
              "'".mysql_escape_string( $_REQUEST['phone'] )."' ". // may be empty, but may be set, doesnt hurt to set.
            ") ";
          mysql_query( $query ) or die( MYSQLERROR( $query ) );
          
          if ( !LOGIN_USER( mysql_insert_id() ) )
            $global_messages .= DISPLAY_MESSAGEBOX( 'Error Creating User', 'Create User.', 'error' );
        }
      }
    }
    else {
      $general_messages .= DISPLAY_MESSAGEBOX( "Please enter a Valid Email Address. This email is used to confirm any bookings and to help you track your appointments.", "Enter Email", "error" );
      $login_string = "<form method='POST' name='login' action='?'>Email:<input onFocus=\"this.value=''\" type='text' name='email' value='".$_SESSION[SITE_ID][USER]['login_string']."' class='login_text'>".( $_SESSION[SITE_ID][USER]['password_required'] ? " Password:<input type='password' name='pw' class='login_text password'>" : "" )."<input type='submit' name='LogIn' value='>Login' class='login_button'></form>";
    }
  }
  else if ( isset( $_SESSION[SITE_ID][USER]['userdata'] ) ) {
    $login_string = 'Logged in as '.( empty( $_SESSION[SITE_ID][USER]['userdata']['name'] ) ? $_SESSION[SITE_ID][USER]['userdata']['email'] : $_SESSION[SITE_ID][USER]['userdata']['name'] ).".";
    $logout_string = "[<a href='/".$_SESSION[SITE_ID][USER]['label']."/?logout'>Log Out</a>]";
  }
  else if ( !empty( $_COOKIE[SITE_ID.$_SESSION[SITE_ID][USER]['id']] ) ) {
    $query =
      "SELECT ".
      "`id` ".
      "FROM ".
      "`users` ".
      "WHERE ".
      "MD5( CONCAT( `email`, `password` ) ) = '".mysql_escape_string( $_COOKIE[SITE_ID.$_SESSION[SITE_ID][USER]['id']] )."' ".
      "AND ".
      "`pid` = ".(int)$pid." ".
      "LIMIT 1 ";
    
    $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
    
    if ( $COOKIE_USER = mysql_fetch_assoc( $result ) ) {
      LOGIN_USER( $COOKIE_USER['id'] );
    }
    else {
      $login_string = "<form method='POST' name='login' action='?'>Email:<input onFocus=\"this.value=''\" type='text' name='email' value='".$_SESSION[SITE_ID][USER]['login_string']."' class='login_text'>".( $_SESSION[SITE_ID][USER]['password_required'] ? " Password:<input type='password' name='pw' class='login_text password'>" : "" )."<input type='submit' name='LogIn' value='>Login' class='login_button'></form>";
      $logout_string = '';
    }
  }
  else {
    $login_string = "<form method='POST' name='login' action='?'>Email:<input onFocus=\"this.value=''\" type='text' name='email' value='".$_SESSION[SITE_ID][USER]['login_string']."' class='login_text'>".( $_SESSION[SITE_ID][USER]['password_required'] ? " Password:<input type='password' name='pw' class='login_text password'>" : "" )."<input type='submit' name='LogIn' value='>Login' class='login_button'></form>";
    $logout_string = '';
  }
}

function UPDATE_USER ( $id, $FIELDS, $VALUES ) {
  if ( $id && ( $num_fields = count( $FIELDS ) ) ) {
    $query =
      "UPDATE `users` ".
      "SET ";
      
    for ( $i = 0; $i < $num_fields; $i++ ) {
      $query .= $comma."`$FIELDS[$i]` = '".mysql_escape_string( $VALUES[$i] )."'";
      $comma = ', ';
    }
    
    $query .= " WHERE `id` = ".(int)$id." LIMIT 1 ";
    
    mysql_query( $query ) or die( MYSQLERROR( $query ) );
  }
}

function get_user_by_id ( $uid ) {
  $query =
    "SELECT ".
    "* ".
    "FROM ".
    "`users` ".
    "WHERE ".
    "`id` = ".(int)$uid." ".
    "LIMIT 1 ";
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  return mysql_fetch_assoc( $result );
}

function LOGIN_USER( $uid ) {
  global $login_string, $logout_string;
  
  if ( $USER = get_user_by_id( $uid ) ) {
    setcookie( SITE_ID.$_SESSION[SITE_ID][USER]['id'], md5( $USER['email'].$USER['pw'] ), time() + 157680000, "/" );
    $_SESSION[SITE_ID][USER]['userdata'] = $USER;
    $login_string = 'Logged in as '.( empty( $USER['name'] ) ? $USER['email'] : $USER['name'] ).".";
    $logout_string = "[<a href='/".$_SESSION[SITE_ID][USER]['label']."/?logout'>Log Out</a>]";
    return true;
  }
  else
    return false;
}

function LOGOUT_USER( $silent = false ) {
  global $login_string, $logout_string, $general_messages;
  
  if ( isset( $_GET['logout'] ) ) {
    unset( $_SESSION[SITE_ID][USER]['userdata'] );
    setcookie( SITE_ID.$_SESSION[SITE_ID][USER]['id'], '', time() - 3600, "/" ); 
    $login_string = "<form method='POST' name='login' action='?'>Email:<input onFocus=\"this.value=''\" type='text' name='email' value='".$_SESSION[SITE_ID][USER]['login_string']."' class='login_text'>".( $_SESSION[SITE_ID][USER]['password_required'] ? " Password:<input type='password' name='pw' class='login_text password'>" : "" )."<input type='submit' name='LogIn' value='>Login' class='login_button'></form>";
    $logout_string = '';
    if ( !$silent && 1 == 2 )
      $general_messages .= DISPLAY_MESSAGEBOX( '', 'Logged out OK.', 'okay' );
    return true;
  }
  
  return false;
}

function SHOW_DAY( $day, $month, $year, $timeremaining ) {
  global $java_highlight, $default_background, $admin;
  
  $pid               = $_SESSION[SITE_ID][USER]['id'];
  $first_hour_in_day = $_SESSION[SITE_ID][USER]['day_start']; // 6;
  $last_hour_in_day  = $_SESSION[SITE_ID][USER]['day_end']; // 20;
  $increment         = $_SESSION[SITE_ID][USER]['slot_type']; // 2
  list( $multiplier, $devisor ) = get_mul_dev( $increment );

  $SLOTS = get_day_template( $pid, date( 'w', mktime( 0, 0, 0, $month, $day, $year ) ) );
  $min_appoint_length = MIN_APPOINT_LENGTH();
  
  // get overlaying values
  $day_timestamp = mktime( 0, 0, 0, $month, $day, $year );
  
  $query =
    "SELECT ".
    "*, ".
    "UNIX_TIMESTAMP( `start` ) AS 'start', ".
    "EXTRACT( HOUR FROM `start` ) AS `slot_hour`, ".
    "EXTRACT( MINUTE FROM `start` ) AS `slot_minute` ".
    "FROM ".
    "`appointments` ".
    "WHERE ".
    "`pid` = ".(int)$pid." ".
    "AND ".
    "EXTRACT( YEAR FROM `start` ) = ".date( 'Y', $day_timestamp )." ".
    "AND ".
    "EXTRACT( MONTH FROM `start` ) = ".date( 'm', $day_timestamp )." ".
    "AND ".
    "EXTRACT( DAY FROM `start` ) = ".date( 'd', $day_timestamp )." ".
    "ORDER BY `uid` "; // added order so that for the next section the marked free/busy should come first, thus get overwritten by the actual appointments if any
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  while ( $APP = mysql_fetch_assoc( $result ) ) {
    $hour = $APP['slot_hour'] * 4 + ( $APP['slot_minute'] ? $APP['slot_minute'] / 15 : 0 );
    if ( $APP['uid'] == -1 )
      $MODS[$day][$hour] = -1;
    else if ( $APP['uid'] == 0 )
      $MODS[$day][$hour] = 1;
    else
      $APPS[$day][$hour] = $APP;
  }
  
  for ( $hour = ( $first_hour_in_day * $multiplier ); $hour < ( $last_hour_in_day * $multiplier ); $hour++ ) {
    $day_timestamp = mktime( floor( $hour / $multiplier ), ( ( $hour % $multiplier ) * $devisor ), 0, $month, $day, $year );
    $LAYOUT[$hour]['timestamp'] = $day_timestamp;
    $template = slot_value( $SLOTS, date( 'w', $day_timestamp ), $hour, $multiplier );
    $mod = slot_value( $MODS, $day, $hour, $multiplier );
    
    if ( $template ) { // always unavailable
      if ( $mod == 1 ) { // (db value of 0) // marked free
        $LAYOUT[$hour]['unavailable'] = 0; // one time available
        $LAYOUT[$hour]['admin_note'] = "Marked Available";
      }
      else { //  if ( $mod == -1 )
        $LAYOUT[$hour]['unavailable'] = 1; // never available
        $LAYOUT[$hour]['admin_note'] = "Never Available";
      }
    }
    else { // always free
      if ( $mod == -1 ) {// marked unavailable
        $LAYOUT[$hour]['unavailable'] = 1; // one time unavailable
        $LAYOUT[$hour]['admin_note'] = "Marked Unavailable";
      }
      else { // if ( $mod == 1 )
        $LAYOUT[$hour]['unavailable'] = 0; // always available
        $LAYOUT[$hour]['admin_note'] = "Always Available";
      }
    }

    switch ( $multiplier ) {
      default:
      case 4:
        if ( $APPS[$day][$hour] )
          $LAYOUT[$hour]['appointments'][] = $APPS[$day][$hour];
        break;
      case 2:
        $first = $hour * 2;
        if ( $APPS[$day][$first] )
          $LAYOUT[$hour]['appointments'][] = $APPS[$day][$first];
        if ( $APPS[$day][$first + 1] )
          $LAYOUT[$hour]['appointments'][] = $APPS[$day][$first + 1];
        break;
      case 1:
        $first = $hour * 4;
        if ( $APPS[$day][$first] )
          $LAYOUT[$hour]['appointments'][] = $APPS[$day][$first];
        if ( $APPS[$day][$first + 1] )
          $LAYOUT[$hour]['appointments'][] = $APPS[$day][$first + 1];
        if ( $APPS[$day][$first + 2] )
          $LAYOUT[$hour]['appointments'][] = $APPS[$day][$first + 2];
        if ( $APPS[$day][$first + 3] )
          $LAYOUT[$hour]['appointments'][] = $APPS[$day][$first + 3];
        break;
    }
  }
  for ( $hour = ( $first_hour_in_day * $multiplier ); $hour < ( $last_hour_in_day * $multiplier ); $hour++ ) {
    $duration_start = $LAYOUT[$hour]['timestamp'];
    $duration_end = $duration_start;
    $unavailable = false;
    $align = 'left';

    // start row. show time cell
    $buf .=
      "  <tr".( ( (int)( time() / ( $devisor * 60 ) ) == (int)( $duration_start / ( $devisor * 60 ) ) ) ? " class='now'" : '' ).">\n".
      "    <td class='time'>".date( 'g:ia', $LAYOUT[$hour]['timestamp'] )."</td>\n";
    
    if ( --$therowspan > 0 ) {
      $buf .= "  </tr>\n";
      continue;
    }
      
    $rowspan = 0;
    $urowspan = 0;
    $booking_buf = '';
    
    foreach ( (array)$LAYOUT[$hour]['appointments'] as $APP ) {
      $duration = $APP['start'] - $LAYOUT[$hour]['timestamp'] + $APP['duration'];
      $blocks = ceil( $duration * $multiplier / 60 / 60 ); // blocks
      $APP['string_date'] = date( 'H:ia', $APP['start'] ).' - '.date( 'H:ia', $APP['start'] + $APP['duration'] );
        
      if ( $APP['uid'] == $_SESSION[SITE_ID][USER]['userdata']['id'] || $admin ) { // user's own appointment, or admin
        $booking_buf .= show_appointment( $APP ); // show the appointment
        $last_unavailable = false;

        if ( $rowspan < $blocks )
          $rowspan = $blocks;
      }
      
      $unavailable = true;
      $cell_java = '';
 
      while ( $blocks-- > 0 ) // mark off the blocks
        $LAYOUT[$hour + $blocks]['unavailable'] = true; // mark it as true so if there are no appointments to show, the general viewer gets the right cell class
    }
    
    
    if ( !$rowspan && $urowspan-- <= 1 ) {
      $urowspan = 0;

      if ( $LAYOUT[$hour]['unavailable'] ) {
        $cell_java = '';
        $unavailable = true;
        $uhour = $hour;
        
        if ( !$last_unavailable ) {
          $booking_buf = "<div class='unavailable'>Unavailable</div>\n"; // unavailable
          $last_unavailable = true;
        }
        else
          $booking_buf = '&nbsp;';
        
        $found_appointment = false; // initialise the new var for breakout;
        
        while ( $LAYOUT[$uhour]['unavailable'] && !$found_appointment ) { // add in found check for breakout
          if ( $admin ) {
            $booking_buf .= $LAYOUT[$uhour]['admin_note']."<br>\n";
            $last_unavailable = false;
          }
          $uhour++; // need to increment first, else we recheck the appointment(s) we just showed.
          
          foreach ( (array)$LAYOUT[$uhour]['appointments'] as $APP )
            if ( $APP['uid'] == $_SESSION[SITE_ID][USER]['userdata']['id'] || $admin ) // user's own appointment, or admin
              $found_appointment = true;
          
          if ( !$found_appointment )
            $urowspan++;
        }
      }
      else {
        if ( $LAYOUT[$hour]['timestamp'] < time() ) {
          $link = '';
          $cell_java = '';
          $booking_buf = "&nbsp;\n"; // in the past
          $unavailable = true;
        }
        else if ( TIMELEFT( $LAYOUT[$hour]['timestamp'] ) < $min_appoint_length ) {
          $link = '';
          $cell_java = '';
          if ( $last_unavailable ) {
            $booking_buf = '&nbsp;';
            $unavailable = true;
          }
          else {
            $booking_buf = "<div class='unavailable'>Timeslot too short</div>\n"; // unavailable
            $last_unavailable = false; // prolly can leave it as true, but no big.
          }
        }
        else {
          // debug timeleft:
//          $timeleft = TIMELEFT( $LAYOUT[$hour]['timestamp'] );
//          $time_left_string = sprintf( '%01d:%02d', $timeleft / ( 60 * 60 ), ( $timeleft / 60 ) % 60 );
          $link = "?mode=book&ts=".$LAYOUT[$hour]['timestamp'];
          $cell_java = " onMouseover=\"this.style.background='$java_highlight'\" onMouseout=\"this.style.background='$default_background'\" onClick=\"window.location='$link'\"";
          $booking_buf = "      <a href='$link#booknow'>Click to reserve time.</a>\n";
          $last_unavailable = false;
          $align = 'center';
        }
      }
    }
    
    $therowspan = $rowspan + $urowspan;
    
    $buf .=
      "    <td align='$align'".$cell_java.( ( $therowspan > 1 ) ? " rowspan='$therowspan'" : '' )." valign='top' class='".( $unavailable ? 'unavailable' : 'times' )."'>\n".
      $booking_buf.
      "    </td>\n".
      "  </tr>\n";
  }
  
  return $buf;
}

function show_appointment( $APP, $admin ) {
  $USER = get_user_by_id( $APP['uid'] );
  $TYPE = get_type_by_id( $APP['type'] );
  
  $buf =
    "      <div class='appointment hollyhack'>\n".
    "        <span class='name'>$APP[name]</span> <span class='phone'>$USER[phone]</span><br>\n".
    "        <a href='mailto:{$USER[email]}'><span class='email'>$USER[email]</span></a><br>\n".
    "        <span class='type'>For: ".( empty( $TYPE['name'] ) ? 'unknown' : $TYPE['name'] )." (".HOURS_MINUTES( $APP['duration'] / 60 ).")</span><br>\n".
    ( empty( $APP['notes'] ) ? ''
    : "        <span class='notes'>$APP[notes]</span><br>\n" ).
    ( ( $admin && !empty( $APP['private'] ) ) ? "<span class='pnotes'>$APP[private]</span><br>\n" : "" ).
    "        <a class='cancel' href='?cancel=$APP[id]' onClick='return confirm(\"Cancel {$APP[name]} \\n- are you sure?\");'>Cancel</a>\n".
    "        <div class='clear'></div>\n".
    "      </div>\n";
  
  return $buf;
}

function HOURS_MINUTES ( $rawminutes ) {
  $minutes = $rawminutes % 60;
  $hours = $rawminutes / 60;
  $hours = (int)$hours;
  
  return ( $hours ? $hours.'h' : '' ).( ( $hours && $minutes ) ? ' ' : '' ).( $minutes ? sprintf( '%02dm', $minutes ) : '' );
}

function JUST_HOURS ( $rawminutes ) {
  $hours = $rawminutes / 60;
  $hours = (int)$hours;

  return $hours;
}

function JUST_MINUTES ( $rawminutes ) {
  $minutes = $rawminutes % 60;
  
  if ( $minutes < 10 )
    $minutes = "0".$minutes;  
  
  return $minutes;
}

function TIMELEFT ( $ts ) {
  $debug .= "TIMELEFT(".date( 'Gi', $ts )."){ ";
  $end_of_day_stamp = mktime( $_SESSION[SITE_ID][USER]['day_end'], 0, 0, date( 'm', $ts ), date( 'd', $ts ), date( 'Y', $ts ) );
  
  $next_time = $end_of_day_stamp;
  $debug .= "End of day cutoff applied: ".date( 'Gi', $end_of_day_stamp ).".";
  
  $query =
    "SELECT ".
    "UNIX_TIMESTAMP( `start` ) AS 'time' ".
    "FROM ".
    "`appointments` ".
    "WHERE ".
    "`pid` = ".(int)$_SESSION[SITE_ID][USER]['id']." ".
    "AND ".
    "`uid` > 0 ".
    "AND ".
    "UNIX_TIMESTAMP( `start` ) + `duration` > ".(int)$ts." ".
    "AND ".
    "EXTRACT( YEAR FROM `start` ) = ".date( 'Y', $ts )." ".
    "AND ".
    "EXTRACT( MONTH FROM `start` ) = ".date( 'm', $ts )." ".
    "AND ".
    "EXTRACT( DAY FROM `start` ) = ".date( 'd', $ts )." ".
    "ORDER BY `start` ".
    "LIMIT 1";
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );

  if ( $NEXTAPPOINTMENT = mysql_fetch_assoc( $result ) ) {
    $next_time = $NEXTAPPOINTMENT['time'];
    $debug .= "app: ".date( 'Gi', $next_time ).".";
  }
  else
    $debug .= 'no app today.';
  
  $query =
    "SELECT ".
    "`time_templates`.*, ".
    "`appointments`.*, ".
    "DATE_FORMAT( `appointments`.`start`, '%w' ) AS 'app_to_day', ".
    "ROUND( EXTRACT( HOUR FROM `start` ) * 4 + ROUND( EXTRACT( MINUTE FROM `start` ) / 15 ) ) AS 'app_to_hour', ".
    "( `time_templates`.`hour` * 15 ) as 'minutes' ".
    "FROM ".
    "`time_templates` ".
    "LEFT JOIN ".
    "`appointments` ".
    "ON ".
    "( ".
      "DATE_FORMAT( `appointments`.`start`, '%Y%m%d' ) = '".date( 'Ymd', $ts )."' ".
      "AND ".
      "`time_templates`.`day` = DATE_FORMAT( `appointments`.`start`, '%w' ) ".
      "AND ".
      "`time_templates`.`hour` = ROUND( EXTRACT( HOUR FROM `start` ) * 4 + ROUND( EXTRACT( MINUTE FROM `start` ) / 15 ) )".
    ") ".
    "WHERE ".
    "`time_templates`.`pid` = ".(int)$_SESSION[SITE_ID][USER]['id']." ".
    "AND ".
    "`appointments`.`pid` = ".(int)$_SESSION[SITE_ID][USER]['id']." ".
    "AND ". // we've already checked 'regular' appointments, so lets filter them out here.
    "( ".
      "`appointments`.`uid` < 1 ". // 0 or -1 are our markers
      "OR ".
      "`appointments`.`uid` IS NULL ". // no modifiers
    ") ".
    "AND ".
    "`time_templates`.`day` = ".(int)date( 'w', $ts )." ".
    "AND ".
    "`time_templates`.`hour` >= ".(int)( date( 'G', $ts ) * 4 + ( date( 'i', $ts ) / 15 ) )." ".
    "AND ".
    "( ".
      "( ". // check for time template marked available and modified to one time unavailable
        "`time_templates`.`status` = 0 ". // marked available
        "AND ".
        "`appointments`.`uid` = -1 ". // non 0 means marked unavailable - actually looking for -1 here as app already checked above
      ") ".
      "OR ".
      "( ". // check for time template marked unavailable, and not overwritten to available
        "`time_templates`.`status` = 1 ". // marked unavailable
        "AND ".
        "( ".
          "`appointments`.`uid` IS NULL ". // no appointment - so not overwritten
          "OR ".
          "`appointments`.`uid` != 0 ". // ( != ) marked available
        ") ".
      ") ".
    ") ".
    "ORDER BY ".
    "`time_templates`.`hour` ".
    "LIMIT 1 ";
  
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );

  if ( $OVERLAY = MYSQL_FETCH_ASSOC( $result ) ) {
    $overlay_next_time = mktime( 0, $OVERLAY['minutes'], 0, date( 'm', $ts ), date( 'd', $ts ), date( 'Y', $ts ) );

    $debug .= "Overlay: ".date( 'Gi', $overlay_next_time ).".";

    if ( $overlay_next_time < $next_time ) {
      $next_time = $overlay_next_time;
      $debug .= "(earlier)";
    }
    else
     $debug .= "(later)";
  }
  else $debug .= "No Overlay today.";

  $debug .= " Result for ".date( 'Gi', $next_time ).": ".($next_time - $ts)."<br>\n";

  return $next_time - $ts;
}

function users_monthly_appointments ( $year, $month ) {
  $ym = sprintf( "%d%02d", $year, $month );
  
  $query =
    "SELECT ".
    "`id`, `uid`, ".
    "`duration`, ".
    "UNIX_TIMESTAMP( `start` ) 'start_ts' ".
    "FROM ".
    "`appointments` ".
    "WHERE ".
    "`pid` = ".(int)$_SESSION[SITE_ID][USER]['id']." ".
    "AND ".
    "`uid` = ".intval( $_SESSION[SITE_ID][USER]['userdata']['id'] )." ".
    "AND ".
    // appointments for this month
    "DATE_FORMAT( `start`, '%Y%m' ) = '$ym' ";
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  while ( $BOOKING = mysql_fetch_assoc( $result ) ) {
    $day = date( 'j', $BOOKING['start_ts'] );
    $hour = date( 'g', $BOOKING['start_ts'] );
    $minute = date( 'i', $BOOKING['start_ts'] );
    
    $USER_APPOINTMENTS[$year][$month][$day] = true;
  }

  return $USER_APPOINTMENTS;
}

function get_monthly_time_available ( $year, $month ) {
  $WEEKLY_TEMPLATE = get_weekly_template( $_SESSION[SITE_ID][USER]['id'] );
  // [day][hour-slot]

  $query =
    "SELECT ".
    "`uid`, `duration`, ".
    "UNIX_TIMESTAMP( `start` ) 'start_ts', ".
    "DATE_FORMAT( `start`, '%H' ) * 4 + ROUND( DATE_FORMAT( `start`, '%H' ) / 15 ) 'hour_slot'".
    "FROM ".
    "`appointments` ".
    "WHERE ".
    "`pid` = ".(int)$_SESSION[SITE_ID][USER]['id']." ".
    "AND ".
    "DATE_FORMAT( `start`, '%Y%m' ) = '".sprintf( '%4d%02d', $year, $month )."' ".
    "AND DATE_FORMAT( `start`, '%k' ) > ".intval( $_SESSION[SITE_ID][USER]['day_start'] )." ".
    "AND DATE_FORMAT( `start`, '%k' ) < ".intval( $_SESSION[SITE_ID][USER]['day_end'] )." ".
//    "AND ". // we've already checked 'regular' appointments, so lets filter them out here.
//    "`appointments`.`uid` < 1 ". // 0 or -1 are our markers
    "ORDER BY ".
    "`start` ";
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  while ( $aOVERLAY = mysql_fetch_assoc( $result ) ) {
    $day = date( 'j', $aOVERLAY['start_ts'] );
    if ( $aOVERLAY['uid'] > 0 ) {
      $OVERLAY[$year][$month][$day][$aOVERLAY['hour_slot']] = 1;
      $duration = $aOVERLAY['duration'];
      $pos = 0;
      while ( ( $duration -= ( 15 * 60 ) ) > 0 ) {
        $pos++;
        $OVERLAY[$year][$month][$day][$aOVERLAY['hour_slot'] + $pos] = 1;
      }
    }
    else
      $OVERLAY[$year][$month][$day][$aOVERLAY['hour_slot']] = $aOVERLAY['uid'];
  }
  
  $min_appoint_length = MIN_APPOINT_LENGTH();
  for ( $day = 1; $day <= 31; $day++ ) {
    $weekday = date( 'w', mktime( 0, 0, 0, $month, $day, $year ) );
    $available_time = 0;
    for ( $hour_slot = $_SESSION[SITE_ID][USER]['day_start'] * 4; $hour_slot < $_SESSION[SITE_ID][USER]['day_end'] * 4; $hour_slot++ ) {
      
      $base = $WEEKLY_TEMPLATE[$weekday][$hour_slot];
      
      if ( $OVERLAY[$year][$month][$day][$hour_slot] > 0
        || !$base && $OVERLAY[$year][$month][$day][$hour_slot] == -1 // available overwritten to unavailable
          || $base && ( !isset( $OVERLAY[$year][$month][$day][$hour_slot] ) || $OVERLAY[$year][$month][$day][$hour_slot] != -1 ) ) {
            
         if ( $available_time >= $min_appoint_length )
           $MONTHLY_TIME_AVAILABLE[$year][$month][$day] += $available_time;
         $available_time = 0; // close off a block of time
      }
      else {
        $available_time += ( 15 * 60 );
      }
    }
    $MONTHLY_TIME_AVAILABLE[$year][$month][$day] += $available_time;
  }
  // we now have $MONTHLY_TIME_AVAILABLE filled available time per day in seconds

  return $MONTHLY_TIME_AVAILABLE;
}

function get_professional_by_id ( $pid ) {
  $query =
    'SELECT '.
    '* '.
    'FROM '.
    '`professionals` '.
    'WHERE '.
    '`id` = '.(int)$pid.' '.
    'LIMIT 1 ';
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  return mysql_fetch_assoc( $result );
}

function get_professional_by_label ( $label ) {
  if ( empty( $label ) )
    return;
    
  $query =
    'SELECT '.
    '* '.
    'FROM '.
    '`professionals` '.
    'WHERE '.
    "`label` = '".mysql_escape_string( $label )."' ".
    'LIMIT 1 ';
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  return mysql_fetch_assoc( $result );
}

function HOW_BOOKED ( $year, $month, $date ) {
  global $USER_APPOINTMENTS, $MONTHLY_TIME_AVAILABLE;

  if ( mktime( 0, 0, 0, $month, $date, $year ) < time() )
    return 0;
    
  if ( $_SESSION[SITE_ID][USER]['userdata']['id'] ) {
    if ( !isset( $USER_APPOINTMENTS ) )
      $USER_APPOINTMENTS = users_monthly_appointments( $year, $month );

      if ( $USER_APPOINTMENTS[$year][$month][$date] )
        return 6; // 6 shows the appointment image
  }
  if ( !$MONTHLY_TIME_AVAILABLE[$year][$month] )
    $MONTHLY_TIME_AVAILABLE = get_monthly_time_available( $year, $month );
  $first_hour_in_day = $_SESSION[SITE_ID][USER]['day_start'];
  $last_hour_in_day  = $_SESSION[SITE_ID][USER]['day_end'];
  
  return intval( min( 5, ceil( ( 4 * $MONTHLY_TIME_AVAILABLE[$year][$month][$date] ) / ( ( $last_hour_in_day - $first_hour_in_day ) * 60 * 60 ) ) ) );
}

function calendar_email( $ts ) {
  global $css_background1, $css_background2, $css_background3, $css_background4;
  $year = date( 'Y', $ts );
  $month = date( 'm', $ts );
  $day = date( 'j', $ts );
  
  $first_of_month = 1 + date( "w", mktime( 0, 0, 0, $month, 1, $year ) ); 
  $daysinmonth = date( "j", mktime( 0, 0, 0, $month + 1, 0, $year ) );
  $dispmonth = date("F",mktime( 0, 0, 0, $month, 1, $year ) );
  $email_calendar .=
    "<table class='tableborder' cellpadding=0 cellspacing=0>".
    "  <tr class='header'>\n".
    "    <td colspan=7 align=center><B class='monthyear'>$dispmonth $year</b></td>\n".
    "  </tr>\n".
    "  <tr><td class='weekday' align='center'><B>Sun</b></td><td class='weekday' align='center'><B>Mon</b></td><td class='weekday' align='center'><B>Tue</b></td><td class='weekday' align='center'><B>Wed</b></td><td class='weekday' align='center'><B>Thu</b></td><td class='weekday' align='center'><B>Fri</b></td><td class='weekday' align='center'><B>Sat</b></td></tr>\n";
  
  $email_calendar .= "  <tr>\n";
  $extradaysneeded = $first_of_month - 1;
  
  for ( $d = 1; $d <= $extradaysneeded; $d++ )
    $email_calendar .= "<td class='weekday' height=50 width=50 valign='top'>&nbsp;</td>";
  
  for ( $date = 1; $date <= $daysinmonth; $date++ ) {
    $howbooked = HOW_BOOKED( $year, $month, $date );
    $dts = mktime( 0, 0, 0, $month, $date, $year );
    $email_calendar .= "<td class='day$howbooked' height=50 width=50 valign='top' bgcolor='".( ( $date == $day ) ? "$css_background1' style='border:2px solid ".$css_background2 : "$css_background2' style='border:2px solid ".$css_background4 )."'>&nbsp;$date<BR></td>"; 
    
    if ( $first_of_month == 7 ) {
       $email_calendar .= "</tr>\n  <tr>";
       $first_of_month=0;
    }
    $first_of_month++;
  }  
  
  while ( $first_of_month++ <= 7 )
    $email_calendar .= "  <td style='border: 0;'></td>\n";
    
  $email_calendar .=
    "</table>\n";
    
  return $email_calendar;
}
?>