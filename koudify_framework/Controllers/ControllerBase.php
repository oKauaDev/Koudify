<?php

namespace Controllers;

use Security\BSP;
use Security\JKT;
use Security\PEK;
use Exception;

class ControllerBase
{

  public const ERROR_200_OK = 200; // OK
  public const ERROR_201_CREATED = 201; // Criado
  public const ERROR_204_NO_CONTENT = 204; // Sem conteúdo
  public const ERROR_400_BAD_REQUEST = 400; // Requisição inválida
  public const ERROR_401_UNAUTHORIZED = 401; // Não autorizado
  public const ERROR_403_FORBIDDEN = 403; // Proibido
  public const ERROR_404_NOT_FOUND = 404; // Não encontrado
  public const ERROR_405_METHOD_NOT_ALLOWED = 405; // Método não permitido
  public const ERROR_409_CONFLICT = 409; // Conflito
  public const ERROR_500_INTERNAL_SERVER_ERROR = 500; // Erro interno do servidor

  private ?string $token = null;
  private ?array $body = null;
  private ?array $params = null;
  private array $plugins = [];
  private ?array $security = [];

  public function __construct(
    string $token = null,
    array $body = null,
    array $params = null
  ) {
    $this->token = $token;
    $this->body = $body;
    $this->params = $params;

    $this->security = [
      "BSP" => new BSP(),
      "JKT" => new JKT(),
      "PEK" => new PEK(),
    ];
  }

  /**
   * @return void
   */
  public function onLoad(): void
  {
    // Essá função será chamada quando a classe for construída.
  }

  public function onPluginLoad(string $plugin, bool $status): void
  {
    // Essa função será executada quando uma lib for carregada
  }

  /**
   * @param mixed $name
   * @param bool $json
   * 
   * @return void
   */
  public function output(mixed $message, bool $json = false): void
  {
    if (is_array($message)) {
      $message = json_encode($message);
    }
    echo $message . PHP_EOL;
  }

  /**
   * @param int $error
   * @param mixed $log
   * @param bool $exit
   */
  public function feedback(
    int $error = 404,
    mixed $log = "",
    bool $exit = true
  ): void {
    http_response_code($error);
    echo $log;
    if ($exit)
      exit;
  }

  /**
   * @return string
   */
  public function getToken(): string
  {
    return $this->token;
  }

  /**
   * @return array
   */
  public function getBody(): array
  {
    return $this->body;
  }

  /**
   * @return array
   */
  public function getParams(): array
  {
    return $this->params;
  }

  /**
   * @return array
   */
  public function getRequestGetParams(): array
  {
    // Clonando a array _GET
    $get = [...$_GET];
    // Retirar a url padrão
    unset($get["url"]);
    // retornando
    return $get;
  }

  /**
   * @return string
   */
  public function getURL(): string
  {
    return $_GET["url"] ?? "/";
  }

  public function getBaseSecurityPractices(): BSP
  {
    return $this->security["BSP"];
  }

  public function getBSP(): BSP
  {
    return $this->getBaseSecurityPractices();
  }

  public function getJsonKoudifyToken(): JKT
  {
    return $this->security["JKT"];
  }

  public function getJKT(): JKT
  {
    return $this->getJsonKoudifyToken();
  }

  public function getPasswordEncriptionKoudify(): PEK
  {
    return $this->security["PEK"];
  }

  public function getPEK(): PEK
  {
    return $this->getPasswordEncriptionKoudify();
  }

  /**
   * @param array $plugins
   * 
   * @return void
   */
  public function loadPlugins(array $plugins = []): void
  {
    // Da um foreach nas plugins
    foreach ($plugins as $lib) {
      $controllersDir = realpath('../plugins/');
      $lib_main_file = $controllersDir . "/" . $lib . "/Main.php";
      // Verifica se a LIB existe
      if (file_exists($lib_main_file)) {
        // Incluindo o arquivo
        include_once $lib_main_file;
        $this->plugins[] = $lib;
        $this->onPluginLoad($lib, true);
      } else {
        $this->onPluginLoad($lib, false);
        throw new Exception("O plugin \"{$lib}\" não foi instalada.");
      }
    }
  }

  function setCookie($name, $value, $expiry = 0, $path = "/", $domain = "", $secure = false, $httponly = true)
  {
    if ($expiry === 0) {
      $expiry = time() + (10 * 365 * 24 * 60 * 60); // 10 anos
    } else {
      $expiry = time() + $expiry;
    }

    setcookie($name, $value, $expiry, $path, $domain, $secure, $httponly);
  }

  function getCookie($name)
  {
    if (isset($_COOKIE[$name])) {
      return $_COOKIE[$name];
    }
    return null;
  }

  function deleteCookie($name, $path = "/", $domain = "")
  {
    setcookie($name, "", time() - 3600, $path, $domain);
    unset($_COOKIE[$name]);
  }


  /**
   * @return array
   */
  public function getLoadedPlugins(): array
  {
    return $this->plugins;
  }

  /**
   * @return string|array|null
   */
  public function getAuthenticationCode(): string|array|null
  {
    $headers = $this->getAuthorizationHeader();
    if (!empty($headers)) {
      if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
      }
    }
    return null;
  }

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
}