<?php

/** The Test Suite model. This model is represented by a three level array/tree structure.
    The first level contains all the modules that have unit tests.
    These module then contain classes, and finally the classes contain test methods
    that are actually ran. 
*/
class Test_suite_m extends My_Model
{
    private $results;

    private $total_results;
    private $test_obj_tree = array();
    private $old_prefix;

    public function run_tests()
    {
        foreach($this->test_obj_tree as $module=> $test_obj){
            $this->install_test_table($module);
            foreach($test_obj as $class => $methods){
                $test_obj = new $class();
                foreach($methods as $method){
                    $test_obj->run($method);
                }
                $this->total_results[$class] = $test_obj->get_data();
            }
            $this->uninstall_test_table($module);
        }
    }

    public function get_results()
    {
        return $this->total_results;
    }

    public function add_test($test_obj)
    {
        $module = $test_obj['module'];
        $class = $test_obj['class'];
        $method = $test_obj['method'];

        //No classes have been added to the module node.
        if(empty($this->test_obj_tree[$module])){
            $this->test_obj_tree[$module] = 
                array(
                    $class => array($method)
                );
        }
        //The test is part of an already added module, but not class.
        else if(empty($this->test_obj_tree[$module][$class])){
            $this->test_obj_tree[$module][$class] = array($method);
        }
        //We have the module and the class in the tree, now we just need to add the method node.
        else{
            $this->test_obj_tree[$module][$class][] = $method;
        }
    }

    private function install_test_table($module)
    {
        log_message('debug', "Installing test table for $module");
        $this->change_db_prefix();
        $this->create_settings_table();

        $module_obj = $this->get_module_object($module);
        $install_result = $module_obj->install();
        $this->reset_db_prefix();
        return $install_result;
    }

    private function uninstall_test_table($module)
    {
        log_message('debug', "uinstalling test table for $module");
        $this->change_db_prefix();

        $module_obj = $this->get_module_object($module);
        $module_obj->uninstall();

        $this->reset_db_prefix();
    }

    private function change_db_prefix()
    {
        log_message('debug', "changing the db prefix to {$this->settings->test_table_prefix}");
        $test_prefix = $this->settings->test_table_prefix;
        $this->db->dbprefix = $test_prefix;
    }

    private function reset_db_prefix()
    {
        log_message('debug', "changing the db prefix to {$this->old_prefix}");
        $this->db->dbprefix = $this->old_prefix;
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


}