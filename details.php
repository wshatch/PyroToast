<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_PyroToast extends Module {

    public $version = '0.1';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'PyroToast'
            ),
            'description' => array(
                'en' => 'A port of the Toast testing library to PyroCMS '
            ),
            'frontend' => TRUE,
            'backend' => TRUE,
            'menu' => 'content', 
        );
    }

    public function install()
    {
        return TRUE;
    }

    public function uninstall()
    {
        return TRUE;
    }


    public function upgrade($old_version)
    {
        // Your Upgrade Logic
        return TRUE;
    }

    public function help()
    {
        // Return a string containing help info
        // You could include a file and return it here.
        return "No documentation has been added for this module.<br />Contact the module developer for assistance.";
    }
}
/* End of file details.php */
