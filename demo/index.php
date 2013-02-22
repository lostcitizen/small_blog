<?php
require_once 'settings.php';
require_once 'lib/php_adodb_v5.18/adodb.inc.php';
require_once 'lib/utils.php';
require_once '../class/smallblog.php';

$include = true;
$posts_count = 0;

$sblog = new smallblog();
$res = $sblog->db_connect($dbcon_settings);
if($res === false) {
	echo 'database error...';
	exit;
} else {

	// count all posts
	$posts = $sblog->getPosts(0, 0, '', '', '', now('UTC'), '', true);
	if($post === false) {
		echo 'Error counting posts...';
		exit;
	} else {
		$posts_count = $posts[0]['total_posts'];
	}


	// get posts for blog home
	$posts = $sblog->getPosts(0, 10, '', '', '', now('UTC'));
	if($post === false) {
		echo 'Error retreiving posts...';
		exit;
	} else {
		if(count($posts) == 0) {
			echo 'No posts found...';
			exit;
		}
	}
}
?>


<div id="post" style="width: 60%; margin: auto">

    <h1>Blog home (total posts: <?php print $posts_count ?>)</h1>
		<?php
		foreach($posts as $post) {
			$dp = $post['date_published'];
			$url = $post['url'];
			$post_content_path = $project_path . $html_path . '/' . substr($dp, 0, 4) . '/' . substr($dp, 4, 2)  . $url . '/home/content' . $html_ext;
			$dpd = date_decode($post['date_published'], 'UTC', 'd/m/Y');
			print '<h2><a href="' . $project_url . $post['url'] . '">' . $dpd . ' - ' . $post['post_title'] . '</a></h2>';
			include_once $post_content_path;
		}
		?>
</div>