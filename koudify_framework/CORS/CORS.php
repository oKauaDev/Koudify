<?php

declare(strict_types=1);

namespace CORS;

use function header;
use function parse_url;

class CORS {
	private ?string $allowedDomain;

	public function __construct(array $settings) {
		$this->allowedDomain = $settings["cors"] ?? null;
	}

	public function initialize(): bool {
		if ($this->allowedDomain === null) {
			return false;
		}

		$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
		if (!empty($origin) && parse_url($origin, PHP_URL_HOST) === $this->allowedDomain) {
			header('Access-Control-Allow-Origin: ' . $origin);
			header('Access-Control-Allow-Methods: *');
			header('Access-Control-Allow-Headers: *');
			header('Access-Control-Max-Age: 86400');
			return true;
		}

		return false;
	}
}