<?php

class user extends generalObject {

	public $u_key;
	protected $task_list;
	protected $response = false;
	protected $safe = false;
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

	public function __construct() {
		parent::__construct();
		if (isset($u_key) && $u_key != null) {
			$this->u_key = $u_key;
		} else {
			$this->u_key = null;
		}
	}

	public function um_generateError($code) {
		return $this->generateError($code);
	}

	public function validateData($array) {
		$this->safe = true;
		$invalid = [];

		foreach ($array as $key => $val) {
			if (!in_array($key, $this->accepted_values)) {
				$invalid[] = "Not accepted: $key";
				$this->safe = false;
			} else {
				switch ($key) {
					case 'nv_u_key':
						if (!is_numeric($val) || strlen($val) !== 7) {
							$invalid[$key] = $val;
							$this->safe = false;
						}
						break;

					case 't_key':
						if (!is_numeric($val)) {
							$invalid[$key] = $val;
							$this->safe = false;
						}
						break;


					case 't_u_key':
						if (!is_numeric($val) && strlen($val) === 7) {
							$invalid[$key] = $val;
							$this->safe = false;
						}
						break;


					case 't_name':
						$array[$key] = preg_replace("/[^a-zA-Z0-9_ -]/", '', $val);
						if ($val == '' || strlen($val) == 0) $val = "ERROR, please contact our service team.";
						break;


					case 't_due_date':
						$val = preg_replace("/[^0-9-]/", '', $val);
						if ($val == '') $val = "2020-01-01";
						break;

					case 't_date_added':
						$val = preg_replace("/[^0-9-]/", '', $val);
						if ($val == '') $val = "2020-01-01";
						break;

					case 't_description':
							$val = htmlspecialchars($val);
						break;

					case 't_complete':
						if (($val !== 1 && $val !== 0) && ($val !== '1' && $val !== '0')){
							$val = '0';
						}
						break;


					default:
						unset($array[$key]);
						break;

				}
			}
		}

		return $array;
	
	}


	private function check_new_key() {
		$new_key = mt_rand(1000000, 9999999);
		$stmt = $this->dbpdo->prepare('SELECT user_key FROM `users` WHERE user_key = :new_key;');
	    $stmt->bindParam(':new_key', $new_key, PDO::PARAM_INT);
		if ($stmt->execute()) {
			if ($stmt->rowCount() != 0) {
				$this->check_new_key();
			} else {
				return $new_key;
			}
		} else {
			return false;

		}
	}

	public function create() {
		$key = $this->check_new_key();
		if ($key) {
			$stmt = $this->dbpdo->prepare('INSERT INTO `users` VALUES (null, :new_key);');
	        $stmt->bindParam(':new_key', $key, PDO::PARAM_INT);

			if ($stmt->execute()) {
				$this->u_key = $key;
				return $this->u_key;
			} else {
				return $this->generateError(201);
			}
		} else {
			return $this->generateError(201);
		}
	}


	public function getUKey() {
		return $this->u_key;
	}

	public function getIncompleteTasks() {
		if ($this->u_key == null) return $this->generateError(201);
		$stmt = $this->dbpdo->prepare("SELECT * FROM tasks WHERE t_u_key = :u_key && t_complete = 0 ORDER BY `tasks`.`t_due_date` ASC");
        $stmt->bindParam(':u_key', $this->u_key, PDO::PARAM_INT);
		if ($stmt->execute()) {
			$possible_task_list = $stmt->fetchAll();
			$task_list = [];
			if (!empty($possible_task_list)) {
				foreach ($possible_task_list as $task) {
					$validate = $this->validateData($task);
					if ($validate) {
						array_push($task_list, $validate);
					}
				}

				if (!empty($task_list)) {
					return $task_list;
				} else {
					$response[0] = 'empty';
					return $response;
				}
				
			} else {
				$response[0] = 'empty';
				return $response;
			}
		} else {
			return $this->generateError(202);
		}
	}

	public function getCompleteTasks() {
		if ($this->u_key == null) return $this->generateError(201);
		$stmt = $this->dbpdo->prepare("SELECT * FROM tasks WHERE t_u_key = :u_key && t_complete = true");
        $stmt->bindParam(':u_key', $this->u_key, PDO::PARAM_INT);
		if ($stmt->execute()) {
			$task_list = $stmt->fetchAll();
			if (!empty($task_list)) {
				return $task_list;
			} else {
				$response[0] = 'empty';
				return $response;
			}
		} else {
			return $this->generateError(202);
		}
	}

	public function getResponse() {
		return $response;
	}

	public function __deconstruct() {
		$this->getResponse;
	}


}