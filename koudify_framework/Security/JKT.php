<?php

declare(strict_types=1);

// JKT = Json Koudify Token

namespace Security;

use function base64_decode;
use function base64_encode;
use function chr;
use function count;
use function explode;
use function json_decode;
use function json_encode;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function openssl_pbkdf2;
use function openssl_random_pseudo_bytes;
use function ord;
use function strlen;
use function substr;
use function time;

class JKT {
	private function getPassword(): string {
		return $_ENV["JKT_HASH_KEY"];
	}

	public function encode(
		array $payload,
		?int $exp_time = null
	): string {
		$password = $this->getPassword();

		if (!$exp_time) {
			$exp_time = time() + 86400;
		}
		//TKE = Token Koudify Encription

		$payload_string = base64_encode(json_encode($payload));
		$string_encription = $payload_string . "." . $exp_time;

		$cesar_cripto = '';
		$passwordLength = strlen($password);
		$string_encriptionLength = strlen($string_encription);

		for ($i = 0; $i < $string_encriptionLength; $i++) {
			$char = $string_encription[$i];
			$keyChar = $password[$i % $passwordLength];
			$charAscii = ord($char);
			$keyCharAscii = ord($keyChar);
			$novaPosicao = ($charAscii + $keyCharAscii) % 256;
			$cesar_cripto .= chr($novaPosicao);
		}
		// Dobrando a camada
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

		// Cria uma chave de 256 bits a partir da senha usando PBKDF2 com 10000 iterações
		$chave = openssl_pbkdf2($password, '', 32, 10000, 'sha256');

		// Criptografa a mensagem usando AES-256-CBC
		$cifra = openssl_encrypt($cesar_cripto, 'aes-256-cbc', $chave, OPENSSL_RAW_DATA, $iv);

		// Concatena o IV com a cifra
		$cifraCompleta = base64_encode($iv . $cifra);
		return $cifraCompleta;
	}

	private function descript(string $cifraCompleta): ?array {
		$password = $this->getPassword();

		// Decodifica a cifra completa da base64
		$cifraCompleta = base64_decode($cifraCompleta, true);

		// Extrai o IV e a cifra da cifra completa
		$ivLength = openssl_cipher_iv_length('aes-256-cbc');
		$iv = substr($cifraCompleta, 0, $ivLength);
		$cifra = substr($cifraCompleta, $ivLength);

		// Cria uma chave de 256 bits a partir da senha usando PBKDF2 com 10000 iterações
		$chave = openssl_pbkdf2($password, '', 32, 10000, 'sha256');

		// Descriptografa a cifra usando AES-256-CBC
		$base_decription = openssl_decrypt($cifra, 'aes-256-cbc', $chave, OPENSSL_RAW_DATA, $iv);

		// Descriptografar a cifra
		$mensagem = "";
		$senhaLength = strlen($password);
		$base_decriptionLength = strlen($base_decription);

		for ($i = 0; $i < $base_decriptionLength; $i++) {
			$char = $base_decription[$i];
			$keyChar = $password[$i % $senhaLength];
			$charAscii = ord($char);
			$keyCharAscii = ord($keyChar);
			$novaPosicao = ($charAscii - $keyCharAscii + 256) % 256;
			$mensagem .= chr($novaPosicao);
		}
		$message_decode = explode(".", $mensagem);
		if (count($message_decode) !== 2) {
			return null;
		}
		$payload = (array) json_decode(base64_decode($message_decode[0], true), true);
		$exp = $message_decode[1];

		if ($payload == null || $exp == null) {
			return null;
		}

		if ($exp < time()) {
			return null;
		}

		return [
			"payload" => $payload,
			"exp" => $exp
		];
	}

	public function valid(string $token): bool {
		$decript = $this->descript($token);
		return !!$decript;
	}

	public function getPayload(string $token): ?array {
		$decript = $this->descript($token);
		if ($decript === null) {
			return null;
		}
		return $decript["payload"];
	}
}