<?php

// this is the processor of the operation
// it handles all data processing
class task extends generalObject {

	public $u_key;
	public $t_key;
	protected $task_list;
	protected $response = false;
	protected $safe = false;


	public function __construct() {
		parent::__construct();
	}

	// validates task info and either returns a list of invalid inputs or returns the response of taskToDb()
	public function createTask($task_info) {
		// prepare variables
		$return_errors = [];

		// sends task info to the validateData() function from the generalObject class found in in /models/general_m.php. It returns a clean version of the task data
		$safe_ver_task = $this->validateData($task_info);

		// check for differences between the clean version and the original values
		$difference = array_diff($safe_ver_task, $task_info);

		// if there are differences...
		if ($difference) {
			// ...create an array with all the 'dirty' values and send those back
			foreach ($difference as $key => $value) {
				$return_errors[$key] = $value;
			}
			return $return_errors;
		// if there are no differences, we've got a clean task that can be added to the db
		} else {

			// send back whatever values 
			return $this->taskToDb($task_info, true);
		}
	}

	// validates task info and either returns a list of invalid inputs or returns the response of taskToDb()
	public function updateTask($task_info) {
		$validation_response = $this->validateTask($task_info);
		if (is_array($validation_response)) {
			return $validation_response;
		} elseif ($validation_response) {
			return $this->taskToDb($task_info);
		}
	}

	// marks a task complete or incomplete, returns true
	public function markTask($t_key, $t_complete) {
		if ($t_key) {
			$stmt = $this->dbpdo->prepare('UPDATE tasks SET t_complete = :t_complete WHERE t_key = :t_key');
			$stmt->bindParam(':t_key', $t_key, PDO::PARAM_INT);
			$stmt->bindParam(':t_complete', $t_complete, PDO::PARAM_INT);
			if ($stmt->execute()) {
				return true;
			} else {
				echo $this->generateError(230);
				exit();
			}
		} else {
			echo $this->generateError(229);
			exit();
		}
	}

	// validates a task id, aka just checks if it's a number. returns the task id or false
	public function checkTaskId($id_to_verify) {
		if (intval($id_to_verify)) return intval($id_to_verify);
		return false;
	}

	// gets the info on an individual task, returns an array with that info or displays and error
	public function getTask($t_key) {
		if ($t_key) {
			$stmt = $this->dbpdo->prepare('SELECT * FROM tasks WHERE t_key = :t_key');
			$stmt->bindParam(':t_key', $t_key, PDO::PARAM_INT);
			if ($stmt->execute()) {
				$task_info = $stmt->fetch();
				return $task_info;
			} else {
				echo $this->generateError(206);
				exit();
			}
		} else {
			echo $this->generateError(205);
			exit();
		}
	}


	// creates a valid version of task and compares the original to that. If they match, returns true. If it's different, it returns a list of all the invalid inputs
	public function validateTask($task_info, $return_all = false) {
		$return_errors = [];
		$safe_ver_task = $this->validateData($task_info);
		$difference = array_diff($task_info, $safe_ver_task);
		if ($difference) {
			foreach ($difference as $key => $value) {
				$return_errors[$key] = $value;
			}
			unset($key,$value);
			if ($return_all) $task_info;
			return $return_errors;
		} else {
			return true;
		}
		
	}

	// sends a task to the db, returns true or displays an error
	protected function taskToDb($task_info, $new = false) {
		if (!$new) {
			$stmt = $this->dbpdo->prepare('UPDATE tasks SET t_name = :t_name, t_due_date = :t_due_date, t_description = :t_description WHERE t_key = :t_key;');

			$stmt->bindParam(':t_key', $task_info['t_key'], PDO::PARAM_INT);
			$stmt->bindParam(':t_name', $task_info['t_name'], PDO::PARAM_STR);
			$stmt->bindParam(':t_due_date', $task_info['t_due_date'], PDO::PARAM_STR);
			$stmt->bindParam(':t_description', $task_info['t_description'], PDO::PARAM_STR);
			if ($stmt->execute()) {
				return true;
			} else {
				echo $this->generateError(201);
				exit();
			}
		} else {
			$task_info['t_u_key'] = $this->u_key;
			$date_created = date('Y-m-d');
			if ($task_info['t_description'] !== '') {
				$stmt = $this->dbpdo->prepare('INSERT INTO tasks VALUES (null, :t_u_key, :t_name, :t_due_date, :t_date_created, :t_description, 0);');

				$stmt->bindParam(':t_description', $task_info['t_description'], PDO::PARAM_STR);
			} else {
				$stmt = $this->dbpdo->prepare('INSERT INTO tasks VALUES (null, :t_u_key, :t_name, :t_due_date, :t_date_created, null, 0);');
			}

			$stmt->bindParam(':t_u_key', $task_info['t_u_key'], PDO::PARAM_STR);
			$stmt->bindParam(':t_name', $task_info['t_name'], PDO::PARAM_STR);
			$stmt->bindParam(':t_due_date', $task_info['t_due_date'], PDO::PARAM_STR);
			$stmt->bindParam(':t_date_created', $date_created, PDO::PARAM_STR);
			$stmt->bindParam(':t_name', $task_info['t_name'], PDO::PARAM_STR);
			if ($stmt->execute()) {
				return $this->dbpdo->lastInsertId();
			} else {
				echo $this->generateError(201);
				exit();
			}	
		}

	}

	// deletes a task from the db, returns true or displays an error
	public function deleteTasks($t_keys) {
		if ($this->u_key == null) {
			echo $this->generateError(235);
			exit();
		}
		if ($t_keys == false) {
			echo $this->generateError(237);
			exit();
		}
		$del_list = false;
		$first = true;
		foreach ($t_keys as $id => $key) {
			$key = substr($key, 2);
			$int_key = intval($key);
			if ($int_key) {
				if ($first) {
					$del_list []= $int_key;
				}
			} else {
				echo $this->generateError(204);
				exit();
			}
		}
		unset($id, $value);
		if ($del_list) {
			foreach ($del_list as $key => $value) {
				$stmt = $this->dbpdo->prepare("DELETE FROM tasks WHERE t_u_key = :u_key && t_key = :t_key");
        		$stmt->bindParam(':u_key', $this->u_key, PDO::PARAM_INT);
        		$stmt->bindParam(':t_key', $value, PDO::PARAM_INT);
        		if (!$stmt->execute()) {
        			echo $this->generateError(236);
        			exit();
        		}
			
			}
			return true;
		} else {
			echo $this->generateError(203);
			exit();
		}

	}

}
