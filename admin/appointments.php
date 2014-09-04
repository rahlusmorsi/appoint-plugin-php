<?php
session_start();
require_once '../config.php';
$admin_page = true;
require_once 'functions.php';

if ( !$_SESSION[SITE_ID][PROFESSIONAL]['basic_setup'] ) {
  header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/setup.php" );
  exit;
}
else if ( !$_SESSION[SITE_ID][PROFESSIONAL]['planner_setup'] ) {
  header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/weekly_modifier.php" );
  exit;
}

if ( isset( $_POST['update'] ) ) {
  foreach ( (array)$_POST as $key => $value ) {
    if ( substr( $key, 0, 5 ) == 'name-' ) {
      list( $null, $aid ) = split( '-', $key );
      if ( $aid == 'new' && !empty( $value ) ) { // insert
        $query =
          "SELECT ".
          "`position` ".
          "FROM ".
          "`appointment_types` ".
          "ORDER BY ".
          "`position` DESC ".
          "LIMIT 1 ";
        $result = mysql_query( $query ) or die( MYSQLERROR( $query ) );
        if ( $TYPE = mysql_fetch_row( $result ) )
          $position = $TYPE[0] + 1;
        else
          $position = 1;
          
        $query =
          "INSERT INTO ".
          "`appointment_types` ".
          "( `pid`, `position`, `name`, `duration`, `restricted` ) ".
          "VALUES ".
          "( ".
            (int)$_SESSION[SITE_ID][PROFESSIONAL]['id'].", ".
            "$position, ".
            "'".mysql_escape_string( $value )."', ".
            (int)$_POST['duration-new'].", ".
            ( $_POST['restricted-new'] ? '1' : '0' ).
          ") ";
        
        mysql_query( $query ) or die( MYSQLERROR( $query ) );
      }
      else if ( is_numeric( $aid ) ) { // update / delete
        if ( empty( $value ) ) { // delete
          $query =
            "DELETE FROM ".
            "`appointment_types` ".
            "WHERE ".
            "`id` = ".(int)$aid." ".
            "LIMIT 1 ";
        }
        else {
          $query =
            "UPDATE ".
            "`appointment_types` ".
            "SET ".
            "`name` = '".mysql_escape_string( $value )."', ".
            "`restricted` = '".( $_POST['restricted-'.$aid] ? '1' : '0' )."', ".
            "`duration` = ".(int)$_POST['duration-'.$aid]." ".
            "WHERE ".
            "`id` = ".(int)$aid." ".
            "LIMIT 1 ";
        }
        mysql_query( $query ) or die( MYSQLERROR( $query ) );
      }
    }
  }
}
else if ( (int)$_GET['aid'] > 0 && !empty( $_GET['move'] ) )
  move_entry( 'appointment_types', (int)$_GET['aid'], $_GET['move'] );
?>
<html>
<head>
<title>Professional Setup</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../themes/admin/styles/main.php" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
[ <a href='./'>Main Menu</a> ]<br>
Setup Your Appointment Types:<br>
<form method='POST' action=''>
<table class='form'>
  <tr><th colspan='2'>Position</th><th>Name</th><th>Duration</th><th>Restricted</th></tr>
<?php
$query =
  "SELECT ".
  "* ".
  "FROM ".
  "`appointment_types` ".
  "WHERE ".
  "`pid` = ".(int)$_SESSION[SITE_ID][PROFESSIONAL]['id']." ".
  "ORDER BY ".
  "`position` ";

$result = mysql_query( $query ) or die( MYSQLERROR( $query ) );

while ( $APPOINTMENT_TYPE = mysql_fetch_assoc( $result ) ) {
  $aid = $APPOINTMENT_TYPE['id'];
  
  $buf .=
    "  <tr>\n".
    "    <td><a href='?aid=$aid&move=up'>[UP]</a></td>\n".
    "    <td><a href='?aid=$aid&move=down'>[DOWN]</a></td>\n".
    "    <td><input type='text' name='name-$aid' value='".htmlentities( $APPOINTMENT_TYPE['name'] )."'></td>\n".
    "    <td>".duration_dropdown( $aid, $APPOINTMENT_TYPE['duration'] )."</td>\n".
    "    <td align=center><input type='checkbox' name='restricted-$aid' value=1".( $APPOINTMENT_TYPE['restricted'] ? ' checked' : '' )."></td>\n".
    "  </tr>\n";
}
echo $buf;
?>
  <tr>
    <td colspan=2 class='label'>Add New:</td>
    <td><input type='text' name='name-new'></td>
    <td><?php echo duration_dropdown( 'new' ); ?></td>
    <td><input type='checkbox' name='restricted-new' value=1></td>
  </tr>
  <tr>
    <td colspan='4' align='right'><input type='submit' name='update' value='Update' class='button'></td>
  </tr>
</table>
</form>
</body>
</html>
<?php
function duration_dropdown ( $aid, $current = 60 ) {
  global $DURATIONS;
  
  $buf = "<select name='duration-$aid'>";
  
  foreach ( (array)$DURATIONS as $DURATION ) {
    // disabled: show all durations, not those limited by slot sizes.
    // if ( $DURATION['duration'] == $current || $_SESSION[SITE_ID][PROFESSIONAL]['slot_type'] >= $DURATION['level'] )
      $buf .= "<option value='$DURATION[duration]'".( ( $DURATION['duration'] == $current ) ? ' selected' : '' ).">$DURATION[label]</option>";
  }
  
  $buf .= "</select>";
  
  return $buf;
}
?>