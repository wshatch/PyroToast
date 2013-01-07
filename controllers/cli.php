<?php 

class Cli extends Public_Controller
{
    public function __construct()
    {
        if(!$this->input->is_cli_request()){
            exit('Sorry, this controller can only be accessed by the command line');
        }
        $this->template->set_layout(FALSE);
        parent::__construct();
    }
    public function help()
    {
        echo "Help called! \n";
    }
    public function test($module = 'all', $class = 'all', $method = 'all')
    {
        echo "test function called! \n";
    }
}
