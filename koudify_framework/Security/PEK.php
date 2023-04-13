<?php

// PEK = Password Encription Koudify

namespace Security;

class PEK
{
  private const HASH_ALGORITHM = PASSWORD_ARGON2ID;
  private const HASH_MEMORY_COST = PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
  private const HASH_TIME_COST = PASSWORD_ARGON2_DEFAULT_TIME_COST;
  private const HASH_THREADS = PASSWORD_ARGON2_DEFAULT_THREADS;
  private const SALT_LENGTH = 1024;

  public function encrypt(string $password): ?string
  {
    // Generate a random salt
    $salt = bin2hex(random_bytes(self::SALT_LENGTH));

    // Concatenate the salt with the password
    $saltedPassword = $salt . $password;

    $options = [
      'memory_cost' => self::HASH_MEMORY_COST,
      'time_cost' => self::HASH_TIME_COST,
      'threads' => self::HASH_THREADS
    ];

    // Hash the salted password
    $hashedPassword = password_hash($saltedPassword, self::HASH_ALGORITHM, $options);

    if (!$hashedPassword) {
      return null;
    }

    // Return the hashed password with the salt appended to it
    return $hashedPassword . '@!@' . $salt;
  }

  public function validate(string $password, string $hash): bool
  {
    // Split the hash into the hashed password and the salt
    $parts = explode('@!@', $hash);

    if (count($parts) !== 2) {
      return false;
    }

    $hashedPassword = $parts[0];
    $salt = $parts[1];

    // Concatenate the salt with the password
    $saltedPassword = $salt . $password;

    // Verify the hashed password against the salted password
    return password_verify($saltedPassword, $hashedPassword);
  }
}