<?php 

class Cli extends Public_Controller
{
    private $shortops = "c::m::";
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
    public function index()
    {
        $options = getopt($this->shortops, $this->longops);
        $used_options = array_keys($options);
        //Ignore everything and just render help
        if(in_array('help',$used_options)){
            $this->help();
        }
        //Get the module to test
        if(in_array('m', $used_options)){
            //Check to see if there's a valid class
            if(!in_array('c', $used_options)){
                $module_tests = $this->test_suite_m->get_modules($options['m'], $options['c']);
            }
            else{
                $module_tests= $this->test_suite_m->get_modules($options['m']);
            }
        }
        //Test ALL the modules!
        else{
            $module_tests= $this->test_suite_m->get_modules();
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
        $this->template->results = $this->test_suite_m->get_results();
        $this->template->set_layout(FALSE);

        //pick which view we're going to use.
        if(in_array('xml',$used_options)){
            $this->template->build('xml_report.php');
            return;
        }
        $this->template->build('cli_report.php');
    }
    public function help()
    {
        //TODO: replace this with a help view
        echo "Help called! \n";
    }

    public function test()
    {
    }
}
