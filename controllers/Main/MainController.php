<?php

declare(strict_types=1);

use Controllers\ControllerBase;
use Teste\Main as Teste;

class MainController extends ControllerBase {

	/**
	 * Método chamado assim que o Controller é chamado.
	 */
	public function onLoad(): void {
		// Carregar os plugins
		$this->loadPlugins(["Teste"]);
	}

	/**
	 * Método chamado assim que um plugin é carregado.
	 */
	public function onPluginLoad(string $name, bool $status): void {
		// recomendamos utilizar switch.
		switch ($name) {
			case 'Teste':
				// Lembre-se que é nescessário criar a classe, o loadPlugins apenas importa o plugin.
				$plugin = new Teste();
				$this->output($plugin->getMessage());
				break;
		}
	}
}