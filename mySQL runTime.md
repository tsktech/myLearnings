$result = fetchAll();
print_r($result); // [0]['name'] = srikanth
$name = array_column($result,0); // removes [0]
print_r($name); ['name'] = srikanth
