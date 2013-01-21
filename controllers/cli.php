<?php 

class Cli extends Public_Controller
{
    private $shortops = "c::m::f::";
    private $longops = array(
        "help",
        "xml"
    );

    public function __construct()
    {
        if(!$this->input->is_cli_request()){
            exit('Sorry, this controller can only be accessed by the command line');
        }
        $this->load->model('test_suite_m');
        $this->template->set_layout(FALSE);
        parent::__construct();
    }
    //TODO refactor into helper functions.
    public function index()
    {
        $options = getopt($this->shortops, $this->longops);
        $used_options = array_keys($options);
        //Ignore everything and just render help
        if(in_array('help',$used_options)){
            $this->help();
            exit(0);
        }
        //Get the module to test
        //TODO: to many embedded if statements. Refactor.
        if(in_array('m', $used_options)){
            //make sure that there is a m argument
            if(!$options['m']){
                exit("There's no module argument. Please use -m<module_name> instead of -m <module_name>\n");
            }
            if(in_array('c', $used_options)){
                if(!$options['c']){
                    exit("There's no class argument. Please use -m<module_name> and -c<class_name>\n");
                }
                $module_tests = $this->test_suite_m->get_modules($options['m'], $options['c']);
            }
            else{
                $module_tests= $this->test_suite_m->get_modules($options['m']);
            }
            //We had a bad module or class arguement
            if(empty($module_tests)){
                exit("Unable to find tests for the module or class.\n Please make sure you're using -m<module_name> instead of -m <module_name>\n");
            }
        }
        //Test ALL the modules!
        else{
            $module_tests= $this->test_suite_m->get_modules();
        }
        //output to a file
        $file_writer = False;
        if(in_array('f', $used_options)){
            $file_writer = True;
        }
        //add the tests
        foreach($module_tests as $module){
            $name = $module['name'];
            foreach($module['tests'] as $test){
                $this->test_suite_m->add_test( 
                    array(
                        'module' => $name,
                        'class' => $test['class'],
                        'method' => $test['method']
                    )
                ); 
            }
        }
        $this->test_suite_m->run_tests();
        $results = $this->test_suite_m->get_results();
        $this->template->results = $results;
        $this->template->set_layout(FALSE);
        //pick which view we're going to use.
        if(in_array('xml',$used_options)){
            $output = $this->load->view('xml_report.php',
                                        array('results' => $results),
                                        $file_writer);
        }
        else{
            $output = $this->template->build('cli_report.php','',$file_writer);
        }
        //Write the file if requested.
        if($file_writer){
            //Change the directory to the same one the client is in.
            chdir($GLOBALS['pyrotoast_client_path']);
            $file = @fopen($options['f'], "w");
            if(!$file){
                error_log("Unable to open the file {$options['f']} for output. Are you sure
                your webserver has access to the file? ");
                die(1);
            }
            $file_write_result = fwrite($file, $output);
            fclose($file);
        }
    }
    public function help()
    {
        echo "Usage:  client.php\n"; 
        echo "\tclient.php [options]\n";
        echo "\tclient.php [options] -f<file>\n";
        echo "\n";
        echo "Options: ";
        echo "\n";
        echo "\t-f<file> writes output to <file>\n";
        echo "\t-m<module> module to run tests for \n";
        echo "\t-c<class> runs the test class (requires the -m argument) \n";
        echo "\t--xml output in a JUnit format\n";
        echo "\t--help brings this help menu\n";
    }

}
