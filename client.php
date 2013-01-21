#!/usr/bin/php
<?php
//Hackish way to make sure we switch to the directory that index.php is in
$file_path = $_SERVER['PWD'].'/'.$_SERVER['PHP_SELF'];
chdir(dirname($file_path));
//Keep our current directory since we're going to change it back in our cli controller.
$GLOBALS['pyrotoast_client_path'] = getcwd();
//change this variable to whatever you're using in 
//$config['index_page'] in system/cms/config/config.php
$index_page = '';
//overwrite the global route to a 404 page to our help controller
//prepare the arguments for URI parsing.
$default_args = array($argv[0], 'pyrotoast', 'cli','index');
$other_args = array_slice($argv, 1);
$args = array_merge($default_args, $other_args);
//generate the request URI 
$request_URI = '/'.$index_page. implode(array_slice($args,1), '/');
/* Just define some globals used in config to get rid of some errors.*/
$temp_server = array(
    'SERVER_NAME' => 'localhost',
    'REQUEST_URI' => $request_URI,
    'QUERY_STRING' => '',
    'SERVER_PORT' => 80,
    'REQUEST_METHOD' => 'GET',
    'SERVER_NAME' => 'localhost',
    'ENVIRONMENT' => 'development',
    'PHP_SELF' => $request_URI,
    'HTTP_HOST' => 'localhost',
    'argv' => $args
);
$_SERVER = array_merge($_SERVER, $temp_server);
//Finally, include the index.php file
$argv = $args;
chdir('../../../..');
require_once('index.php');
