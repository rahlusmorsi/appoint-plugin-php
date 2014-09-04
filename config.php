<?php
session_start();

define( 'MYSQL_HOST', 'localhost' );
define( 'MYSQL_DB', 'appoint_plugin_com' );


define( 'MYSQL_USER', '[your-mysql-user-here]' );
define( 'MYSQL_PASS', '[your-mysql-pass-here]' );


$mysqlconnection_handle = mysql_connect( MYSQL_HOST, MYSQL_USER, MYSQL_PASS ) or die( mysql_error( ) );
mysql_select_db( MYSQL_DB ) or die( mysql_error( ) );
require_once 'common-functions.php';

define( 'SITE_DOMAIN', 'appoint-plugin' );
define( 'PROFESSIONAL', 'professional' );
define( 'USER', 'user' );
define( 'EXPIRE_TIME', time()+60*60*24*30 ); // 30 days

$DURATIONS = array (
  array(
    'level' => 3,
    'duration' => 60 * 15,
    'label' => '15m',
  ),
  array( 
    'level' => 2,
    'duration' => 60 * 30,
    'label' => '30m',
  ),
  array( 
    'level' => 3,
    'duration' => 60 * 45,
    'label' => '45m',
  ),
  array( 
    'level' => 1,
    'duration' => 60 * 60,
    'label' => '1h',
  ),
  array( 
    'level' => 3,
    'duration' => 60 * 75,
    'label' => '1h 15m',
  ),
  array( 
    'level' => 2,
    'duration' => 60 * 90,
    'label' => '1h 30m',
  ),
  array( 
    'level' => 3,
    'duration' => 60 * 105,
    'label' => '1h 45m',
  ),
  array( 
    'level' => 1,
    'duration' => 60 * 120,
    'label' => '2h',
  ),
  array( 
    'level' => 2,
    'duration' => 60 * 150,
    'label' => '2h 30m',
  ),
  array( 
    'level' => 1,
    'duration' => 60 * 60 * 3,
    'label' => '3h',
  ),
  array( 
    'level' => 1,
    'duration' => 60 * 60 * 4,
    'label' => '4h',
  ),
  array( 
    'level' => 1,
    'duration' => 60 * 60 * 5,
    'label' => '5h',
  ),
  array( 
    'level' => 1,
    'duration' => 60 * 60 * 6,
    'label' => '6h',
  ),
  array( 
    'level' => 1,
    'duration' => 60 * 60 * 9,
    'label' => '9h',
  ),
  array( 
    'level' => 1,
    'duration' => 60 * 60 * 12,
    'label' => '12h',
  ),
);
?>