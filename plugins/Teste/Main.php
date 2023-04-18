<?php

declare(strict_types=1);

// Recomendamos que a namespace seja o nome do plugin
namespace Teste;

// A classe principal do plugin deve ser chama de Main
// Você pode "renomear" ela assim que importar o plugin
class Main {
	// após isso o plugin pode ter vários arquivos.
	// basta crialos aqui, não é necessário importalos com include ou requir
	// pois eles já são importados altomaticamente
	public function getMessage(): string {
		return "Olá mundo";
	}
}