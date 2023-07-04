<?php

declare(strict_types=1);

// Recomendamos que a namespace seja o nome do plugin
namespace Teste;

// A classe principal do plugin deve ser chamada de Main
// Você pode "renomear" ela assim que importar o plugin
class Main {
	// após isso, o plugin pode ter vários arquivos.
	// basta criá-los aqui, não é necessário importá-los com include ou require
	// pois eles já são importados automaticamente
	public function getMessage(): string {
		return "Olá mundo";
	}
}
