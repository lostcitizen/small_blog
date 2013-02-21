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
	 * @return object|bool database connection or false
	 */
	public function db_connect($db_settings) {
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
					$conn->execute($db_settings['query_after_connection']);
				}
			}

		} else if($db_type == "POSTGRES") {
			$dsn = 'host=' . $db_settings['db_server'] . ' port=' . $db_settings['db_port'] . ' dbname=' . $db_settings['db_name'] .
				' user=' . $db_settings['db_user'] . ' password=' . rawurlencode($db_settings['db_passwd']);
			$conn = pg_connect($dsn);
		}

		if($conn === false) {
			$this->last_error = 'Cannot connect to database';
		}

		$this->db_settings = $db_settings;
		$this->conn = $conn;

	}


	/**
	 * Get posts meta-data for given range
	 *
	 * @param $offset
	 * @param $posts_per_page
	 * @param $date_until
	 * @param $date_from
	 * @return array|bool posts data or false
	 */
	public function getPosts($offset, $posts_per_page, $date_until, $date_from = '') {
		$posts = false;
		$conn = $this->conn;

		$rdbms = $this->db_settings['rdbms'];
		$use_prepared_statements = $this->db_settings['use_prepared_statements'];

		if($rdbms == "ADODB") {
			if($use_prepared_statements) { // SelectLimit cannot be used with PREPARED STATEMENTS in ADODB
				$sql = 'SELECT * FROM posts WHERE date_published is not null AND date_published <= ?';
				if($date_from != '') {
					$sql .= ' AND date_published >= ?';
				}
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

				$a_bind_params = array($date_until);
				if($date_from != '') {
					$a_bind_params = array($date_until, $date_from);
				}
				$smtp = $conn->Execute($sql, $a_bind_params);

				if($smtp === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
					$posts = false;
				} else {
					$posts = $smtp->GetRows();
				}
			} else {
				$sql = 'SELECT * FROM posts WHERE date_published is not null AND date_published <= ' . $conn->qstr($date_until);
				if($date_from != '') {
					$sql .= ' AND date_published >= ' . $conn->qstr($date_from);
				}
				$rs = $conn->SelectLimit($sql, $posts_per_page, $offset);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . $conn->ErrorMsg();
					$posts = false;
				} else {
					$posts = $rs->GetRows();
				}
			}
		} else if($rdbms == "POSTGRES") {

			if($use_prepared_statements) {
				$sql = 'SELECT * FROM posts WHERE date_published is not null AND date_published <= $1';
				if($date_from != '') {
					$sql .= ' AND date_published >= $2';
				}
				$sql .= ' LIMIT ' . $posts_per_page . ' OFFSET ' . $offset;
				$a_bind_params = array($date_until);
				if($date_from != '') {
					$a_bind_params = array($date_until, $date_from);
				}

				$rs = pg_query_params($conn, $sql, $a_bind_params);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
					$posts = false;
				} else {
					$posts = pg_fetch_all($rs);
				}
			} else {
				$sql = 'SELECT * FROM posts WHERE date_published is not null AND date_published <= ' . pg_escape_literal($conn, $date_until);
				if($date_from != '') {
					$sql .= ' AND date_published >= ' . pg_escape_literal($conn, $date_until);
				}
				$sql .= ' LIMIT ' . $posts_per_page . ' OFFSET ' . $offset;
				$rs = pg_query($conn, $sql);
				if($rs === false) {
					$this->last_error = 'Wrong SQL: ' . $sql . ' Error: ' . pg_last_error();
					$posts = false;
				} else {
					$posts = pg_fetch_all($rs);
				}
			}
		}

		return $posts;
	}


	/**
	 * Get post meta-data using post url
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
