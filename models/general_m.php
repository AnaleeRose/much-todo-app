<?php
require '../p_includes/mysql.inc.php';
date_default_timezone_set("America/Chicago");
error_reporting(E_ALL);
ini_set('display_errors', 'on');
// $dbpdo->errorInfo();

// this contains some functions that both user and task models need
class generalObject extends mysql {

	public $error_logging = false;
	protected $required_values = [
			't_name',
			't_due_date'
		];
	protected $accepted_values = [
			'nv_u_key',
			't_key',
			't_u_key',
			't_name',
			't_due_date',
			't_date_added',
			't_description',
			't_complete'
		];

	function __construct() {
		parent::__construct();
		$this->dbpdo = $this->mysql_connect();
        $this->dbpdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $this->dbpdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE , PDO::FETCH_ASSOC );

	}

	public function validateData($array) {
		foreach ($array as $key => $value) {
			if (!in_array($key, $this->accepted_values)) {
				unset($array[$key]);
			} else {
				if (in_array($key, $this->required_values) && empty($value)) {
					$array[$key] = 'error';
					continue;
				}

				switch ($key) {
					case 'nv_u_key':
						if (!is_numeric($value) || strlen($value) !== 7) {
							if (!is_numeric($value)) return $array;
							if (strlen($value) !== 7) return strlen($value);
							// return false;
						} elseif (count($array) === 1) {
							return true;
						}
						break;

					case 't_key':
						if (!is_numeric($value)) {
							$array[$key] = 'error';
						}
						if ($value === '') $array[$key] = 'error';
						break;


					case 't_u_key':
						if (!is_numeric($value) || strlen($value) !== 7) {
							// $value = 'error';
							$array[$key] = 'error';
						}
						if ($value === '') $array[$key] = 'error';
						break;


					case 't_name':
						$n_value = preg_replace("/[^a-zA-Z0-9_ ,\.-]/", '', $value);
						if ($value == '' || strlen($value) == 0) {
							$array[$key] = "error";
						} else {
							$array[$key] = $n_value;
						}
						if ($value === '') $array[$key] = 'error';
						break;


					case 't_due_date':
						$n_value = preg_replace("/[^0-9-:A-Z]/", '', $value);
						if ($value == '' || strlen($value) == 0) {
							$array[$key] = "error";
						} else {
							$array[$key] = $n_value;
						}
						if ($value === '') $array[$key] = 'error';
						break;

					case 't_date_added':
						$n_value = preg_replace("/[^0-9-:A-Z]/", '', $value);
						if ($value == '' || strlen($value) == 0) {
							$array[$key] = "error";
						} else {
							$array[$key] = $n_value;
						}
						if ($value === '') $array[$key] = 'error';
						break;

					case 't_description':
							$array[$key] = htmlspecialchars($value);
						break;

					case 't_complete':
							if (($value !== 1 && $value !== 0) && ($value !== '1' && $value !== '0')){
								$array[$key] = '0';
							} else {
								$array[$key] = $value;

							}
						if ($value === '') $array[$key] = 'error';
						break;


					default:
						if ($value === '') $array[$key] = 'error';
						unset($array[$key]);
						break;

				}
			}
		}

		unset($key, $value);
		return $array;
	}

	public function generateError($code) {
		if (!empty($code) && is_numeric($code) && strlen($code) === 3) {
			$e_code = $code;
		} else {
			$e_code = 100;
		}
		if ($this->error_logging) {
			$stmt = $this->dbpdo->prepare("INSERT INTO logs VALUES (null, :code, null)");
	        $stmt->bindParam(':code', $code, PDO::PARAM_INT);

			try {
			    $stmt->execute();
			} catch (exception $e) {
			    // do nothing
		    }
		}

		$error_notice = '<span id="generateNotice" data-notice-type="error" data-code=' . $e_code . '>error</span>';
		// $error_response = ['error_response', $error_notice];

		return $error_notice;
	}
}
$error = new generalObject;

