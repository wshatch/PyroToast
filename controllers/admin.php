<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller
{
    private $old_prefix;

    public function __construct()
    {
        parent::__construct();
        $this->old_prefix = $this->db->dbprefix;
    }
    public function index()
    {
        //Gather all the modules
        $this->lang->load('pyrotoast');
        //Check for filter fields.
        $class_input = $this->input->post('f_classes');
        $class_filter = $class_input === '0' ? FALSE : $class_input;

        $module_input = $this->input->post('f_module_name');
        $module_filter = $module_input === '0' ? False : $module_input;
        $modules = $this->get_modules($module_filter, $class_filter);
        $names = array();
        $class_names = array();
        foreach($modules as $module){
            $name = $module['name'];
            $names[$name] = $name; 
            //Skip the ones we're trying to filter
            if($module_filter !== false and $name !== $module_filter){
                continue;
            }
            foreach($module['tests'] as $test){
                $class_name = $test['class'];
                if($class_filter === false){
                    $class_names[$class_name] = $class_name;
                }
                else if($test['class'] === $class_filter){
                    $class_names[$class_name] = $class_name;
                }
            }
        }
        $ajax = $this->input->is_ajax_request();
        $ajax and $this->template->set_layout(FALSE);
        //$class_names = array_values(array_unique($class_names, SORT_STRING));

        $this->template
         ->append_js('admin/filter.js')
         ->set('module_names', $names)
         ->set('module_tests', $modules)
         ->set('class_names', $class_names)
         ->set_partial('filters', 'admin/partials/filters.php');
        if($ajax){
            $this->template
             ->build('admin/partials/tests.php');
        }
        else{
            $this->template
             ->set_partial('tests', 'admin/partials/tests.php')
             ->build('admin/index');
        }
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
        $result_count= $this->count_results($test_results);
        $this->template
          ->set('test_results', $test_results)
          ->set('fails', $result_count['fails'])
          ->set('passes', $result_count['passes'])
          ->build('admin/report');
    }

    private function count_results($results)
    {
        $passes = 0;
        $fails = 0;
        foreach($results as $class_results){
            foreach($class_results['results'] as $result){
                if($result['Result'] == 'Passed'){
                    $passes++;
                }
                else{
                    $fails++;
                }
            }
        }
        return array( 'passes' => $passes,
                      'fails' =>  $fails);
    }

    private function get_modules($module_filter = FALSE, $class_filter = False)
    {
        $this->load->model('modules/module_m'); 
        $params = array('is_core' => FALSE);
        $modules = $this->module_m->get_all($params);
        $ret_modules = array();
        foreach($modules as $module){
            if($module_filter !== false and $module_filter !== $module['slug']){
                continue;
            }
            if(is_dir($module['path'].'/tests')){
                $ret_modules[] = array('tests' => $this->get_tests($module, $class_filter),
                                    'name' => $module['slug']);
            }
        }
        return $ret_modules;
    }

    /* Gets a list of test functions from the file */
    private function get_tests($module, $class_filter = FALSE)
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
        $this->install_test_tables('Invoicer');
        foreach(get_declared_classes() as $class){
            if($class_filter !== FALSE and $class !== $class_filter){
                continue;
            }
            if(is_subclass_of($class, 'Toast')){
                $reflector = new ReflectionClass($class);
                $reflector_methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
                foreach($reflector_methods as $method){
                    //don't need to know methods that start with __ 
                    if(strpos($method->name, '__') !== false ||
                       //or constructor
                       $method->name === $class ||
                       $method->getDeclaringClass()->name == "Toast" ||
                       //Ignore setup methods
                       $method->name === "pre" ||
                       $method->name === "post" ){
                       continue;
                    }
                    $methods[] = array('class'   => $class,
                                       'method'  => $method->name);
                }
            }
        }
        return $methods;
    }

    private function install_test_tables($module)
    {
        $this->change_db_prefix();
        $this->create_settings_table();

        $module_obj = $this->get_module_object($module);
        $install_result = $module_obj->install();
        //TODO: render an error if the test install didn't work.
        $this->reset_db_prefix();
    }

    private function delete_test_tables($module)
    {
        $this->change_db_prefix();

        $this->delete_settings_table();

        $module_obj = $this->get_module_object($module);
        $module_obj->uninstall();

        $this->reset_db_prefix();
    }

    private function get_module_object($module)
    {
        //actually build the module
        $details_module = 'Module_'.$module;
        $details = new $details_module();
        //Set the variables that Module_m->install does.
        $details->site_ref = SITE_REF;
        $details->upload_path =  'uploads/'.SITE_REF .'/';
        return $details;
    }


    private function create_settings_table()
    {
        $this->dbforge->drop_table('settings');
        $fields = array(
                'slug' => array('type' => 'VARCHAR', 'constraint' => 30, 'primary' => true, 'unique' => true, 'key' => 'index_slug'),
                'title' => array('type' => 'VARCHAR', 'constraint' => 100,),
                'description' => array('type' => 'TEXT',),
                'type' => array('type' => 'set',  'constraint' => array('text','textarea','password','select','select-multiple','radio','checkbox'),),
                'default' => array('type' => 'TEXT',),
                'value' => array('type' => 'TEXT',),
                'options' => array('type' => 'VARCHAR', 'constraint' => 255,),
                'is_required' => array('type' => 'INT', 'constraint' => 1,),
                'is_gui' => array('type' => 'INT', 'constraint' => 1,),
                'module' => array('type' => 'VARCHAR', 'constraint' => 50,),
                'order' => array('type' => 'INT', 'constraint' => 10, 'default' => 0,),
            );
        $this->dbforge->add_field($fields);
        $this->dbforge->create_table('settings', TRUE);
    }


    private function change_db_prefix()
    {
        $test_prefix = $this->settings->test_table_prefix;
        $this->db->dbprefix = $test_prefix;
    }

    private function reset_db_prefix()
    {
        $this->db->dbprefix = $this->old_prefix;
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
            self::$test_obj_tree[$test_obj->module][$test_obj->test_class] = array($test_obj->method);
        }
        else{
            self::$test_obj_tree[$test_obj->module][$test_obj->test_class][] = $test_obj->method;
        }
    }
}
