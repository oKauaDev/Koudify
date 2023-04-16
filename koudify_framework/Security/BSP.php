<?php

declare(strict_types=1);

//BSP = Basic security practices

namespace Security;

use function bin2hex;
use function hash_equals;
use function htmlentities;
use function htmlspecialchars;
use function implode;
use function preg_match;
use function random_bytes;
use function stripos;

class BSP {
	public function check_sql_injection(string $sql): bool {
		$keywords = ['ALTER', 'CREATE', 'DELETE', 'INSERT', 'SELECT', 'UPDATE'];

		$blacklist = ['=', ';', '--', '/*', '*/', 'UNION'];

		preg_match('/\b(' . implode('|', $keywords) . ')\b\s+/i', $sql, $matches);
		if (!empty($matches)) {
			return true;
		}

		foreach ($blacklist as $badChar) {
			if (stripos($sql, $badChar) !== false) {
				return true;
			}
		}

		return false;
	}

	public function sanitize_input(string $input): string {
		return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	public function sanitize_output(string $output): string {
		return htmlentities($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	public function generate_csrf_token(): string {
		return bin2hex(random_bytes(32));
	}

	public function validate_csrf_token(string $token, string $session_token): bool {
		return hash_equals($token, $session_token);
	}
}