<?php
//TODO: write an abstract test object.
abstract class Functional_Test
{
    public $controller;

    protected function pre(){}
    protected function post(){}
    private   function fail($message = null)
    {
    }

    protected function login($user, $password)
    {
    }

    protected function set_post($data)
    {
    }

    protected function set_get($data)
    {
    }

    protected function set_delete($data)
    {
    }

    protected function assert_contains($info)
    {
        $this->file_data();
    }

    protected function assert_response($controller, $response)
    {
        $this->file_data();
    }

    protected function assert_redirect($controller)
    {
        $this->file_data();
    }

    protected function assert_data_set($data)
    {
        $this->file_data();
    }
    protected function assert_partial_loaded($partial)
    {
        $this->file_data();
    }
    protected function assert_view_loaded($view)
    {
        $this->file_data();
    }
    protected function assert_loads_metadata($file)
    {
    }
}
