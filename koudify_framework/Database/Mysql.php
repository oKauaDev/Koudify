<?php

declare(strict_types=1);

namespace Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use function json_encode;

class Mysql {
	private string $host = "localhost";
	private string $user = "root";
	private string $password = "";
	private string $dbname = "";

	private ?PDO $dbh = null;

	public function __construct(string $dbname = "") {
		$this->dbname = $dbname;
		$this->host = $GLOBALS["mysql"]["host"] ?? "localhost";
		$this->user = $GLOBALS["mysql"]["user"] ?? "root";
		$this->password = $GLOBALS["mysql"]["password"] ?? "";
	}

	public function connect(): self {
		try {
			$this->dbh = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
			$this->dbh->exec("set names utf8mb4");
		} catch (PDOException $e) {
			$error = [
				"file" => $e->getFile(),
				"line" => $e->getLine(),
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
			];
			die(json_encode($error));
		}

		return $this;
	}

	public function execute(string $query, array $params = []): PDOStatement|bool|null {
		if ($this->dbh === null) {
			throw new Exception("Database connection not established");
		}

		try {
			if ($stmt = $this->dbh->prepare($query)) {
				foreach ($params as $key => &$value) {
					$stmt->bindValue($key, $value);
				}

				$stmt->execute();
				return $stmt;
			}
		} catch (PDOException $e) {
			throw new Exception($e->getMessage());
		}
		return false;
	}

	public function fecth(
		string $query,
		array $params = [],
		int $mode = PDO::FETCH_DEFAULT,
		int|null $cursorOrientation = PDO::FETCH_ORI_NEXT,
		int|null $cursorOffset = 0
	): mixed {
		$stmt = $this->execute($query, $params);
		return $stmt->fetch($mode, $cursorOrientation, $cursorOffset);
	}

	/**
	 * @param int $mode
	 */
	public function fecthAll(
		string $query,
		array $params = [],
		?int $mode = PDO::FETCH_DEFAULT
	): array {
		$stmt = $this->execute($query, $params);
		return $stmt->fetchAll($mode);
	}

	public function close(): void {
		$this->dbh = null;
	}

	public function hasErrors(): bool {
		return $this->dbh->errorCode() !== '00000';
	}

	public function getErrorInfo(): array {
		return $this->dbh->errorInfo();
	}

	public function getServerVersion(): string {
		return $this->dbh->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	public function getPDO(): ?PDO {
		return $this->dbh;
	}

	public function setHost(string $host) {
		$this->host = $host;
	}

	public function setUser(string $user) {
		$this->user = $user;
	}

	public function setPassword(string $password) {
		$this->password = $password;
	}

	public function setDBname(string $dbname) {
		$this->dbname = $dbname;
	}
}