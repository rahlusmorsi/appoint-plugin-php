<html>
<head>
<title>Appoint-Plugin.com - Screenshots</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="./main.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="780" height="700" border="0" align="center" cellpadding="0" cellspacing="0" id="main">
	<tr>
		<td><table width="760" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="5"><a href="./index.html"><img src="./images/nav_01.jpg" width="780" height="90" border="0" usemap="#Map"></a></td>
  </tr>
  <tr>
    <td><a href="./benefits.html"><img src="./images/nav_02.jpg" width="111" height="42" border="0"></a></td>
    <td><a href="./how.html"><img src="./images/nav_03.jpg" width="132" height="42" border="0"></a></td>
    <td><a href="./pricing.html"><img src="./images/nav_04.jpg" width="90" height="42" border="0"></a></td>
    <td><a name="NULL100"><img src="./images/2_05.jpg" width="137" height="42" border="0"></a></td>
    <td><img src="./images/nav_06.jpg" width="310" height="42"></td>
  </tr>
</table>
</td>
	</tr>
	<tr>
		<td valign="top">
		<table width="621" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td id="headers" height="39" colspan="3" valign="top" background="./images/main_05.jpg">Screenshots</td>
          </tr>
          <tr>
            <td width="21" valign="top" background="./images/main_07.jpg"> <img src="./images/main_07.jpg" width="21" height="207" alt=""></td>
            <td width="582" valign="top" bgcolor="#FFFFFF" id="content">
<?php
/* ************************************** */
/*  1.     Database Configuration         */
/* ************************************** */

// This gallery uses MYSQL.  If site already has mysql setup, use that config file otherwise you can use 'common' mysql database (remember it's shared among multiple sites and applications).
// require_once 'common-mysql.php'; //// never again - Steve 7/31/2012
?>

<?php
/* ************************************** */
/*  2.       Basic Configuration          */
/* ************************************** */
$folder = './images/screenshot-gallery/'; // keep trailing slash - determines gallery to track images for.  NOTE:  On the server, this folder must be 'group writable' or this doesn't work!
$upload_link = 'ftp://appoint:bookit941@server01.metroplexwebdesign.com/public_html/images/screenshot-gallery/'; // for user to put new pictures into gallery
$admin_un = 'appoint';
$admin_pw = '';  //password can be blank for quick username-only style login

$width = 500; // maximum width for wide images (aspect ratio is maintained)
$height = 800;  // maximum height for tall images (aspect ratio is maintained)
$thumb = 25; // percentage of above dimensions to use to create thumbnails

$cols = 4; // thumbnails per row in gallery display
$rows = 2; // rows of thumbnails per page for gallery display
?>


<?php
/* ************************************** */
/*  3.       Advanced Options             */
/* ************************************** */
$enlarge_small_images = false;
$process_delay = 3; // when importing, delay in seconds between pictures
$watermark = 'www. Appoint - Plugin .com';
$gallery_table = 'cwd_gallery';
$gallery_images_table = 'cwd_gallery_images';
$image_prefix = 'z'; 
$thumb_prefix = 'z'; 
$thumb_suffix = '_thumb'; 
$watermark_colour = '#FFFFFF';
$watermark_shadow = '#000000';
//$watermark_font = 'fonts/verdana.ttf'; // path from where this is called to font
$watermark_fontsize = 9;
$default_caption = <<<DESCRIPTION
Screenshot of the easy-to-use www.Appoint-Plugin.com system.<br>
&copy; MetroPlex Web Design, Inc.
DESCRIPTION;
?>


<?php
/* ************************************** */
/*  4.       The Actual Gallery!          */
/* ************************************** */
// this piece actually makes the gallery draw in
// the page whereever you put this.
include 'gallery-module.php';
?>






            </td>
            <td width="18" valign="top" background="./images/main_09.jpg"> <img src="./images/main_09.jpg" width="18" height="207" alt=""></td>
          </tr>
          <tr>
            <td colspan="3" valign="top"> <img src="./images/main_10.jpg" width="621" height="30" alt=""></td>
          </tr>
      </table></td>
	</tr>
	<tr>
		<td>
			<img src="./images/main_03.jpg" width="780" height="36" alt=""></td>
	</tr>
	<tr>
		<td>
			<img src="./images/banner-screens.jpg" width="780" height="206" alt=""></td>
	</tr>
	<tr>
      <td class="footer" height="52" valign="top" background="./images/main_11.jpg">
        <div align="center"> <a href="./index.html">HOME</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="./benefits.html">BENEFITS</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="./how.html">HOW
            IT WORKS</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="./pricing.html">PRICING</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a name="NULL100">SCREENSHOTS</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="./contact.html">CUSTOMER
            SERVICE</a></div>
      </td>
  </tr>
	<tr>
		<td class="copy"><div align="right"> Copyright &copy; 2006 Metroplex Web Design, Inc.</div></td>
	</tr>
</table>
<div id="cwebdes" align="center">- <a href="http://www.carrolltonwebdesign.com" target="_blank">Dallas Web Design</a> by -</div>
<map name="Map">
<area shape="rect" coords="541,2,629,20" href="./index.html">
<area shape="rect" coords="635,2,760,20" href="./contact.html">
<area shape="rect" coords="5,15,486,89" href="./index.html">
</map>
</body>
</html>
