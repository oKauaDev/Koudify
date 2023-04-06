<?php

// Configurando as configurações.
$KD_SETTINGS = json_decode(file_get_contents("../settings.json"), true);
header("Content-Type: application/json");

// Solução para o CORS
if ($KD_SETTINGS["cors"]["enable"] ?? false) {
  header('Access-Control-Allow-Origin: ' . implode(", ", $KD_SETTINGS["cors"]["domains"] ?? []));
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    header("Access-Control-Max-Age: 86400");
    exit(0);
  }
}
use Routers\Routers;

require("env.php");
loadEnv("../.env");

// carregar todas as classes do projeto.
require("Routers/Routers.php");
require("Controllers/ControllerBase.php");
require("Security/BSP.php");
require("Security/JKT.php");
require("Security/PEK.php");
require("Database/Mysql.php");

$GLOBALS["mysql"] = [
  "host" => $_ENV["MYSQL_HOST"],
  "user" => $_ENV["MYSQL_USER"],
  "password" => $_ENV["MYSQL_PASSWORD"]
];

$routers = new Routers();

// Pegando as rotas
foreach ($KD_SETTINGS["routers"] as $url => $settings) {
  $routers->setRouter($url, $settings);
}

$url = $_GET["url"] ?? "/";
//Executando as rotass
$routers->exec($url);