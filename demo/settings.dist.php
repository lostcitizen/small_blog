<?php

// DO NOT CHANGE

/* set server default timezone (it is possible to set from php.ini) */
define('CONST_SERVER_TIMEZONE', 'UTC');
date_default_timezone_set(CONST_SERVER_TIMEZONE);

/* set server dateformat */
define('CONST_SERVER_DATEFORMAT', 'YmdHis');

// COPY THIS FILE AS settings.php AND CONFIGURE THE FOLLOWING PARAMETER VALUES

$project_url = '/small_blog/demo';
$project_full_url = 'http://' . $_SERVER['HTTP_HOST'] . '/small_blog/demo';
$project_path = '/var/www/small_blog/demo';
$html_path = '/html';
$html_ext = '.php';

$dbcon_settings = array(
	'rdbms' => 'ADODB', // one of "ADODB", "MYSQL", "MYSQLi", "MYSQL_PDO", "POSTGRES" (at this time only "ADODB" and "POSTGRES" are implemented)
	'use_prepared_statements' => true,
	'php_adodb_driver' => 'pdo_mysql', // ADODB drivers tested: mysql, mysqlt, mysqli, pdo_mysql, postgres
	'php_adodb_dsn_options_persist' => '0', // do not change if you are not sure
	'php_adodb_dsn_options_misc' => '', // do not set fetchmode here, it is set to ADODB_FETCH_ASSOC
	'php_adodb_dsn_custom' => '',
	'db_server' => 'DB_SERVERNAME_OR_IP',
	'db_name' => 'DBNAME',
	'db_user' => 'DB_USER',
	'db_passwd' => 'DB_PASSWORD',
	'db_port' => '',
	'query_after_connection' => '' // e.g. 'SET NAMES UTF8'
);
?>