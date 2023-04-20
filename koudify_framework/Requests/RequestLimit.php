<?php

declare(strict_types=1);

namespace Requests;

use function end;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function hash;
use function implode;
use function is_int;
use function mkdir;
use function substr;
use function time;
use function trim;
use function unlink;

class RequestLimit {
	private const FOLDER = __DIR__ . "/.requests";
	private const SECRET_KEY = "ÇPAD-&9AW-§AWD-°WDA";

	private int $seconds = 120;
	private int $requestPerSecounds = 5;
	private int $cooldown = 3600;

	public function __construct(
		int $seconds = 120,
		int $requestPerSecounds = 5,
		int $cooldown = 3600
	) {
		$this->seconds = $seconds;
		$this->requestPerSecounds = $requestPerSecounds;
		$this->cooldown = $cooldown;

		if (!file_exists(self::FOLDER)) {
			mkdir(self::FOLDER, 0777, true);
		}
	}

	public function addRequest(): array {
		$this->delete_old_requests();
		$ip = $this->getClientIp();
		$hashed_ip = substr(hash('sha256', $ip . self::SECRET_KEY), 0, 20);
		$file = self::FOLDER . "/" . $hashed_ip . '.rl';

		$fileParams = [];

		if (file_exists($file)) {
			$fileParams = $this->rl_read($file);
			if (!$this->ipInCooldown($fileParams)) {
				$fileParams["cooldown"] = false;
				if (isset($fileParams["requests"])) {
					$fileParams["requests"]++;
				} else {
					$fileParams["requests"] = 1;
				}

				// Verificar se deu o número de requisições no número de segundos.
				$time = $fileParams["lastRequest"] + $this->seconds;

				if ($time > time()) {
					if ($fileParams["requests"] >= $this->requestPerSecounds) {
						if (isset($fileParams["cooldown"]) && !is_int($fileParams["cooldown"])) {
							$fileParams["cooldown"] = time() + $this->cooldown;
						}
						$fileParams["requests"] = 1;
					}
				} else {
					$fileParams["requests"] = 1;
				}

				$fileParams["lastRequest"] = time();
			}
		} else {
			$fileParams["requests"] = 1;
			$fileParams["lastRequest"] = time();
			$fileParams["cooldown"] = false;
		}

		$this->rl_write($file, $fileParams);

		return $fileParams;
	}

	public function ipInCooldown($fileParams) {
		if (!empty($fileParams)) {
			$ip = $this->getClientIp();
			$hashed_ip = substr(hash('sha256', $ip . self::SECRET_KEY), 0, 20);
			$file = self::FOLDER . "/" . $hashed_ip . '.rl';

			if (file_exists($file)) {
				if (isset($fileParams["cooldown"]) && is_int((int) $fileParams["cooldown"])) {
					return $fileParams["cooldown"] > time();
				}
			}
		}
		return false;
	}

	private function delete_old_requests() {
		$files = glob(self::FOLDER . '/*');
		$time_threshold = time() - 1800; // 1800 segundos = 30 minutos

		foreach ($files as $file) {
			$file_data = $this->rl_read($file);
			$last_request = $file_data['lastRequest'];

			if ($last_request < $time_threshold) {
				unlink($file);
			}
		}
	}

	private function getClientIp(): ?string {
		$ip = null;
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip = trim(end($ipList));
		} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return (string) $ip;
	}

	private function rl_read(string $arquivo) {
		$conteudo = file_get_contents($arquivo);
		$linhas = explode("\n", $conteudo);
		$informacoes = [];
		foreach ($linhas as $linha) {
			$partes = explode("=>", $linha);
			$informacoes[$partes[0]] = $partes[1];
		}
		return $informacoes;
	}

	private function rl_write(string $arquivo, array $dados) {
		$linhas = [];

		foreach ($dados as $chave => $valor) {
			$linhas[] = $chave . "=>" . $valor;
		}

		$conteudo = implode("\n", $linhas);

		file_put_contents($arquivo, $conteudo);
	}
}