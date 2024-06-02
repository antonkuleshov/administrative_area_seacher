<?php

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Service\AdministrativeAreaSearcher;
use Service\EncodeFromJavaSourceCode;

require_once "vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$searchLink = $_ENV['GEOCODING_API'];
$apiKey = $_ENV['API_KEY'];

$url = $_SERVER['REQUEST_URI'];

$name = rawurldecode(substr($url, 1));

$encode = new EncodeFromJavaSourceCode();

$client = new Client();

$admAreaSearcher = new AdministrativeAreaSearcher($client, $searchLink, $apiKey);
echo $admAreaSearcher->getName($encode($name));
