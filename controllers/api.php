<?php
require_once(ROOT . 'classes/usertoken.php');

class Api extends Core\Controller
{
	public bool $is_api_call = false; // ✅ déclaration explicite

	public function __construct()
	{
		parent::__construct(DEFAULTDATABASE, 'php', 'api');

		if (!isset($_SESSION['loggedin']) && !isset($_SERVER['HTTP_USER_AGENT'])) {
			header('Location: ' . WEBROOT . 'login');
			exit();
		}
	}

	public function index()
	{
		parent::index();
	}

	public function get($url)
	{
		$tokenObj = $this->authenticate_user_api($url);
		$username = $tokenObj->get_username(); 
		$this->DB->connect(DATADIRECTORY, $username, 'php');

		$table = $line = $column = $field = $op = $value = null;

		foreach ($url as $segment) {
			if (preg_match('/^t(\d+)$/', $segment, $m)) $table = (int) $m[1];
			if (preg_match('/^l(\d+)$/', $segment, $m)) $line  = (int) $m[1];
			if (preg_match('/^c(\d+)$/', $segment, $m)) $column = (int) $m[1];
		}

		// 📌 Requête WHERE
		if (($where = array_search('where', $url)) !== false) 
		{
			$field = $this->sanitize_field($url[$where + 1] ?? '');
			$op    = strtoupper($this->sanitize_segment($url[$where + 2] ?? '=='));
			$value = $this->sanitize_value($url[$where + 3] ?? '');

			// 🔄 Convertit _ ou - en espace (utile pour LIKE ou valeurs avec espace)
			$value = str_replace(['_', '-'], ' ', $value);

			// 🔍 Validation pour BETWEEN et LIST
			if (in_array($op, ['BETWEEN', 'LIST']) && !str_contains($value, ',')) {
				http_response_code(400);
				echo json_encode(['error' => "L’opérateur $op requiert des valeurs séparées par des virgules."]);
				exit;
			}
			$json = $this->export_where($table, $field, $op, $value);
		} 
		else 
		{
			$json = $this->export_json($table, $line, $column);
		}

		// 🧪 Mode RAW : si extension .raw ou ?raw=true
		if (isset($_GET['raw']) || in_array('raw', $url) || str_ends_with(end($url), '.raw')) {
			$decoded = json_decode($json, true);
			if (isset($decoded['data']) && is_scalar($decoded['data']))
			{
				header('Content-Type: text/plain; charset=utf-8');
				echo $decoded['data'];
				exit;
			} else {
				http_response_code(400);
				echo "No raw-compatible data found.";
				exit;
			}
		}
		// 🧠 Réponse JSON directe si demandé (curl, rawjson, API headers)
		if (!headers_sent() && $this->is_json_request()) {
			header('Content-Type: application/json; charset=utf-8');
			echo $json;
			exit;
		}

		// 🖼️ Interface HTML
		$this->data['json']    = $json;
		$this->data['content'] = $this->Template->load('response', $this->data, true);
		$this->Template->load('layout', $this->data);
	}


	private function is_json_request(): bool
	{
		return (
			($this->is_api_call ?? false) || // cas API token
			isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json') ||
			isset($_GET['rawjson']) ||
			isset($_SERVER['HTTP_USER_AGENT']) && str_contains($_SERVER['HTTP_USER_AGENT'], 'curl')
		);
	}

	private function authenticate_user_api(array &$url): UserToken
	{
		$userId = $url[2] ?? null;
		$token  = $url[3] ?? null;

		// 🛑 Vérification des paramètres
		if (!$userId || !$token || !ctype_digit((string)$userId)) {
			http_response_code(400);
			exit(json_encode(['error' => 'Paramètres manquants ou invalides']));
		}

		// 📦 Chargement des identifiants
		$tableId  = $this->Sys->id_table('users');
		$colKey   = $this->Sys->id_column($tableId, 'key');
		$colUser  = $this->Sys->id_column($tableId, 'username');

		$key      = $this->Sys->get_cell($tableId, $userId, $colKey);
		$username = $this->Sys->get_cell($tableId, $userId, $colUser);

		// 🛑 Validation des données
		if (!$key || !$username) {
			http_response_code(404);
			exit(json_encode(['error' => 'Utilisateur introuvable ou clé absente']));
		}

		// 🔐 Création de l'objet UserToken
		$tokenObj = new UserToken($username, $key);

		if (!$tokenObj->validate_token($token)) {
			http_response_code(401);
			exit(json_encode(['error' => 'Token invalide']));
		}

		// 🧹 Nettoyage de l’URL (enlève userId et token)
		array_splice($url, 2, 2);

		$this->is_api_call = true; // Pour désactiver l'affichage HTML
		// ✅ Retourne l’objet
		return $tokenObj;
	}

	public function export_json($table_id, $line = null, $column = null)
	{
		$data = $this->DB->data;

		if (!isset($data[$table_id])) {
			http_response_code(404);
			return json_encode(['status' => 'error', 'message' => "Table {$table_id} introuvable."]);
		}

		$table = $data[$table_id];
		$response = [
			'status'    => 'success',
			'table'     => $table_id,
			'endpoint'  => "/api/t{$table_id}" .
			              ($line !== null ? "/l{$line}" : "") .
			              ($column !== null ? "/c{$column}" : ""),
			'timestamp' => date('c')
		];

		if ($line !== null && $column !== null) {
			if (!isset($table[$line])) {
				http_response_code(404);
				return json_encode(['status' => 'error', 'message' => "Ligne {$line} introuvable."]);
			}
			$response['data'] = $table[$line][$column] ?? null;
			$response['meta'] = [
				'line'   => $line,
				'column' => $column,
				'field'  => $table[0][$column] ?? 'unknown'
			];
		} elseif ($line !== null) {
			$tbl_name = $this->DB->table_name($table_id);
			$record = $this->DB->record($tbl_name, $line);

			if (!$record) {
				http_response_code(404);
				return json_encode(['status' => 'error', 'message' => "Ligne {$line} introuvable."]);
			}
			$response['header'] = $table[0];
			$response['data']   = $record;
		} else {
			$response['header'] = $table[0];
			$response['data']   = array_slice($table, 1);
		}

		return json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	/**
	 * 📤 Exporte les données filtrées par une condition (where)
	 */
	public function export_where($table_id, $field, $op, $value)
	{
		$table_name = $this->DB->table_name($table_id);

		// Map d'opérateurs alias
		$aliases = [
			'eq'  => '==',
			'ne'  => '!=',
			'lt'  => '<',
			'gt'  => '>',
			'lte' => '<=',
			'gte' => '>=',
			'like' => 'LIKE',
			'between' => 'BETWEEN',
			'list' => 'LIST'
		];
		if (isset($aliases[strtolower($op)])) {
			$op = $aliases[strtolower($op)];
		}

		$records = $this->DB->where_multiple($table_name, $field, $op, $value);

		if (!$records) {
			http_response_code(404);
			return json_encode([
				'status' => 'error',
				'message' => "Aucun enregistrement trouvé dans {$table_name} pour {$field} {$op} {$value}."
			]);
		}

		$header = $this->DB->table($table_name, true)[0];

		return json_encode([
			'status'    => 'success',
			'table'     => $table_id,
			'endpoint'  => "/api/t{$table_id}/where/{$field}/{$op}/{$value}",
			'header'    => $header,
			'count'     => count($records),
			'data'      => array_values($records),
			'timestamp' => date('c')
		], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Sanitize and decode a URL segment safely
	 */
	private function sanitize_segment(?string $segment): string
	{
		if ($segment === null) return '';

		// 🔓 Decode URL-encoded characters (e.g. %20 → space)
		$segment = urldecode($segment);

		// 🔧 Convert underscores and dashes to spaces (for LIKE)
		$segment = str_replace(['_', '-'], ' ', $segment);

		// ✂️ Remove any HTML tags to prevent XSS
		$segment = strip_tags($segment);

		// 🚫 Remove unwanted characters (keep letters, numbers, common punctuation)
		$segment = preg_replace('/[^\p{L}\p{N}\s.,@-]/u', '', $segment);

		// 🧼 Trim whitespace
		return trim($segment);
	}
	private function sanitize_field(string $segment): string
	{
			// 🔓 Décodage basique
			$segment = urldecode($segment);
			$segment = strip_tags($segment);
			$segment = trim($segment);

			// 🛡️ Vérifie que le nom de champ est conforme : lettres, chiffres, underscores
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $segment)) {
				throw new \Exception("Nom de champ invalide : {$segment}");
			}

			return $segment;
	}

	private function sanitize_value(string $segment): string
	{
		$segment = urldecode($segment);
		$segment = str_replace(['_', '-'], ' ', $segment);
		$segment = strip_tags($segment);
		$segment = preg_replace('/[^\p{L}\p{N}\s.,@-]/u', '', $segment);
		return trim($segment);
	}
	public function put($url)
	{
		$tokenObj = $this->authenticate_user_api($url);
		$username = $tokenObj->get_username(); 
		$this->DB->connect(DATADIRECTORY, $username, 'php');

		$table = $line = $column = null;
		foreach ($url as $segment) {
			if (preg_match('/^t(\d+)$/', $segment, $m)) $table = (int)$m[1];
			if (preg_match('/^l(\d+)$/', $segment, $m)) $line  = (int)$m[1];
			if (preg_match('/^c(\d+)$/', $segment, $m)) $column = (int)$m[1];
		}

		$input = json_decode(file_get_contents('php://input'), true);
		$value = $input['value'] ?? null;

		if ($table !== null && $line !== null && $column !== null && $value !== null) {
			$table_name = $this->DB->table_name($table);
			$table = $this->DB->id_table($table_name);
			$success = $this->DB->set_cell($table, $line, $column, $value);

			if ($success) {
				echo json_encode(['status' => 'success', 'message' => 'Valeur mise à jour.']);
			} else {
				http_response_code(500);
				echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour.']);
			}
		} else {
			http_response_code(400);
			echo json_encode(['status' => 'error', 'message' => 'Paramètres manquants.']);
		}
	}

	public function post($url)
	{
		$input = file_get_contents('php://input');

		$tokenObj = $this->authenticate_user_api($url);
		$username = $tokenObj->get_username(); 
		$this->DB->connect(DATADIRECTORY, $username, 'php');

		$tableId = null;
		foreach ($url as $segment) {
			if (preg_match('/^t(\d+)$/', $segment, $m)) {
				$tableId = (int)$m[1];
				break;
			}
		}

		if ($tableId === null) {
			http_response_code(400);
			echo json_encode(['status' => 'error', 'message' => 'Aucune table spécifiée.']);
			return;
		}

		$tableName = $this->DB->table_name($tableId);
		$json = json_decode(file_get_contents('php://input'), true);

		if (!isset($json['record']) || !is_array($json['record'])) {
			http_response_code(400);
			echo json_encode(['status' => 'error', 'message' => 'Enregistrement invalide.']);
			return;
		}

		$record = $json['record'];
		$record['table'] = $tableId;
		$success = $this->DB->add_record($record);

		if ($success) {
			echo json_encode(['status' => 'success', 'message' => 'Enregistrement ajouté.']);
		} else {
			http_response_code(500);
			echo json_encode(['status' => 'error', 'message' => 'Échec de l\'ajout.']);
		}
	}

	public function delete($url)
	{
		$tokenObj = $this->authenticate_user_api($url);
		$username = $tokenObj->get_username(); 
		$this->DB->connect(DATADIRECTORY, $username, 'php');

		$table = $line = null;
		foreach ($url as $segment) {
			if (preg_match('/^t(\d+)$/', $segment, $m)) $table = (int)$m[1];
			if (preg_match('/^l(\d+)$/', $segment, $m)) $line  = (int)$m[1];
		}

		if ($table !== null && $line !== null) {
			$tableName = $this->DB->table_name($table);
			$tableId   = $this->DB->id_table($tableName);

			try {
				$this->DB->del_line($tableId, $line);

				// Réponse JSON
				if (!headers_sent() && $this->is_json_request()) {
					header('Content-Type: application/json; charset=utf-8');
					echo json_encode(['status' => 'success', 'message' => 'Ligne supprimée.']);
					exit;
				}

			} catch (\Exception $e) {
				http_response_code(500);
				echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
				return;
			}
		} else {
			http_response_code(400);
			echo json_encode(['status' => 'error', 'message' => 'Table ou ligne manquante.']);
			return;
		}
	}




} //CLASS
