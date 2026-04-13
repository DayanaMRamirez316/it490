<?php 
require_once __DIR__ .'/../sql/path.inc';
require_once __DIR__ .'/../sql/get_host_info.inc';
require_once __DIR__ .'/../sql/rabbitMQLib.inc';

$client = new rabbitMQClient("../sql/testRabbitMQ.ini", "testServer");
$metadataFile = __DIR__ .'/metadata.json';
if (!file_exists($metadataFile)) {
    echo "metadata.json is not found";
    exit (1);
}
$metadata = json_decode(file_get_contents($metadataFile), true);
if (!$metadata){
    echo "couldn't parse metadata.json\n";
    exit (1);
}
$request = 
[
    "type" => "deployment_metadata",
    "file_location" => $metadata["file_location"],
    "version" => $metadata["version"],
];

echo "Sending deployment metadata through RabbitMQ\n";
print_r($request);

$response = $client->send_request($request);
echo "Received response from server:\n";
print_r($response);
?>