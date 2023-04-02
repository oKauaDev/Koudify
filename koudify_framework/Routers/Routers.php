<?php

namespace Routers;

use Exception;

class Routers
{
  private string $base = "";
  public array $routes = [];

  /**
   * @param string $base
   */
  public function __construct(string $base = "")
  {
    $this->base = $base;
  }

  /**
   * @param string $url
   * @param callable $callback
   */
  public function setRouter(
    string $url,
    array $settings
  ): void {
    $url = rtrim($this->base, "/") . "/" . ltrim($url, "/");
    $this->routes[$url] = $settings;
  }

  public function exec(string $url): bool
  {
    // Carrega todos os controllers.
    $controllersDir = realpath('../controllers/');

    // Pegando o body
    $method = $_SERVER['REQUEST_METHOD'] ?? "GET";
    $body = [];
    if ($method === "POST" or $method === "PUT") {
      $body = (array) json_decode(file_get_contents("php://input"), true);
    }

    $url = rtrim($this->base, "/") . "/" . ltrim($url, "/");
    if (isset($this->routes)) {
      foreach ($this->routes as $route => $urlSettings) {
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
          if (in_array($method, $requests)) {
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
            $class = new $urlSettings["class"]($token, $body, $array_params);
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

  /**
   * @param string $route
   * @return array
   */
  private function getRouteParts(string $route): array
  {
    $parts = explode('/', $route);
    $route_parts = [];
    foreach ($parts as $part) {
      if (strpos($part, ':') === 0) {
        $route_parts[] = str_replace(':', "", $part);
      }
    }

    return $route_parts;
  }

  /**
   * @return null|string
   */
  private function getAuthorizationHeader(): ?string
  {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
      $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
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

  /**
   * @return string|array|null
   */
  private function getBearerToken(): string|array|null
  {
    $headers = $this->getAuthorizationHeader();
    if (!empty($headers)) {
      if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
      }
    }
    return null;
  }
}