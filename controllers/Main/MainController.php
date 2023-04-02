<?php

use Controllers\ControllerBase;
use Teste\Main;


class MainController extends ControllerBase
{
  /**
   * Summary of onLoad
   * @return void
   */
  public function onLoad(): void
  {
    $this->loadPlugins(["Teste"]);
    $hash = $this->getPEK()->encrypt("Kaua10052006ç");
    var_dump(["hash" => $hash, "conseguiu?" => $this->getPEK()->validate("Kaua10052006ç", $hash) ? "Sim" : "Não"]);
    // $this->feedback(self::ERROR_200_OK, "Usuário criado", true);
  }
}