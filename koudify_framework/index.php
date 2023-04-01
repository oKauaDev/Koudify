<?php

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
// Configurando as configurações.
$KD_SETTINGS = json_decode(file_get_contents("../settings.json"), true);

$GLOBALS["mysql"] = [
  "host" => $_ENV["MYSQL_HOST"],
  "user" => $_ENV["MYSQL_USER"],
  "password" => $_ENV["MYSQL_PASSWORD"]
];

$routers = new Routers();

// Pegando as rotas
foreach ($KD_SETTINGS["routers"] as $path => $controller) {
  $routers->setRouter($path, $controller);
}

$url = $_GET["url"] ?? "/";

//Executando as rotass
$routers->exec($url);