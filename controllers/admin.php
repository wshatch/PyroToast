<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller
{
    public function index()
    {
        //Gather all the modules
        $this->lang->load('pyrotoast');
        $modules = $this->get_modules();
        $names = array();
        $class_names = array();
        foreach($modules as $module){
            $names[] = $module['name'];
            foreach($module['tests'] as $test){
                $class_names[] = $test['class'];
            }
        }
        $class_names = array_unique($class_names);
        //Get the path for each test module
        $this->template
         ->append_js('admin/filter.js')
         ->set('module_names', $names)
         ->set('module_tests', $modules)
         ->set('class_names', $class_names)
         ->set_partial('filters', 'admin/partials/filters.php')
         ->set_partial('tests', 'admin/partials/tests.php')
         ->build('admin/index');
    }

    public function run_tests()
    {
        //Just loads the classes
        $this->get_modules();
        $post_input= $this->input->post('action_to');
        $tests = array();
        foreach($post_input as $test_string){
            $test_obj = new TestHandler($test_string);
       }
        TestHandler::run_tests();
        $test_results = TestHandler::get_all_results();
        $this->template
          ->set('test_results', $test_results)
          ->set('fails', 0)
          ->set('passes', 2)
          ->build('admin/report');
    }

    private function get_modules()
    {
        $this->load->model('modules/module_m'); 
        $params = array('is_core' => FALSE);
        $modules = $this->module_m->get_all($params);
        $ret_modules = array();
        foreach($modules as $module){
            if(is_dir($module['path'].'/tests')){
                $ret_modules[] = array('tests' => $this->get_tests($module),
                                    'name' => $module['slug']);
            }
        }
        return $ret_modules;
    }

    /* Gets a list of test functions from the file */
    private function get_tests($module)
    {
        $dir_location = FCPATH.$module['path'].'/tests';
        $dir = opendir($dir_location);
        while(false !== ($file = readdir($dir))){
            if(pathinfo($file, PATHINFO_EXTENSION) === 'php'){
                include_once $dir_location.DIRECTORY_SEPARATOR.$file;
            }
        }
        //Get the subclasses and the test functions
        $methods = array();
        foreach(get_declared_classes() as $class){
            if(is_subclass_of($class, 'Toast')){
                $reflector = new ReflectionClass($class);
                $reflector_methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
                foreach($reflector_methods as $method){
                    //don't need to know methods that start with __ 
                    //or constructor
                    if(strpos($method->name, '__') !== false ||
                       $method->name === $class ||
                       $method->getDeclaringClass()->name == "Toast"){
                       continue;
                    }
                    $methods[] = array('class'   => $class,
                                       'method'  => $method->name);
                }
            }
        }
        return $methods;
    }
}

class TestHandler
{
    private $module;
    private $class;
    private $method;
    private $results;

    private static $total_results;
    private static $test_obj_tree = array();

    public function __construct($string)
    {
        $test_data = explode(':', $string);
        if(sizeof($test_data) !== 3){
            $elements = sizeof($test_data) - 1;
            trigger_error("TestHandler constructor requires a string delimited by two ':'. String provided delimeted by $elements", E_USER_WARNING);
            return;
        }
        $this->module = $test_data[0];
        $this->test_class = $test_data[1];
        $this->method = $test_data[2];
        
        TestHandler::add_tree_node($this);
    }

    public function run_test()
    {
        $test_obj = new $this->class();
        $test_obj->run($this->method);
        $this->results = $test_obj->get_data();
    }

    public function get_results()
    {
        return $this->results;
    }

    public static function run_tests()
    {
        foreach(self::$test_obj_tree as $module=> $test_obj){
            foreach($test_obj as $class => $methods){
                $test_obj = new $class();
                foreach($methods as $method){
                    $test_obj->run($method);
                }
                self::$total_results[$class] = $test_obj->get_data();
            }
        }
    }

    public static function get_all_results()
    {
        return self::$total_results;
    }

    private static function add_tree_node($test_obj)
    {
        if(empty(self::$test_obj_tree[$test_obj->module])){
            self::$test_obj_tree[$test_obj->module] = 
                array(
                    $test_obj->test_class => array($test_obj->method)
                );
        }
        else if(empty(self::$test_obj_tree[$test_obj->module][$test_obj->test_class])){
            self::$test_obj_tree[$test_obj->module][$test_obj->test_class] = $test_obj->method;
        }
        else{
            self::$test_obj_tree[$test_obj->module][$test_obj->test_class][] = $test_obj->method;
        }
    }
}
