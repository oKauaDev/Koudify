<?php

declare(strict_types=1);

namespace App;

use Routers\Routers;
use function explode;
use function fclose;
use function fgets;
use function file_get_contents;
use function fopen;
use function header;
use function http_response_code;
use function json_decode;
use function json_encode;
use function password_hash;
use function set_error_handler;
use function set_exception_handler;
use function trim;

class App {
	public function run(): void {
		// Esse código é para tratar os erros melhor.
		set_error_handler([$this, 'errorHandler']);

		set_exception_handler(function($exception) {
			$error = [
				'message' => $exception->getMessage(),
				'code' => $exception->getCode(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
			];
			echo json_encode($error);
			header('Content-Type: application/json');
			http_response_code(500);
		});

		// Configurando as configurações.
		$KD_SETTINGS = json_decode(file_get_contents("../settings.json"), true);

		header("Content-Type: application/json");

		$this->loadEnv("../.env");

		// Criptografando o token ADMIN
		$options = [
			'memory_cost' => 1024,
			'time_cost' => 4,
			'threads' => 2
		];

		$GLOBALS["ADMIN_TOKEN"] = password_hash($_ENV["ADMIN_TOKEN"], PASSWORD_ARGON2ID, $options);

		// carregar todas as classes do projeto.
		$files = ["Routers/Routers.php", "Controllers/ControllerBase.php", "Security/BSP.php", "Security/JKT.php", "Security/PEK.php", "Database/Mysql.php", "Requests/RequestLimit.php", "CORS/CORS.php"];

		foreach ($files as $file) {
			require $file;
		}

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
		//Executando as rotas
		$routers->exec($url);
	}

	public function loadEnv(string $envPath): void {
		$env = [];
		$handle = fopen($envPath, "r");

		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				$line = trim($line);
				if ($line && $line[0] !== "#") {
					$parts = explode("=", $line, 2);
					$env[$parts[0]] = isset($parts[1]) ? $parts[1] : "";
				}
			}
			fclose($handle);
		}

		foreach ($env as $key => $value) {
			$_ENV[$key] = $value;
		}
	}

	public function errorHandler($errno, $errstr, $errfile, $errline) {
		http_response_code(500);
		header('Content-Type: application/json');
		echo json_encode(
			[
				'message' => $errstr,
				'code' => $errno,
				'file' => $errfile,
				'line' => $errline
			]
		);
	}
}

$app = new App();
$app->run();