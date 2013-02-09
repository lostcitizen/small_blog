<?php
require_once 'settings.php';
require_once 'lib/php_adodb_v5.18/adodb.inc.php';
require_once 'lib/utlis.php';
require_once '../class/smallblog.php';

$sblog = new smallblog();
$res = $sblog->db_connect($dbcon_settings);
if($res === false) {
	echo 'database error...';
	exit;
} else {

	echo parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '<br>';

	// retrieve url

	$durl = urldecode($_SERVER['REQUEST_URI']);
	$purl_len = mb_strlen($project_url);
	$qs_pos = mb_strpos($durl, '?');
	if($qs_pos === false) {
		$url = mb_substr($durl, $purl_len);
	} else {
		$url_len = $qs_pos - $purl_len;
		$url = mb_substr($durl, $purl_len, $url_len);
	}

	echo $durl . '<br>';
	echo $qs_pos . '<br>';
	echo $purl_len . '<br>';
	echo $_SERVER['QUERY_STRING'] . '<br>';
	echo $url . '<br>';


	$post = $sblog->getPostByURL($url);

}

?>


<div id="post" style="width: 60%; margin: auto">


	<?php
	//echo $project_path . $html_path . $url . $html_ext;
	if($post[0]['date_published'] != '') {


		print '<h1>' . $post[0]['post_title'] . '</h1>';
		include_once $project_path . $html_path . $url . $html_ext;

		//$res = $sblog->increasePostInmpressions($post_id);

	} else {
		print 'Post is not published...';
	}


	?>

</div>