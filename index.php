<?php

$SITE_BASE = "http://www.trelby.org/paste";
$INST_SUBDIR = "/paste";
$PASTE_PATH = 'content/';
$PASTEID_LEN = 8;

function headerstuff() {
?>

<html>
<head>
<title>Trelby Pastebin</title>
<link media="screen" type="text/css" href="http://www.trelby.org/wp-content/themes/pink-touch-2/style.css" rel="stylesheet">
<link id="arvo-css" media="all" type="text/css" href="http://fonts.googleapis.com/css?family=Arvo%3A400%2C700&ver=3.2.1" rel="stylesheet">

<style type="text/css">
textarea, pre {font: 12px/14px Courier, "Courier New", monospace !important;}
textarea {border: 1px solid lightblue; padding: 5px; }
body {background:none; color: #000000;}
h1 {font-size: 1.5em; margin: 10px;}
h2 {font-size: 1.2em; margin: 10px;}
pre {text-align: left !important; letter-spacing: 0 !important; margin-top: 0px !important; margin-bottom: 0px !important;padding:0 !important;}
.title, .footer {margin: 15px;}
.sc {font-weight: bold !important;}
.nt {color: blue; font-style: italic !important;}
.spcenter {
    background-color: #f5f5f5;
    border-left: 1px solid #CCCCCC;
    border-right: 1px solid #CCCCCC;
    margin: 20px auto;
    padding: 20px;
    width: 500px;
}
.spcenter pre{background-color: #f5f5f5;}
</style>
</head>
<body>

<div id="navigation">
 <div class="wrapper clearfix">
  <div id="header">
   <h1><img width="40" height="40" style="padding:0 5px 0px 5px;" src="http://www.trelby.org/wp-content/uploads/icon64.png"><a rel="home" title="Trelby Pastebin" href="http://www.trelby.org/">Trelby</a></h1>
  </div><!-- /#header -->
  <div id="nav-menu" class="menu-home-container">
   <ul id="menu-home" class="menu">
<?php
    global $SITE_BASE;
    echo "<li><a href='$SITE_BASE'>Your friendly neighbourhood screenplay pastebin.</a></li>";
?>
   </ul></div>
  </div>
 </div>
<div id="navigation-frill"></div>

<?php
} // headerstuff

function footerstuff() {
?>
  </body></html>
<?php
}
function showmain() {
?>
<div id="content">
 <h1>Easy screenplay viewing</h1>
 <p>You can post here either directly from inside <a href="http://www.trelby.org">Trelby</a><br>or by pasting formatted screenplay text below.</p>
 <br>
 <form action="new/" method="POST" >
  <textarea name="script" cols="70" rows="20">Paste here!</textarea>
  <br><br>
  <input type="submit" value="Create Paste">
 </form>
</div>

<?php
}

//returns a random new paste ID
function randomid() {
    global $PASTE_PATH, $PASTEID_LEN;
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $foundid = FALSE;
    while ($foundid == FALSE){
        $string = "";
        for ($i = 0; $i < $PASTEID_LEN; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        if (file_exists($PASTE_PATH.$string) == FALSE)
            $foundid = TRUE;
        }
    }
    return $string;
}
//create a new paste (via text posted from webform)
// - print the view/delete links on success
function createnew() {
    global $PASTE_PATH, $SITE_BASE;
    $script = $_POST["script"] ;
    $textAr = explode("\n", $script);
    $text = "";

    foreach ($textAr as $line) {
        $text .= "<pre class='na'>".rtrim($line)." </pre>\n";
    }
    $text = '<div class="spcenter">'.$text.'</div>';
    $pasteid = randomid();
    $f = fopen($PASTE_PATH.$pasteid, "w");
    if ($f == FALSE) {
        echo "Please check folder permissions";
        return;
    }
    fwrite($f, $text);
    fclose($f);
    $deleteid = randomid();
    $f = fopen($PASTE_PATH.$pasteid.$deleteid, "w");
    fclose($f);
    $vl = "$SITE_BASE/$pasteid";
    $dl = "$SITE_BASE/del/$pasteid/$deleteid";
    echo "<h1>Paste created!</h1>";
    echo "<h2>View: <a href='$vl'>$vl</a><br>\n";
    echo "Delete: <a href='$dl'>$dl</a></h2>\n";
}

//create new paste (via text posted from app).
// - print view/delete urls on success, and FAIL on failure.
/*Example return on success:

VIEW,http://paste.trelby.org/32e32d32
DELETE,http://paste.trelby.org/del/32e32d32/dsd78sd8

*/
function createnewapp() {
    global $PASTE_PATH, $SITE_BASE;
    if (!isset ($_POST['htmlscript'])){
        echo "FAIL,No data provided";
        return;
    }
    $script = $_POST["htmlscript"] ;
    $pasteid = randomid();
    $f = fopen($PASTE_PATH.$pasteid, "w");
    if ($f == FALSE) {
        echo "FAIL,Internal pastebin error";
        return;
    }
    fwrite($f, $script);
    fclose($f);
    $deleteid = randomid();
    $f = fopen($PASTE_PATH.$pasteid.$deleteid, "w");
    fclose($f);
    echo "VIEW,$SITE_BASE/$pasteid\n";
    echo "DELETE,$SITE_BASE/del/$pasteid/$deleteid\n";
}

// delete a paste given id, and the deleteid
function deletepaste($pasteid, $deleteid){
    //sanitize
    if (ctype_alnum($pasteid) == FALSE or ctype_alnum($deleteid) == FALSE) {
        oops();
        return;
    }
    global $PASTE_PATH;
    if (file_exists($PASTE_PATH.$pasteid) and file_exists($PASTE_PATH.$pasteid.$deleteid)){
        //delete the paste itself, and rename the pasteid.deleteid to _pasteid.deleteid
        unlink($PASTE_PATH.$pasteid);
        rename($PASTE_PATH.$pasteid.$deleteid, $PASTE_PATH.'_'.$pasteid.$deleteid);
        echo "<h1>Paste Successfully deleted</h1>";
        return;
    }
    if (file_exists($PASTE_PATH.'_'.$pasteid.$deleteid) == TRUE){
        echo "<h1>This paste was already deleted!</h1>";
        return;
    }
    oops();
}

// confirm the user wants to delete the paste
function askfordelete($pasteid, $deleteid){
    //sanitize
    if (ctype_alnum($pasteid) == FALSE or ctype_alnum($deleteid) == FALSE) {
        oops();
        return;
    }
    global $PASTE_PATH, $INST_SUBDIR;
    if (file_exists($PASTE_PATH.$pasteid.$deleteid) == FALSE){
        oops();
        return;
    }
?>
    <h1>Confirm Delete!</h1>
<?php
    echo "<form action='$INST_SUBDIR/cdel/$pasteid/$deleteid' method='POST'>";
    echo "<input type='submit' value='I am sure, delete this paste!'></form>";
    showpaste($pasteid);
}

// catchall error function.
function oops(){
?>
    <h1>Oops!</h1>
    <p>Something that should not happen, did.</p>
    <p>Or something that should happen, didn't.</p>

<?php
}

// show a paste given the id. just read contents from file and echo.
function showpaste($id){
    // first sanitize - check that id is alphanum.
    if (ctype_alnum($id) == FALSE){
        oops();
        return;
    }
    global $PASTE_PATH;
    if (!file_exists($PASTE_PATH.$id)) {
        echo "<h1>Not found!</h1>";
        echo "<p>This paste id ($id) does not exist</p>";
        return;
    }
    $f = fopen($PASTE_PATH.$id, "r");
    $content = fread($f,filesize($PASTE_PATH.$id));
    fclose($f);
    echo "<h2>Viewing paste: $id</h2>";
    echo $content;
}

/* Main program routine */

//first get rid of any magic quotes
foreach ($_GET as $key => &$val) $val = filter_input(INPUT_GET, $key);
foreach ($_POST as $key => &$val) $val = filter_input(INPUT_POST, $key);

if (!isset ($_GET['action']))
{
    headerstuff();
	showmain();
    footerstuff();
}
else
{
	switch ($_GET['action'])
	{
		case "view":
            $id = $_GET['id'];
            headerstuff();
			showpaste($id);
            footerstuff();
			break;
        case "new":
            headerstuff();
            createnew();
            footerstuff();
            break;
		case "newviaapp":
			createnewapp();
			break;
		case "del":
            headerstuff();
            $id = $_GET['id'];
            $did = $_GET['deleteid'];
			askfordelete($id, $did);
            footerstuff();
			break;
        case "confirmdelete":
            headerstuff();
            $id = $_GET['id'];
            $did = $_GET['deleteid'];
            deletepaste($id, $did);
            footerstuff();
            break;
        default:
            headerstuff();
			showmain();
            footerstuff();
	}
}
?>
