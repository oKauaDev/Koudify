<?php

namespace Controllers;

class HttpBase
{
  private $headers = [];

  public function setHeader($name, $value)
  {
    $this->headers[$name] = $value;
    header("$name: $value");
  }

  public function getHeaders()
  {
    return $this->headers;
  }

  public function removeHeader($name)
  {
    if (isset($this->headers[$name])) {
      unset($this->headers[$name]);
      header_remove($name);
    }
  }

  public function request($url, $method = "GET", $data = [], $headers = [])
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (!empty($headers)) {
      $headers = array_merge($this->headers, $headers);
    } else {
      $headers = $this->headers;
    }
    $headers_arr = [];
    foreach ($headers as $name => $value) {
      $headers_arr[] = "{$name}: {$value}";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_arr);
    if (!empty($data)) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['body' => $response, 'http_code' => $http_code];
  }
}