<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller
{
    private $old_prefix;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('test_suite_m');
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
        $modules = $this->test_suite_m->get_modules($module_filter, $class_filter);

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
        //$this->test_suite_m->get_modules();
        $post_input= $this->input->post('action_to');

        $tests = array();
        foreach($post_input as $test_string){
            $test = $this->parse_string($test_string);
            $this->test_suite_m->add_test($test);
        }
        $this->test_suite_m->run_tests();
        $test_results = $this->test_suite_m->get_results();
        $result_count= $this->count_results($test_results);
        $this->template
          ->append_css('module::style.css')
          ->set('test_results', $test_results)
          ->set('fails', $result_count['fails'])
          ->set('passes', $result_count['passes']);
          ->build('admin/report');
    }

    /**Parses the string argument used in the admin controller*/
    private function parse_string($string)
    {
        $test_data = explode(':', $string);
        if(sizeof($test_data) !== 3){
            trigger_error("Bad String argument for the TestSuite model.", E_USER_WARNING);
            return false;
        }
        return array(
            'module' => $test_data[0],
            'class' => $test_data[1],
            'method' => $test_data[2]
        );

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
}

