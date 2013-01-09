<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Toast
 *
 * JUnit-style unit testing in CodeIgniter. Requires PHP 5 (AFAIK). Subclass
 * this class to create your own tests. See the README file or go to
 * http://jensroland.com/projects/toast/ for usage and examples.
 *
 * RESERVED TEST FUNCTION NAMES: test_index, test_show_results, test__[*]
 *
 * @package         CodeIgniter
 * @subpackage  Controllers
 * @category        Unit Testing
 * @based on        Brilliant original code by user t'mo from the CI forums
 * @based on        Assert functions by user 'redguy' from the CI forums
 * @license         Creative Commons Attribution 3.0 (cc) 2009 Jens Roland
 * @author          Jens Roland (mail@jensroland.com)
 *
 */


abstract class Toast extends Admin_Controller
{
    var $test_dir = '/test/';

    var $modelname;
    var $modelname_short;
    var $message;
    var $messages;
    var $asserts;
    var $file_data = array();

    function __construct($name)
    {
        parent::__construct();
        $this->load->library('unit_test');
        //reset the results array since it's maintained by other
        //Toast instances.
        $this->unit->results = array();
        $this->modelname = $name;
        $this->modelname_short = basename($name, '.php');
        $this->messages = array();
        $this->db->dbprefix = $this->settings->test_table_prefix;
    }

    protected function index()
    {
        $this->show_all();
    }
    protected function get_test_methods()
    {
         $class = get_class($this);
         $reflector = new ReflectionClass($class);
         $method_names = array();
         $class_name = strtolower($class);
         foreach($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method){
             //Ignore pre or post methods
             if(strtolower($method->class) == $class_name){
                 $methodNames[] = $method->name;
             }
         }
         return $method_names;
    }

    private function run_all()
    {
        foreach ($this->get_test_methods() as $method)
        {
            $this->run($method);
        }
    }

    public function run($method)
    {
        // Reset message from test
        $this->message = '';

        // Reset asserts
        $this->asserts = TRUE;

        // Run cleanup method _pre
        $this->pre();

        // Run test case (result will be in $this->asserts)
        $this->$method();

        // Run cleanup method _post
        $this->post();

        // Set test description to "model name -> method name" with links
        $this->load->helper('url');
        $test_class_segments = $this->test_dir . strtolower($this->modelname_short);
        $test_method_segments = $test_class_segments . '/' . substr($method, 5);
        $desc = anchor($test_class_segments, $this->modelname_short) . ' -> ' . anchor($test_method_segments, substr($method, 5));
  
        $this->messages[] = $this->message;

        // Pass the test case to CodeIgniter
        $this->unit->run($this->asserts, TRUE, $desc);
    }
    public function get_data()
    {
        //Since I don't know how to remove the urls from Test Name in the default unit->result,
        //we replace Test Name with the class name and method name
        $old_results = $this->unit->result();
        foreach($old_results as $result){
            $test_name = strip_tags($result['Test Name']);
            $test_data = explode('->',$test_name);
            $result['classname'] = trim($test_data[0]);
            $result['method'] = trim($test_data[1]);

            //Set the file_data to more accurate results.
            $method_id =trim('Test_' . $result['classname'].':test_'.$result['method']);
            //If there are no asserts in the method, they won't be added to the file_data.
            if(in_array($method_id, $this->file_data)){
                $result['File Name'] = $this->file_data[$method_id]['file'];
                $result['Line Number'] = $this->file_data[$method_id]['line'];
            }
            unset($result['Test Name']);
            $results[] = $result;
        }
        return array('modelname' => $this->modelname,
                     'messages' => $this->messages,
                     'results' => $results
                     );
    }

    /**
     * Remap function (CI magic function)
     *
     * Reroutes any request that matches a test function in the subclass
     * to the _show() function.
     *
     * This makes it possible to request /my_test_class/my_test_function
     * to test just that single function, and /my_test_class to test all the
     * functions in the class.
     *
     */
    private function remap($method)
    {
        $test_name = 'test_' . $method;
        if (method_exists($this, $test_name))
        {
            $this->_show($test_name);
        }
        else
        {
            $this->$method();
        }
    }


    /**
     * Cleanup function that is run before each test case
     * Override this method in test classes!
     */
    protected function pre() { }

    /**
     * Cleanup function that is run after each test case
     * Override this method in test classes!
     */
    protected function post() { }


    private function fail($message = null) {
        $this->asserts = FALSE;
        if ($message != null) {
            $this->message = $message;
        }
        return FALSE;
    }

    /** Used to gain accurate information for the file name and last assert line
     *  since taking them from the Unit Testing library points to this file.
     */
    private function file_data()
    {
        $back = debug_backtrace();
        $method_id = $back[2]['class'] . ':'. $back[2]['function'];
        $this->file_data[$method_id] = array('file' => $back[1]["file"], 
                                             'line' => $back[1]["line"]);
    }
    protected function assert_true($assertion) {
        $this->file_data();
        if($assertion) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }

    protected function assert_false($assertion) {
        $this->file_data();
        if($assertion) {
            $this->asserts = FALSE;
            return FALSE;
        } else {
            return TRUE;
        }
    }

    protected function assert_true_strict($assertion) {
        $this->file_data();
        if($assertion === TRUE) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }

    protected function assert_false_strict($assertion) {
        $this->file_data();
        if($assertion === FALSE) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }

    protected function assert_equals($base, $check) {
        $this->file_data();
        if($base == $check) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }

    protected function assert_not_equals($base, $check) {
        $this->file_data();
        if($base != $check) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }

    protected function assert_equals_strict($base, $check) {
        $this->file_data();
        if($base === $check) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }

    protected function assert_not_equals_strict($base, $check) {
        $this->file_data();
        if($base !== $check) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }

    protected function assert_empty($assertion) {
        $this->file_data();
        if(empty($assertion)) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }

    protected function assert_not_empty($assertion) {
        $this->file_data();
        if(!empty($assertion)) {
            return TRUE;
        } else {
            $this->asserts = FALSE;
            return FALSE;
        }
    }


}

// End of file Toast.php */
// Location: ./system/application/controllers/test/Toast.php */
