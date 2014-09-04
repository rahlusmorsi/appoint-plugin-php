<?php
require_once '../config.php';
$admin_page = true;
require_once 'functions.php';
include '../themes/default/variables.php';
if ( !$_SESSION[SITE_ID][PROFESSIONAL]['basic_setup'] ) {
  header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/setup.php" );
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
    theValue = 1;
    theCell.style.background = '<?php echo $css_background2; ?>';
  }
  else if ( theValue == 1 ) {
    theValue = 0;
    theCell.style.background = '<?php echo $css_background1; ?>';
  }
  
  theField.value = theValue;
}
//-->
</script>
<link href="../themes/admin/styles/main.php" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php

if ( isset( $_POST['submit'] ) ) {
  $query = "REPLACE INTO `time_templates` ( `slot_id`, `pid`, `day`, `hour`, `status` ) VALUES ";
  $comma = '';
  
  for ( $day = 0; $day < 7; $day++ ) {
    for ( $hour = ( $first_hour_in_day * 4 ); $hour < ( $last_hour_in_day * 4 ); $hour++ ) {
      $status = (int)$_POST[sprintf( 'slot-%01d-%d', $day, floor( $hour / 4 * $multiplier ) )];
      $slot_id = sprintf( '%d-%01d-%d', $pid, $day, $hour );
      $query .= $comma."( '$slot_id', $pid, $day, $hour, $status )";
      $comma = ', ';
    }
  }
  mysql_query( $query ) or die( MYSQLERROR( $query ) );
  
  if ( !$_SESSION[SITE_ID][PROFESSIONAL]['planner_setup'] ) {
    $query =
      "UPDATE ".
      "`professionals` ".
      "SET ".
      "`planner_setup` = 1 ".
      "WHERE ".
      "`id` = ".(int)$_SESSION[SITE_ID][PROFESSIONAL]['id']." ".
      "LIMIT 1 ";
    
    mysql_query( $query ) or die( MYSQLERROR( $query ) );
    
    $_SESSION[SITE_ID][PROFESSIONAL]['planner_setup'] = 1;
  }
}
if ( !$_SESSION[SITE_ID][PROFESSIONAL]['planner_setup'] ) {
  echo
    "[ <a href='setup.php'>Back to Basic Setup</a> ]<br>\n".
    "Even if you wish no changes, please Confirm this setup.<br>\n";
  $submit_text = 'Confirm';
}
else {
?>
[ <a href='./'>Back to Main</a> ]<br>
[ <a href='planner.php?ts=<?php echo $start_of_week; ?>'>Back to Monthly Overview</a> ]<br>
<?php
  $submit_text = 'Update';
}

$SLOTS = get_weekly_template( $pid );
?>
<div class='inc_<?php echo $increment; ?>'>
<table class='layout'>
<?php
$cols = 1;

for ( $day = 0; $day < 7; $day++ ) {
  if ( !$header_complete )
    $header_row = "  <td>\n";
  
  $table_body .= "  <tr class='day_".strtolower( $DAYS[$day] )."'>\n";
  $table_body .= "    <td class='day day_".strtolower( $DAYS[$day] )."'>".$DAYS[$day]."</td>\n";
  for ( $hour = ( $first_hour_in_day * $multiplier ); $hour < ( $last_hour_in_day * $multiplier ); $hour++ ) {
    if ( !$header_complete ) {
      $cols++;
      $header_row .= "    <td class='time time".date( 'i', mktime( ( $hour / $multiplier ), ( $hour % $multiplier ) * $devisor, 0, 1, 1, 2000 ) )."'>".vert_time( date( 'g:ia', mktime( ( $hour / $multiplier ), ( $hour % $multiplier ) * $devisor, 0, 1, 1, 2000 ) ) )."</td>\n";
    }
      
    $value = slot_value( $SLOTS, $day, $hour, $multiplier );
    $name = sprintf( '%01d-%01d', $day % 7, $hour );
    
    $table_body .= "    <td class='cell".(int)$value."' onClick=\"value_cycle(this,'slot-$name')\"$cellsize><img src='../images/admin/blank.gif'><input type='hidden' id='slot-$name' name='slot-$name' value=".(int)$value."></td>\n";
  }
  $table_body .= " </tr>\n";
  if ( !$header_complete ) {
    $header_row .= "  </td>\n";
    $header_complete = true;
  }
}

echo
  $header_row.
  "<form method='post'>\n".
  "<input type='hidden' name='ts' value='$start_of_week'>".
  "<input type='hidden' name='i' value='$increment'>".
  $table_body;
  
$span = floor( $cols / 5 );
?>
  <tr><td class='day'>Key:</td><td colspan=<?php echo $span; ?> class='cell0'>Always<br>Available</td><td colspan=<?php echo $span; ?> class='cell1'>Never<br>Available</td><td colspan='<?php( $cols - ( $span * 2 + 1 ) )?>' class='blank'><input class='button' type='submit' value='<?php echo $submit_text; ?>' name='submit'></td></tr>
</table>
</div>
</form>
</body>
</html>