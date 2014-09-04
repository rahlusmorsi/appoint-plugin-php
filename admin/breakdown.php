<?php
require_once 'common-functions.php';
echo PRINTR( $_SERVER );
$uri = $_SERVER['SCRIPT_URI'].'/more.stuff?blah';
echo "Script URI: ".$uri."<br>";
preg_match( "/^(http:\/\/)?([^\/]+)(?:\/([^\/]*)\/)?(.*)?/i", $uri, $MATCHES );
$domain = $MATCHES[2];
$dir = $MATCHES[3];
$rest = $MATCHES[4];

$DOMAIN = split( "\.", $domain );

if ( $DOMAIN[0] != 'www' && $DOMAIN[0] != SITE_DOMAIN ) {
  echo "label: $DOMAIN[0]<br>\n";
}
else {
  echo "no subdomain to check<br>";
}
  
echo "Dir: $dir<br>\n";

  
list( $user, $trest ) = split( '\/', $rest, 2 );
echo "User: $user<br>rest: $trest<br>";
?>