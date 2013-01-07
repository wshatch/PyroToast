<?php
//change this variable to whatever you're using in 
//$config['index_page'] in system/cms/config/config.php
$index_page = '';
//overwrite the global route to a 404 page to our help controller
//prepare the arguments for URI parsing.
$default_args = array($argv[0], 'pyrotoast', 'cli');
$other_args = array_slice($argv, 1);
$args = array_merge($default_args, $other_args);
//generate the request URI 
$request_URI = '/'.$index_page. implode(array_slice($args,1), '/');

/*check to see if we have a valid controller method */
//Too lazy to use a reflection class. The Cli controller should be simple,
//so we'll just update it when we add to the controller for now.
$controller_methods = array('help', 'test');
//Generate the help page if there's a bad argument
if(!in_array($other_args[0], $controller_methods)){
    $args[3] = 'help';
    $request_URI = '/'.$index_page . 'pyrotoast/cli/help';
}
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
