<?php
class UserToken
{
	private string $username;
	private string $key; // clé alphabétique

	public function __construct(string $username, string $key)
	{
		$this->username = $username;
		$this->key = strtoupper($key);
	}

	/**
	 * Retourne la clé alphabétique
	 */
	public function get_key(): string
	{
		return $this->key;
	}

	public function __toString(): string
	{
		return $this->username . ' [' . $this->key . ']';
	}

	/**
	 * Génère le token numérique à partir de la clé
	 */
	public function generate_token(): string
	{
		$token = '';
		for ($i = 0; $i < strlen($this->key); $i++) {
			$char = $this->key[$i];
			if ($char >= 'A' && $char <= 'Z') {
				$token .= (ord($char) - 64); // A=1, B=2, ..., Z=26
			}
		}
		return $token;
	}

	/**
	 * Vérifie si un token donné est valide
	 */
	public function validate_token(string $token): bool
	{
		return $token === $this->generate_token();
	}

	/**
	 * Génère une clé alphabétique aléatoire (A-Z uniquement)
	 */
	public static function generate_random_key(int $length = 7): string
	{
		$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$key = '';
		for ($i = 0; $i < $length; $i++) {
			$key .= $letters[random_int(0, 25)];
		}
		return $key;
	}

	/**
	 * Retourne le nom d'utilisateur
	 */
	public function get_username(): string
	{
		return $this->username;
	}
}
