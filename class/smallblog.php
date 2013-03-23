<?php
/**
 * smallblog php class, handling blogging operations.
 *
 * @author     Christos Pontikis http://pontikis.net
 * @copyright    Christos Pontikis
 * @license https://raw.github.com/pontikis/small_blog/master/MIT_LICENSE MIT
 * @version    0.1.0 (10 Feb 2013)
 * @link https://github.com/pontikis/small_blog
 *
 **/
class smallblog {

	/** @var string Last error occured */
	private $last_error;

	/** @var array Database connection settings */
	private $db_settings;

	/** @var object Database connection */
	private $conn;


	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		// initialize
		$this->db_settings = null;
		$this->conn = null;
		$this->last_error = null;
	}


	public function get_last_error() {
		return $this->last_error;
	}

	/**
	 * Create database connection
	 *
	 * Supported RDMBS: "ADODB", "MYSQL", "MYSQLi", "MYSQL_PDO", "POSTGRES"
	 * Currently only "ADODB" and "POSTGRES" are implemented. ADODB drivers tested: mysql, mysqlt, mysqli, pdo_mysql, postgres.
	 * \todo implement misc RDBMS
	 *
	 * @param Array $db_settings database settings
	 * @return bool
	 */
	public function db_connect($db_settings) {
		$result = false;
		$db_type = $db_settings['rdbms'];

		if(!in_array($db_type, array("ADODB", "POSTGRES"))) {
			$this->last_error = 'Database (' . $db_type . ') not supported';
			return false;
		}

		if($db_type == "ADODB" && !in_array($db_settings['php_adodb_driver'], array("mysql", "mysqlt", "mysqli", "pdo_mysql", "postgres"))) {
			$this->last_error = 'ADODB driver ' . $db_settings['php_adodb_driver'] . ') not supported';
			return false;
		}

		if($db_type == "ADODB") {

			switch($db_settings['php_adodb_driver']) {
				case 'mysql':
				case 'mysqlt':
				case 'mysqli':
				case 'pdo_mysql':
				case 'postgres':
					$dsn = $db_settings['php_adodb_driver'] . '://' . $db_settings['db_user'] . ':' . rawurlencode($db_settings['db_passwd']) .
						'@' . $db_settings['db_server'] . '/' .
						$db_settings['db_name'] .
						'?persist=' . $db_settings['php_adodb_dsn_options_persist'] . '&fetchmode=' . ADODB_FETCH_ASSOC . $db_settings['php_adodb_dsn_options_misc'];
					$conn = NewADOConnection($dsn);
					break;
				case 'firebird':
					$dsn = $db_settings['php_adodb_driver'] . '://' . $db_settings['db_user'] . ':' . rawurlencode($db_settings['db_passwd']) .
						'@' . $db_settings['db_server'] . '/' . $db_settings['db_name'] .
						'?persist=' . $db_settings['php_adodb_dsn_options_persist'] . '&fetchmode=' . ADODB_FETCH_ASSOC . $db_settings['php_adodb_dsn_options_misc'];
					$conn = NewADOConnection($dsn);
					break;
				case 'sqlite':
				case 'oci8':
					$conn = NewADOConnection($db_settings['php_adodb_dsn_custom']);
					break;
				case 'access':
				case 'db2':
					$conn =& ADONewConnection($db_settings['php_adodb_driver']);
					$conn->Connect($db_settings['php_adodb_dsn_custom']);
					break;
				case 'odbc_mssql':
					$conn =& ADONewConnection($db_settings['php_adodb_driver']);
					$conn->Connect($db_settings['php_adodb_dsn_custom'], $db_settings['db_user'], $db_settings['db_passwd']);
					break;
			}

			if($conn !== false) {
				if($db_settings['query_after_connection']) {
					$conn->Execute($db_settings['query_after_connection']);
				}
			}

		} else if($db_type == "POSTGRES") {
			$dsn = 'host=' . $db_settings['db_server'] . ' port=' . $db_settings['db_port'] . ' dbname=' . $db_settings['db_name'] .
				' user=' . $db_settings['db_user'] . ' password=' . rawurlencode($db_settings['db_passwd']);
			$conn = pg_connect($dsn);
		}

		if($conn === false) {
			$this->last_error = 'Cannot connect to database';
		} else {
			$result = true;
		}

		$this->db_settings = $db_settings;
		$this->conn = $conn;

		return $result;

	}


	/**
	 * Get posts meta-data for given range
	 *
	 * @param $offset
	 * @param $posts_per_page
	 * @param $tag
	 * @param $tag_delim
	 * @param $ctg_id
	 * @param $date_until
	 * @param $date_from
	 * @param $count
	 * @return array|bool posts meta-data or false
	 */
	public function getPosts($offset, $posts_per_page, $tag, $tag_delim, $ctg_id, $date_until, $date_from = '', $count = false) {
		$posts = false;
		$conn = $this->conn;

		$select = ($count ? ' count(id) as total_posts ' : ' * ');

		$rdbms = $this->db_settings['rdbms'];
		$use_prepared_statements = $this->db_settings['use_prepared_statements'];

		if($rdbms == "ADODB") {

			if($use_prepared_statements) {
				$sql = 'SELECT' . $select . 'FROM posts WHERE date_published is not null AND date_published <= ?';
				if($date_from != '') {
					$sql .= ' AND date_published >= ?';
				}
				if($tag != '') {
					$sql .= ' AND tags LIKE ?';
				}
				if($ctg_id > 0) {
					$sql .= ' AND ctg_id = ?';
				}

				$a_bind_params = array($date_until);
				if($date_from != '') {
					array_push($a_bind_params, $date_from);
				}
				if($tag != '') {
					array_push($a_bind_params, '%' . $tag_delim . $tag . $tag_delim . '%');
				}
				if($ctg_id > 0) {
					array_push($a_bind_params, $ctg_id);
				}

			} else {

				$sql = 'SELECT' . $select . 'FROM posts WHERE date_published is not null AND date_published <= ' . $conn->qstr($date_until);
				if($date_from != '') {
					$sql .= ' AND date_published >= ' . $conn->qstr($date_from);
				}
				if($tag != '') {
					$sql .= ' AND tags LIKE ' . $conn->qstr('%' . $tag_delim . $tag . $tag_delim . '%');
				}
				if($ctg_id > 0) {
					$sql .= ' AND ctg_id = ' . $ctg_id;
				}

			}

			if(!$count) {
				$sql .= ' ORDER BY date_published DESC';
			}

			if($posts_per_page > 0) {
				switch($this->db_settings['php_adodb_driver']) {
					/**  \todo implement misc ADODB drivers */
					case "mysql":
					case "mysqlt":
					case "mysqli":
					case "pdo_mysql":
						$sql .= ' LIMIT ' . $offset . ',' . $posts_per_page;
						break;
					case "postgres":
						$sql .= ' LIMIT ' . $posts_per_page . ' OFFSET ' . $offset;
				}
			}

			if($use_prepared_statements) {
				$rs = $conn->Execute($sql, $a_bind_params);
			} else {
				$rs = $conn->Execute($sql);
			}

			if($rs === false) {
				$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
				$posts = false;
			} else {
				$posts = $rs->GetRows();
			}

		} else if($rdbms == "POSTGRES") {

			if($use_prepared_statements) {
				$sql = 'SELECT' . $select . 'FROM posts WHERE date_published is not null AND date_published <= $1';
				if($date_from != '') {
					$sql .= ' AND date_published >= $2';
				}
				if($tag != '') {
					$sql .= ' AND tags LIKE $3';
				}
				if($ctg_id > 0) {
					$sql .= ' AND ctg_id = $4';
				}

				$a_bind_params = array($date_until);
				if($date_from != '') {
					array_push($a_bind_params, $date_from);
				}
				if($tag != '') {
					array_push($a_bind_params, '%' . $tag_delim . $tag . $tag_delim . '%');
				}
				if($ctg_id > 0) {
					array_push($a_bind_params, $ctg_id);
				}


			} else {
				$sql = 'SELECT' . $select . 'FROM posts WHERE date_published is not null AND date_published <= ' . pg_escape_literal($conn, $date_until);
				if($date_from != '') {
					$sql .= ' AND date_published >= ' . pg_escape_literal($conn, $date_from);
				}
				if($tag != '') {
					$sql .= ' AND tags LIKE ' . pg_escape_literal($conn, '%' . $tag_delim . $tag . $tag_delim . '%');
				}
				if($ctg_id > 0) {
					$sql .= ' AND ctg_id = ' . $ctg_id;
				}
			}

			if(!$count) {
				$sql .= ' ORDER BY date_published DESC';
			}
			if($posts_per_page > 0) {
				$sql .= ' LIMIT ' . $posts_per_page . ' OFFSET ' . $offset;
			}

			if($use_prepared_statements) {
				$rs = pg_query_params($conn, $sql, $a_bind_params);
			} else {
				$rs = pg_query($conn, $sql);
			}

			if($rs === false) {
				$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
				$posts = false;
			} else {
				$posts = pg_fetch_all($rs);
			}
		}

		return $posts;
	}


	/**
	 * Get post meta-data from post url
	 *
	 * @param $url
	 * @return array|bool post data or false
	 */
	public function getPostByURL($url) {
		$post = false;
		$conn = $this->conn;

		$rdbms = $this->db_settings['rdbms'];
		$use_prepared_statements = $this->db_settings['use_prepared_statements'];

		if($rdbms == "ADODB") {
			if($use_prepared_statements) {

				$sql = 'SELECT * FROM posts WHERE url=?';
				$a_bind_params = array($url);

				$stmt = $conn->Execute($sql, $a_bind_params);
				if($stmt === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
				} else {
					$post = $stmt->GetRows();
				}
			} else {

				$sql = 'SELECT * FROM posts WHERE url=' . $conn->qstr($url);

				$rs = $conn->Execute($sql);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
				} else {
					$post = $rs->GetRows();
				}
			}
		} else if($rdbms == "POSTGRES") {
			if($use_prepared_statements) {

				$sql = 'SELECT * FROM posts WHERE url=$1';
				$a_bind_params = array($url);

				$rs = pg_query_params($conn, $sql, $a_bind_params);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
				} else {
					$post = pg_fetch_all($rs);
				}
			} else {

				$sql = 'SELECT * FROM posts WHERE url=' . pg_escape_literal($conn, $url);

				$rs = pg_query($conn, $sql);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
				} else {
					$post = pg_fetch_all($rs);
				}
			}
		} else {

		}

		return $post;
	}

	/**
	 * Increase Post Impressions
	 *
	 * @param $id
	 * @return bool
	 */
	public function increasePostImpressions($id) {
		$res = false;
		$conn = $this->conn;

		$rdbms = $this->db_settings['rdbms'];
		$use_prepared_statements = $this->db_settings['use_prepared_statements'];

		if($rdbms == "ADODB") {
			if($use_prepared_statements) {

				$sql = 'UPDATE posts SET impressions=impressions+1 WHERE id=?';
				$a_bind_params = array($id);

				$stmt = $conn->Execute($sql, $a_bind_params);
				if($stmt === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
				} else {
					$res = true;
				}
			} else {

				$sql = 'UPDATE posts SET impressions=impressions+1 WHERE id=' . $id;

				$rs = $conn->GetRow($sql);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
				} else {
					$res = true;
				}
			}
		} else if($rdbms == "POSTGRES") {
			if($use_prepared_statements) {

				$sql = 'UPDATE posts SET impressions=impressions+1 WHERE id=$1';
				$a_bind_params = array($id);

				$rs = pg_query_params($conn, $sql, $a_bind_params);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
				} else {
					$res = true;
				}
			} else {

				$sql = 'UPDATE posts SET impressions=impressions+1 WHERE id=' . $id;

				$rs = pg_query($conn, $sql);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
				} else {
					$res = true;
				}
			}
		} else {

		}

		return $res;
	}


	/**
	 * Get next or previous post
	 *
	 * @param $date
	 * @param string $pos
	 * @return array|bool
	 */
	public function getNearPost($date, $pos = 'next') {
		$post = false;
		$conn = $this->conn;

		$rdbms = $this->db_settings['rdbms'];
		$use_prepared_statements = $this->db_settings['use_prepared_statements'];

		if($rdbms == "ADODB") {
			if($use_prepared_statements) {

				if($pos == 'previous') {
					$sql = 'SELECT url, post_title FROM posts WHERE date_published IS NOT NULL AND date_published < ?' .
						' ORDER BY date_published DESC LIMIT 0,1';
				}
				if($pos == 'next') {
					$sql = 'SELECT url, post_title FROM posts WHERE date_published IS NOT NULL AND date_published > ?' .
						' ORDER BY date_published ASC LIMIT 0,1';
				}
				$a_bind_params = array($date);

				$stmt = $conn->Execute($sql, $a_bind_params);
				if($stmt === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
				} else {
					$post = $stmt->GetRows();
				}
			} else {

				if($pos == 'previous') {
					$sql = 'SELECT url, post_title FROM posts WHERE date_published IS NOT NULL AND date_published < ' .	$conn->qstr($date) .
						' ORDER BY date_published DESC LIMIT 0,1';
				}
				if($pos == 'next') {
					$sql = 'SELECT url, post_title FROM posts WHERE date_published IS NOT NULL AND date_published > ' .	$conn->qstr($date) .
						' ORDER BY date_published ASC LIMIT 0,1';
				}

				$rs = $conn->Execute($sql);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
				} else {
					$post = $rs->GetRows();
				}
			}
		} else if($rdbms == "POSTGRES") {
			if($use_prepared_statements) {

				if($pos == 'previous') {
					$sql = 'SELECT url, post_title FROM posts WHERE date_published IS NOT NULL AND date_published < $1' .
						' ORDER BY date_published DESC LIMIT 0,1';
				}
				if($pos == 'next') {
					$sql = 'SELECT url, post_title FROM posts WHERE date_published IS NOT NULL AND date_published > $1' .
						' ORDER BY date_published ASC LIMIT 0,1';
				}
				$a_bind_params = array($date);

				$rs = pg_query_params($conn, $sql, $a_bind_params);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
				} else {
					$post = pg_fetch_all($rs);
				}
			} else {

				if($pos == 'previous') {
					$sql = 'SELECT url, post_title FROM posts WHERE date_published IS NOT NULL AND date_published < ' .	pg_escape_literal($conn, $date) .
						' ORDER BY date_published DESC LIMIT 0,1';
				}
				if($pos == 'next') {
					$sql = 'SELECT url, post_title FROM posts WHERE date_published IS NOT NULL AND date_published > ' .	pg_escape_literal($conn, $date) .
						' ORDER BY date_published ASC LIMIT 0,1';
				}

				$rs = pg_query($conn, $sql);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
				} else {
					$post = pg_fetch_all($rs);
				}
			}
		} else {

		}

		return $post;
	}


	/**
	 * Disconnect database
	 *
	 * @param $conn
	 */
	public function db_disconnect($conn) {
		$rdbms = $this->db_settings['rdbms'];

		if($rdbms == "ADODB") {
			$conn->Close();
		} elseif(($rdbms == "POSTGRES")) {
			pg_close($conn);
		}
	}
}
