<?php

declare(strict_types=1);

function loadEnv($envPath) {
	$env = [];
	$handle = fopen($envPath, "r");

	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			$line = trim($line);
			if ($line && $line[0] !== "#") {
				$parts = explode("=", $line, 2);
				$env[$parts[0]] = isset($parts[1]) ? $parts[1] : "";
			}
		}
		fclose($handle);
	}

	foreach ($env as $key => $value) {
		$_ENV[$key] = $value;
	}
}