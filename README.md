PyroToast: Easy testing for PyroCMS modules
===========================================

PyroToast is a unit testing module roughly based on the Toast library for codeigniter. Running unit tests can be done through the admin section of your website. It is meant to be deployed in 
development instances and should not be deployed.

Writting Tests
==============
Tests are stored in a directory \<my module\>/tests. To write a test, simply extend the Toast class in a file in your module's test directory like so:

        <?php
        $path = 'modules/pyrotoast/controllers/test/toast.php'
        if(file_exists(ADDONPATH.$path)){
            require_once(ADDONPATH.$path);
        }
        else{
            require_once(SHARED_ADDONPATH.$path);
        }
        class Test_Some_Model_m extends Toast
        {
            public function test_true(){
                $this->assert_true(TRUE);
            }
        }
For more documentation on asserts, [see Toast's library](http://jensroland.com/projects/toast)

Running Tests
=============
There are two ways of running unit tests
1. Through the control panel at http://yourpyrocmsinstance.com/admin/pyrotoast (or just use the admin navigation). Just select which tests you want to run and click "Activate." You'll then see the results for your unit tests.
2. The client program. This is currently somewhat unstable, but running client.php from the command line will allow you to see your tests on the console. It can also output to a JUnit friendly format with the --xml argument. Run --help for more information.

Questions
=========
If you have a bug, question, suggestion, or having any issues running and installing this module, please create an issue in github. This module is currently in somewhat heavy development and bugs
are to be expected.

License
=======
Licensed with the MIT License.

Copyright (c) 2013 William Hatch 

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
