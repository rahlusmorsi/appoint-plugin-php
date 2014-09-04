<?php
session_start();
require_once '../config.php';
$admin_page = true;
require_once 'functions.php';
include '../themes/default/variables.php';

if ( !$_SESSION[SITE_ID][PROFESSIONAL]['basic_setup'] ) {
  header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/setup.php" );
  exit;
}
else if ( !$_SESSION[SITE_ID][PROFESSIONAL]['planner_setup'] ) {
  header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/weekly_modifier.php" );
  exit;
}
?>
<html>
<head>
<title>Page Title</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function value_cycle( theCell, theID ) {
  var theField = document.getElementById( theID );
  var theValue = theField.value;
  if ( theValue == 0 ) {
    theValue = 2;
    theCell.style.background = '<?php echo $css_background3; ?>';
  }
  else if ( theValue == 1 ) {
    theValue = 3;
    theCell.style.background = '<?php echo $css_background4; ?>';
  }
  else if ( theValue == 2 ) {
    theValue = 0;
    theCell.style.background = '<?php echo $css_background1; ?>';
  }
  else if ( theValue == 3 ) {
    theValue = 1;
    theCell.style.background = '<?php echo $css_background2; ?>';
  }
  
  theField.value = theValue;
}
//-->
</script>
<link href="../themes/admin/styles/main.php" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
[ <a href='./'>Back to Main</a> ]<br>
<div class='inc_<?php echo $increment; ?>'>
<table class='layout'>
<?php
$cols = 1;
$SLOTS = get_weekly_template( $pid );

for ( $day = 1; $day <= date( 't', $start_of_month ); $day++ ) {
  // get overlaying values
  $day_timestamp = mktime( 0, 0, 0, $month, $day, $year );
  $ymd_format = date( 'Ymd', $day_timestamp );
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
    "DATE_FORMAT( `start`, '%Y%m%d' ) = '$ymd_format' ";
    
  $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  unset( $MODS );
  
  while ( $APP = mysql_fetch_assoc( $result ) ) {
    $slot_hour = $APP['slot_hour'] * 4 + ( $APP['slot_minute'] ? $APP['slot_minute'] / 15 : 0 );
    if ( $APP['uid'] == -1 ) {
      $MODS[$day][$slot_hour] = -1;
    }
    else if ( $APP['uid'] == 0 ) {
      $MODS[$day][$slot_hour] = 1;
    }
    else {
      /*
      $day_timestamp = mktime( floor( $APP['slot_hour'] / $multiplier ), ( ( $APP['slot_hour'] % $multiplier ) * $devisor ), 0, $month, $day, $year );
      $duration = $APP['start'] - $day_timestamp + $APP['duration'];
      $blocks = ceil( $duration * $multiplier / 60 / 60 ); // blocks
      */
      $blocks = $APP['duration'] / 60 / $devisor;
      do {
        $blocks--;
        $APPS[$day][$slot_hour + $blocks] = 1;
      } while ( $blocks > 0 );
      $LIST_APPS[$APP['start']][] = $APP;
    }
  }
  
  if ( !$header_complete )
    $header_row = "  <tr>\n".
      "    <td class='month blank'>&nbsp;</td>\n";
    
  $table_body .=
    "  <tr onClick=\"window.location='weekly_planner.php?ts=$day_timestamp'\" class='day_".strtolower( date( 'D', $day_timestamp ) )."'>\n".
    "    <td class='month day day_".strtolower( date( 'D', $day_timestamp ) )."'>".date( 'D, jS', $day_timestamp )."</td>\n";
  for ( $hour = ( $first_hour_in_day * $multiplier ); $hour < ( $last_hour_in_day * $multiplier ); $hour++ ) {
    if ( !$header_complete ) {
      $cols++;
      $header_row .= "    <td class='month time time".date( 'i', mktime( ( $hour / $multiplier ), ( $hour % $multiplier ) * $devisor, 0, 1, 1, 2000 ) )."'>".vert_time( date( 'g:ia', mktime( ( $hour / $multiplier ), ( $hour % $multiplier ) * $devisor, 0, 1, 1, 2000 ) ) )."</td>\n";
    }

    $template = slot_value( $SLOTS, ( $day + date( 'w', mktime( 0, 0, 0, $month, 0, $year ) ) ) % 7, $hour, $multiplier );
    $mod = slot_value( $MODS, $day, $hour, $multiplier );
    $app = slot_value( $APPS, $day, $hour, $multiplier );

    if ( $template ) { // always unavailable
      if ( $mod == 1 ) // (db value of 0) // marked free
        $value = 3; // one time available
      else //  if ( $mod == -1 )
        $value = 1; // never available
    }
    else { // always free
      if ( $mod == -1 ) // marked unavailable
        $value = 2; // one time unavailable
      else // if ( $mod == 1 )
        $value = 0; // always available
    }
    
    $table_body .= "    <td class='month cell".(int)$value."'>".( $app ? 'A' : "<img src='../images/admin/blank.gif'>" )."</td>\n";
  }
  $table_body .= "  </tr>\n";
  if ( !$header_complete ) {
    $header_row .= "  </tr>\n";
    $header_complete = true;
  }
}
echo
  "  <tr><td class='blank'>&nbsp;</td><td colspan='".( $cols - 1 )."' class='heading'><a href='?ts=".( mktime( 0, 0, 0, $month - 1, 1, $year ) )."'>&lt;&lt;  </a>".date( 'F, Y', $start_of_month )."<a href='?ts=".( mktime( 0, 0, 0, $month + 1, 1, $year ) )."'>  &gt;&gt;</a></td></tr>\n".
//  "  <tr><td class='blank'>&nbsp;</td><td colspan='".( $cols - 1 )."' class='heading'>".increment_select()."</td></tr>\n".
  "<form method='post' action='?ts=$start_of_month'>\n".
  "<input type='hidden' name='ts' value='$start_of_month'>".
  "<input type='hidden' name='i' value='$increment'>".
  $header_row.
  $table_body;
  
$span = floor( $cols / 5 );
?>
  <tr><td colspan='<?php echo $cols; ?>' class='time'>&nbsp;</td></tr>
  <tr><td class='day'>Key:</td><td colspan=<?php echo $span; ?> class='cell0'>Always<br>Available</td><td colspan=<?php echo $span; ?> class='cell1'>Never<br>Available</td><td colspan=<?php echo $span; ?> class='cell3'>One Time<br>Available</td><td colspan=<?php echo $span; ?> class='cell2'>One Time<br>Unavailable</td><td colspan='<?php( $cols - ( $span * 4 + 1 ) )?>' class='blank'><input type=button class='button' value='Edit Weekly Template' onClick="window.location='weekly_modifier.php?ts=<?php echo $start_of_month; ?>'"></td></tr>
</table>
</div>
</form>
<br>
<table border=1 cellpadding=1 cellspacing=0 class='list_appoints'>
  <tr><th>Time</th><th>Click to Administer</th><th>Phone</th><th>For</th></tr>
<?php
$query =
  "SELECT ".
  "* ".
  "FROM ".
  "`appointment_types` ".
  "WHERE ".
  "`pid` = ".(int)$_SESSION[SITE_ID][PROFESSIONAL]['id']." ";

$result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
while ( $TYPE = mysql_fetch_assoc( $result ) )
  $TYPES[$TYPE['id']] = $TYPE;
  
foreach ( (array)$LIST_APPS as $ts => $APPS ) {
  foreach ( (array)$APPS as $APP ) {
    $USER = get_user_by_id( $APP['uid'] );
    $TYPE = $TYPES[$APP['type']];    
    echo "  <tr><td><nobr>".date( 'F jS h:ia', $ts )."</nobr></td><td><nobr><a href='../".$_SESSION[SITE_ID][PROFESSIONAL]['label']."/?pid=".$_SESSION[SITE_ID][PROFESSIONAL]['pid']."&ts=$ts&mode=daily' target='_BLANK'>$APP[name]</a></nobr></td><td class='phone'><nobr>$USER[phone]</nobr></td><td>".( empty( $TYPE['name'] ) ? 'unknown' : $TYPE['name'] )." (".HOURS_MINUTES( $APP['duration'] / 60 ).")</td></tr>\n";
  }
}
?>
</table>
<br>
</body>
</html>