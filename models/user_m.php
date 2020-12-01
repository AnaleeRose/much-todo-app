<?php

// this is the processor of the operation
// it handles all data processing
class user extends generalObject {

	public $u_key;
	public $pg_num = 1;
	public $pg_limit = 8;
	public $total_pages = 0;
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
	}

	// since we're just randomly generate a number for the user key, we need to make sure the number is unique
	// this will run until it gets an unused key
	protected function check_new_key() {
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

	// creates a new user and returns the new user key
	public function create() {
		$key = $this->check_new_key();
		if ($key) {
			$stmt = $this->dbpdo->prepare('INSERT INTO `users` VALUES (null, :new_key);');
	        $stmt->bindParam(':new_key', $key, PDO::PARAM_INT);

			if ($stmt->execute()) {
				$this->u_key = $key;
				return $this->u_key;
			} else {
				echo $this->generateError(201);
				exit();
			}
		} else {
			echo $this->generateError(201);
			exit();
		}
	}


	// does all the processing for the primary view
	// gets a list of all incomplete tasks and handles pagination processing
	public function getIncompleteTasks() {
		$this->getTotalPages('incomplete');
		if ($this->u_key == null) {
			echo $this->generateError(201);
			exit();
		}

		if ($this->pg_num > 1) {
			$offset = ($this->pg_num-1) * $this->pg_limit;
		} else {
			$offset = 0;
		}
		$stmt = $this->dbpdo->prepare("SELECT * FROM tasks WHERE t_u_key = :u_key && t_complete = 0 ORDER BY t_due_date ASC LIMIT :offset, :pg_limit");
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->bindParam(':pg_limit', $this->pg_limit, PDO::PARAM_INT);
    
        $stmt->bindParam(':u_key', $this->u_key, PDO::PARAM_INT);
		if ($stmt->execute()) {
			$possible_task_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$task_list = [];
			if (!empty($possible_task_list)) {
				foreach ($possible_task_list as $task) {
					$validated = $this->validateData($task);
					$difference = array_diff($task, $validated);
					if (!$difference) {
						array_push($task_list, $validated);
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
			echo $this->generateError(202);
			exit();
		}
	}

	// does all the processing for the historical view
	// gets a list of all complete tasks and handles pagination processing
	public function getCompleteTasks() {
		// we need to return the total number of pages set by the function below!
		$this->getTotalPages('complete');
		if ($this->u_key == null) {
			echo $this->generateError(201);
			exit();
		}

		if ($this->pg_num > 1) {
			$offset = ($this->pg_num-1) * $this->pg_limit;
		} else {
			$offset = 0;
		}
		$stmt = $this->dbpdo->prepare("SELECT * FROM tasks WHERE t_u_key = :u_key && t_complete = 1 ORDER BY t_due_date ASC LIMIT :offset, :pg_limit");
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->bindParam(':pg_limit', $this->pg_limit, PDO::PARAM_INT);
    
        $stmt->bindParam(':u_key', $this->u_key, PDO::PARAM_INT);
		if ($stmt->execute()) {
			$possible_task_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$task_list = [];
			if (!empty($possible_task_list)) {
				foreach ($possible_task_list as $task) {
					$validated = $this->validateData($task);
					$difference = array_diff($task, $validated);
					if (!$difference) {
						array_push($task_list, $validated);
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
			echo $this->generateError(202);
			exit();
		}
	}


	// gets the total amount of pages for a specific view
	protected function getTotalPages($type_of_tasks) {
		switch ($type_of_tasks) {
			case 'incomplete':
				$stmt = $this->dbpdo->prepare("SELECT COUNT(*) as total_tasks FROM tasks WHERE t_u_key = :u_key && t_complete = :t_complete ORDER BY t_due_date ASC");
				$t_complete = 0;
				break;

			case 'complete':
				$stmt = $this->dbpdo->prepare("SELECT COUNT(*) as total_tasks FROM tasks WHERE t_u_key = :u_key && t_complete = :t_complete ORDER BY t_due_date ASC");
				$t_complete = 1;
				break;
			
			case 'both':
				$stmt = $this->dbpdo->prepare("SELECT COUNT(*) as total_tasks FROM tasks WHERE t_u_key = :u_key ORDER BY t_due_date ASC");
				break;

			default:
				print_r($this->generateError(253));
				exit();
				break;
		}

		if (isset($t_complete)) $stmt->bindParam(':t_complete', $t_complete, PDO::PARAM_INT);
        $stmt->bindParam(':u_key', $this->u_key, PDO::PARAM_INT);
        if ($stmt->execute()) {
        	$s_response = $stmt->fetch();
        	$this->total_pages = ceil($s_response['total_tasks']/$this->pg_limit);
        } else {
			print_r($this->generateError(253));
        	exit();
        }
	}

	// does all the processing for the delete view
	// gets a list of all tasks and handles pagination processing
	public function getAllTasks() {
		// we need to return the total number of pages set by the function below!
		$this->getTotalPages('both');
		if ($this->u_key == null) {
			echo $this->generateError(201);
			exit();
		}


		if ($this->pg_num > 1) {
			$offset = ($this->pg_num-1) * $this->pg_limit;
		} else {
			$offset = 0;
		}
		$stmt = $this->dbpdo->prepare("SELECT * FROM tasks WHERE t_u_key = :u_key ORDER BY t_due_date ASC LIMIT :offset, :pg_limit");
        $stmt->bindParam(':u_key', $this->u_key, PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
		$stmt->bindParam(':pg_limit', $this->pg_limit, PDO::PARAM_INT);

		if ($stmt->execute()) {
			$possible_task_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
			echo "total: " .$this->total_pages;
			echo $this->generateError(202);
			exit();
		}
	}

}