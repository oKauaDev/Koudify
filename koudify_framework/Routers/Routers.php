<?php

declare(strict_types=1);

namespace Routers;

use CORS\CORS;
use Exception;
use Requests\RequestLimit;

use function apache_request_headers;
use function array_combine;
use function array_keys;
use function array_map;
use function array_shift;
use function array_values;
use function count;
use function explode;
use function file_exists;
use function file_get_contents;
use function function_exists;
use function http_response_code;
use function in_array;
use function is_array;
use function json_decode;
use function json_encode;
use function ltrim;
use function preg_match;
use function preg_replace;
use function realpath;
use function rtrim;
use function str_replace;
use function strpos;
use function strtoupper;
use function token_get_all;
use function trim;

class Routers {
	private string $base = "";
	public array $routes = [];

	public function __construct(string $base = "") {
		$this->base = $base;
	}

	public function setRouter(
		string $url,
		array $settings
	): void {
		$url = rtrim($this->base, "/") . "/" . ltrim($url, "/");
		$this->routes[$url] = $settings;
	}

	public function exec(string $url): bool {
		// Carrega todos os controllers.
		$controllersDir = realpath('../controllers/');

		// Pegando o body
		$method = $_SERVER['REQUEST_METHOD'] ?? "GET";
		$body = [];
		if ($method === "POST" || $method === "PUT") {
			$body = (array) json_decode(file_get_contents("php://input"), true);
		}

		$url = rtrim($this->base, "/") . "/" . ltrim($url, "/");
		if (isset($this->routes)) {
			foreach ($this->routes as $route => $urlSettings) {
				$cors = new CORS($urlSettings);
				$cors->initialize();

				if (isset($urlSettings["request_limit"])) {
					$rl = $urlSettings["request_limit"];
					$request_limit = new RequestLimit($rl["seconds"] ?? 120, $rl["requestPerSecounds"] ?? 5, $rl["cooldown"] ?? 3600);
					$params = $request_limit->addRequest();
					if ($request_limit->ipInCooldown($params)) {
						$retorno = [
							"code" => 429,
							"message" => "[FRAMEWORK] You made many requests in " . ($rl["seconds"] ?? 120) . " seconds, wait " . ($rl["cooldown"] ?? 3600) . " to make a new request"
						];
						http_response_code(429);
						echo json_encode($retorno);
						return false;
					}
				}
				$file_path = $controllersDir . "/" . $urlSettings["path"] . ".php";
				if (!file_exists($file_path)) {
					throw new Exception("File $file_path not exists");
				}
				// Substitui as partes dinâmicas da rota por uma expressão regular
				$pattern = preg_replace("/:[\w]+/", "([\w-]+)", $route);
				$pattern = "/^" . str_replace("/", "\/", $pattern) . "$/";

				// Verifica se a URL bate com a rota
				if (preg_match($pattern, $url, $params)) {
					$requests = [];
					foreach ($urlSettings["request"] as $r) {
						$requests[] = strtoupper($r);
					}
					if (in_array($method, $requests, true)) {
						array_shift($params);
						$params = (array) $params;
						$routeKeys = $this->getRouteParts($route);
						$array_params = [];
						foreach ($params as $index => $param) {
							$key = $routeKeys[$index];
							$array_params[$key] = $param;
							$array_params[] = $param;
						}
						require_once($file_path);
						$token = $this->getBearerToken();

						$className = $this->get_first_class_name($file_path);

						$class = new $className($token, $body, $array_params);
						$class->onLoad();
						return !!$class;
					} else {
						$retorno = [
							"code" => 405,
							"message" => "[FRAMEWORK] This page does not receive the request method: $method"
						];
						http_response_code(405);
						echo json_encode($retorno);
					}
				}
			}
		}
		return false;
	}

	function get_first_class_name(string $filename): ?string {
		$tokens = token_get_all(file_get_contents($filename));
		foreach ($tokens as $index => $token) {
			if (is_array($token) && $token[0] === T_CLASS) {
				// Encontrou a definição de uma classe
				for ($i = $index + 1; $i < count($tokens); $i++) {
					if (is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
						// Encontrou o nome da classe
						return $tokens[$i][1];
					}
				}
				break;
			}
		}
		return null;
	}

	private function getRouteParts(string $route): array {
		$parts = explode('/', $route);
		$route_parts = [];
		foreach ($parts as $part) {
			if (strpos($part, ':') === 0) {
				$route_parts[] = str_replace(':', "", $part);
			}
		}

		return $route_parts;
	}

	private function getAuthorizationHeader(): ?string {
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	private function getBearerToken(): string|array|null {
		$headers = $this->getAuthorizationHeader();
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
}