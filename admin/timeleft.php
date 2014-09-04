<?php
// get from template settings
require_once '../config.php';
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../themes/admin/styles/main.php" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form method='get'>
<textarea name='ts' rows=5 cols=40><?php echo $_GET['ts']; ?></textarea><br>
<input type='submit' name='submit' value='Analyse'>
</form>
<?php
$ts = $_GET['ts'];

$query =
  'SELECT '.
  '* '.
  'FROM '.
  '`professionals` '.
  'WHERE '.
  "`id` = 1 ".
  'LIMIT 1 ';
  
$result = mysql_query( $query ) or die( MYSQLERROR( $query ) );

$_SESSION[SITE_ID][USER] = mysql_fetch_assoc( $result );

echo "Professional:<br>\n".PRINTR( $_SESSION[SITE_ID][USER] );

echo "<h1>Time Available at $ts ( ".date( "h:ia \o\\n l, F jS, Y", $ts )." )</h1>\n";
$day_start = mktime( $_SESSION[SITE_ID][USER]['day_start'], 0, 0, date( 'm', $ts ), date( 'd', $ts ), date( 'Y', $ts ) );
$day_end = mktime( $_SESSION[SITE_ID][USER]['day_end'], 0, 0, date( 'm', $ts ), date( 'd', $ts ), date( 'Y', $ts ) );

echo "Day Start: ".date( "h:ia \o\\n l, F jS, Y", $day_start )."<br>\n";
  
if ( $ts < $day_start ) {
  $ts = $day_start;
  echo "Changing timestamp to start of day ($ts : ".date( "h:ia \o\\n l, F jS, Y", $ts ).")<br>\n";
}

echo "End of day: ".date( 'h:ia', $day_end )."<br>\n";

$next_time = $day_end;

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

echo "Querying for appointments:<br>\n$query<br>\n";

if ( $NEXTAPPOINTMENT = mysql_fetch_assoc( $result ) ) {
  $next_time = $NEXTAPPOINTMENT['time'];
  echo "Found a next appointment at ".date( 'h:ia', $next_time ).".<br>\n";
}
else
  echo "no app today.<br>\n";

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

echo "querying for mask overlay for this day:<br>\n$query<br>\n";

if ( $OVERLAY = MYSQL_FETCH_ASSOC( $result ) ) {
  $overlay_next_time = mktime( 0, $OVERLAY['minutes'], 0, date( 'm', $ts ), date( 'd', $ts ), date( 'Y', $ts ) );

  echo "Overlay time found: ".date( 'h:ia', $overlay_next_time ).".<br>\n";

  if ( $overlay_next_time < $next_time ) {
    $next_time = $overlay_next_time;
    echo "Overlay time is earlier, setting to new 'next cutoff'.<br>\n";
  }
  else
   echo "Overlay time is later - this shouldn't happen should it?<br>\n";
}
else
  echo "No Overlay today.<br>\n";

$available = $next_time - $ts;
echo
  "Final cutoff time: ".date( 'h:ia', $next_time )."<br>\n".
  "Available time will be: $available<br>\n".
  "Which is ".sprintf( '%01d:%02d', $available / ( 60 * 60 ), ( $available / 60 ) % 60 )."<br>\n";
?>
</body>
</html>