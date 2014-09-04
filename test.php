<?php
$check = new timeCheck( $_POST );

echo $check->out(); // status
echo $check->form(); // form

class timeCheck {
  var $available;
  var $DATA;
  
  function timeCheck ( $DATA = array() ) {
    $this->DATA = array( 'label' => 'web', 'ts' => time(), 'seconds' => 30 * 60 );
    if ( is_array( $DATA ) ) {
      foreach ( (array)$DATA as $key => $value ) {
        if ( isset( $this->DATA[$key] ) ) {
          if ( $key == 'ts' )
            $value = strtotime( $value );
          $this->DATA[$key] = $value;
        }
      }
    }
      
    $this->available = file_get_contents( "http://www.appoint-plugin.com/".$this->DATA['label']."/?check&ts=".$this->DATA['ts']."&seconds=".$this->DATA['seconds'] );
  }
  
  function form ( ) {
    $form = 
      "<form method='POST'>\n".
      "<table>\n".
      "  <tr><td><label for='label'>Label</label></td><td><input type='text' name='label' id='label' value='".htmlentities( $this->DATA['label'], ENT_QUOTES )."'></td></tr>\n".
      "  <tr><td><label for='ts'>Timestamp</label></td><td><input type='text' name='ts' id='ts' value='".date( 'm/d/Y h:ia', $this->DATA['ts'] )."'></td></tr>\n".
      "  <tr><td><label for='seconds'>Seconds</label></td><td><input type='text' name='seconds' id='seconds' value='".htmlentities( $this->DATA['seconds'], ENT_QUOTES )."'></td></tr>\n".
      "  <tr><td colspan=2 align='right'><input type='submit' value='Check Availibility'></td></tr>\n".
      "</table>\n".
      "</form>\n";
    return $form;
  }
  
  function out ( ) {
    return "<p>".$this->DATA['label']." is ".( $this->available ? '' : 'not ' )." available at ".date( 'm/d/y h:ia', $this->DATA['ts'] )." for ".$this->DATA['seconds']." seconds.</p>\n";
  }
}
?>