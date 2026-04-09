<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function gameSearch($search){
	$env = parse_ini_file(__DIR__ . '/.env');
	if(!$env || !isset($env['RAWG_API_KEY'])){
		return array("returnCode" => 0, "message" => "API key not found in .env file.");
	}
	$apiKey = $env['RAWG_API_KEY'];
	$rawgAPIurl = "https://api.rawg.io/api/games?key=$apiKey&search=" . urlencode($search) . "&page_size=30";

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $rawgAPIurl);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);

	$response = curl_exec($curl);

	if(curl_errno($curl)){
		$error = curl_error($curl);
		curl_close($curl);
		return array("returnCode" => 0, "message" => "Curl error: " . $error );
	}

	curl_close($curl);

	$apiGameData = json_decode($response, true);

	if( !apiGameData || !isset($apiGameData['results'])){
		return array("returnCode" => 0, "message" => "No results found");
	}
}

function gameDetails($gameId){
	$env = parse_ini_file(__DIR__ . '/.env');

	if(!$env || !isset($env['RAWG_API_KEY'])){
		return array("returnCode" => 0, "message" => "API key not found in .env file");
	}

	$apiKey = $env['RAWG_API_KEY'];

	$rawgAPIurl = "https://api.rawg.io/api/games/$gameId?key=$apiKey";
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $rawgAPIurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

	$response = curl_exec($curl);

	if(curl_errno($curl)){
		$error = curl_error($curl);
		curl_close($curl);
		return array("returnCode" => 0, "message" => "Curl error: " . $error );
	}

	curl_close($curl);

	$apiGameData = json_decode($response, true);

	if(!$apiGameData || isset($apiGameData['detail'])){
		return array("returnCode" => 0, "message" => "No results found");
	}

	echo "Details found \n";

	return array("returnCode" => 1, "game" => $apiGameData );
}

function gameGenre($genre){
	$env = parse_ini_file(__DIR__ . '/.env');

        if(!$env || !isset($env['RAWG_API_KEY'])){
                return array("returnCode" => 0, "message" => "API key not found in .env file");
        }

        $apiKey = $env['RAWG_API_KEY'];

        $rawgAPIurl = "https://api.rawg.io/api/games?key=$apiKey&genres=$genre&page_size=100&ordering=-rating";
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $rawgAPIurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($curl);

        if(curl_errno($curl)){
                $error = curl_error($curl);
                curl_close($curl);
                return array("returnCode" => 0, "message" => "Curl error: " . $error );
        }

        curl_close($curl);

	$rawgAPIdata = json_decode($response, true);

	if(!$rawgAPIdata || !isset($rawgAPIdata['results'])){
                return array("returnCode" => 0, "message" => "No results found");
        }

        echo "Details found \n";
        return array("returnCode" => 1, "genre" => $rawgAPIdata['results']);

}

function requestProcessor($request){
	switch($request['type']){
	case "listGames":
		return gameSearch($request['search']);
	case "details":
		return gameDetails($request['gameId']);
	case "recomendGenre": 
		return gameGenre($request['genre']);
	}
	return array("returnCode" => 0, "message" => "Unknown request type");
}

$server = new rabbitMQServer("dmz.ini", "testServer");
$server->process_requests('requestProcessor');

?>
