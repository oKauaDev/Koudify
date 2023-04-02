<?php

namespace Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Mysql
{
  private string $host = "localhost";
  private string $user = "root";
  private string $password = "";
  private string $dbname = "";

  private ?PDO $dbh = null;

  /**
   * @param string $dbname
   */
  public function __construct(string $dbname = "")
  {
    $this->dbname = $dbname;
    $this->host = $GLOBALS["mysql"]["host"] ?? "localhost";
    $this->user = $GLOBALS["mysql"]["user"] ?? "root";
    $this->password = $GLOBALS["mysql"]["password"] ?? "";
  }

  public function connect(): self
  {
    try {
      $this->dbh = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
    } catch (PDOException $e) {
      die($e);
    }

    return $this;
  }

  /**
   * @param string $query
   * @param array $params
   */
  public function execute(string $query, array $params = []): PDOStatement|bool|null
  {
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
      throw new Exception($e);
    }
    return false;
  }

  /**
   * @param PDOStatement $stmt
   * @param int $mode
   * @param int|null $cursorOrientation
   * @param int|null $cursorOffset
   */
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
   * @param PDOStatement $stmt
   * @param int $mode
   */
  public function fecthAll(
    string $query,
    array $params = [],
  ): array {
    $stmt = $this->execute($query, $params);
    return $stmt->fetchAll();
  }

  public function close(): void
  {
    $this->dbh = null;
  }

  public function hasErrors(): bool
  {
    return $this->dbh->errorCode() !== '00000';
  }

  public function getErrorInfo(): array
  {
    return $this->dbh->errorInfo();
  }

  public function getServerVersion(): string
  {
    return $this->dbh->getAttribute(PDO::ATTR_SERVER_VERSION);
  }

  public function getPDO(): ?PDO
  {
    return $this->dbh;
  }

  /**
   * @param string $host
   */
  public function setHost(string $host)
  {
    $this->host = $host;
  }

  /**
   * @param string $user
   */
  public function setUser(string $user)
  {
    $this->user = $user;
  }

  /**
   * @param string $password
   */
  public function setPassword(string $password)
  {
    $this->password = $password;
  }

  /**
   * @param string $dbname
   */
  public function setDBname(string $dbname)
  {
    $this->dbname = $dbname;
  }
}