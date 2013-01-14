<?php
$test_count = 0;
$pass_count = 0;
$fail_count = 0;
foreach($results as $class=>$data){
    foreach($data['results'] as $result){
        $test_count++;
        if($result['Result'] === "Failed"){
            var_dump($result);
            $fail_count++;
            echo "Test {$result['classname']}->{$result['method']} has failed! \n";
            echo " On line {$result['Line Number']} in file {$result['File Name']}\n";
            echo "\n";
        }
        else{
            $pass_count++;
        }
    }
}

echo count($results) . ' test classes with ' . $test_count . " tests ran. \n"; 
echo "$pass_count passes, $fail_count failures. \n ";
