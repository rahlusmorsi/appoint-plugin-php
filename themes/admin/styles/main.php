<?php
$stylesheet = true;

$css_background1 = '#FFFFFF';
$css_background2 = '#9999FF';
$css_background3 = '#FF99FF';
$css_background4 = '#99DDFF';
$css_background_weekend = '#EEEEEE';

require_once '../../../admin/functions.php';

?>body {
  font-family: Arial, Verdana, Helvetica, sans-serif;
}

table.layout td {
  border-width: 1px;
  border-style: solid;
  border-color: #777;  
  border-collapse: collapse;
  width: 20px;
  height: 20px;
  font-size: 10px;
  text-align: center;
}

table.layout td.month {
  width: 5px;
  height: 5px;
}

table.layout td.heading {
  width: 1px;
  height: 1px;
  text-align: center;
  vertical-align: bottom;
  border: 0;
}

table.layout td.blank {
  width: 1px;
  height: 1px;
  border: 0;
}

table.layout td.day {
  width: 100px;
  text-align: right;
  border: 0;
}

table.layout td.time {
  height: 15px;
  vertical-align: center;
  border: 0;
}

td.button {
  height: 10px;
  vertical-align: bottom;
  border: 0;
  text-align: right;
}

input.button {
  margin: 2px;
  border: 3px double #999;
  border-top-color: #CCC;
  border-left-color: #CCC;
  padding: 2px;
  color: #333;
  font-size: 10px;
  font-weight: bold;
  font-family: Arial, Verdana, Helvetica, sans-serif;
  cursor: pointer;
  height: 28px;
}

input.button:active, input.button:hover {
  border: 3px double #CCC;
  border-top-color: #999;
  border-left-color: #999;
}

input.button:focus {
  border-top-color: #F00;
  border-right-color: #F00;
  border-bottom-color: #F00;
  border-left-color: #F00;
}

td.cell0 {
  background: <?php echo $css_background1; ?>;
}
td.cell1 {
  background: <?php echo $css_background2; ?>;
}
td.cell2 {
  background: <?php echo $css_background3; ?>;
}
td.cell3 {
  background: <?php echo $css_background4; ?>;
}

.inc_2 table.layout td.time00,
.inc_3 table.layout td.time00 {
  border-left: 1px dashed #777;
}

table.layout tr.day_sat td {
  border-top: 1px solid #000;
  border-bottom: 2px solid #000;
}
table.layout tr.day_sun td {
  border-bottom: 1px solid #000;
  border-top: 2px solid #000;
}
table.layout td.day_sat {
  border-top: 1px dashed #777 !important;
}
table.layout td.day_sun {
  border-bottom: 1px dashed #777 ! important;
}
table.layout td.day_sat, table.layout td.day_sun, table.layout tr.day_sat td.cell0, table.layout tr.day_sun td.cell0 {
  background: <?php echo $css_background_weekend; ?>;
}

table.form {
  text-align: center;
  border: 10px solid #999;
  background: #DDD;
  margin: 20px auto;
  padding: 10px;
}

table.form td {
  border: 0px;
  text-align: left;
}

table.form td.label {
  text-align: right;
}

table.form td.button {
  text-align: right;
}

table.list_appoints {
  font-size: 12px;
  empty-cells: show;
}

table.list_appoints td {
  vertical-align: top;
}
