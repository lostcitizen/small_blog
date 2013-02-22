<?php
require_once 'settings.php';
require_once 'lib/php_adodb_v5.18/adodb.inc.php';
require_once 'lib/utils.php';
require_once '../class/smallblog.php';

$include = true;

$sblog = new smallblog();
$res = $sblog->db_connect($dbcon_settings);
if($res === false) {
	echo 'database error...';
	exit;
} else {

    // retrieve url
	$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$duri = urldecode($uri);
	$purl_len = mb_strlen(urldecode($project_url));
	$url = mb_substr($duri, $purl_len);

	// get post
	$post = $sblog->getPostByURL($url);
	if($post === false) {
		echo 'Error retreiving post with url: ' . $url;
		exit;
	} else {
		if(count($post) == 1) {
			$post_id = $post[0]['id'];
			$dp = $post[0]['date_published'];
			$title = $post[0]['post_title'];
			$impressions = $post[0]['impressions'];
		} else {
			echo 'Cannot retrieve post with url: ' . $url;
			exit;
		}
	}
}
?>

<div id="post" style="width: 60%; margin: auto">

	<h1><a href="index.php">&laquo; back to Blog home</a></h1>

	<?php

	if($dp != '' && $dp <= now('UTC')) {

		$post_content_path = $project_path . $html_path . '/' . substr($dp, 0, 4) . '/' . substr($dp, 4, 2)  . $url . '/post/content' . $html_ext;
		print '<h1>' . $title . '</h1>';
		print '<h3>Viewed ' . $impressions . ' times</h3>';
		include_once $post_content_path;

		$res = $sblog->increasePostImpressions($post_id);

	} else {
		print 'Post is not published...';
	}
	?>

</div>