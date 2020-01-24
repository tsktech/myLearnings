<?php
// When using prepared statements there is no official PDO feature to show you the final query string that is submitted to a database complete with the parameters you passed.

// Use this simple function for debugging. The values you are passing may not be what you expect.


//Sample query string
$query = "UPDATE users SET name = :user_name WHERE id = :user_id";
//Sample parameters
$params = [':user_name' => 'foobear', ':user_id' => 1001];

function build_pdo_query($string, $array) {
    //Get the key lengths for each of the array elements.
    $keys = array_map('strlen', array_keys($array));

    //Sort the array by string length so the longest strings are replaced first.
    array_multisort($keys, SORT_DESC, $array);
    foreach($array as $k => $v) {
        //Quote non-numeric values.
        $replacement = is_numeric($v) ? $v : "'{$v}'";

        //Replace the needle.
        $string = str_replace($k, $replacement, $string);
    }
    return $string;
}

echo build_pdo_query($query, $params);    //UPDATE users SET name = 'foobear' WHERE id = 1001


?>