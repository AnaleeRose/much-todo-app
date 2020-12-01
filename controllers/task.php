<?php
// if the page sent us some data, save it for later
if (file_get_contents('php://input')) {
	$json = file_get_contents('php://input');
	$form_info = json_decode($json, true);
} else {
	$form_info = false;
}

// get the prerequisite files
require_once './../p_includes/config.inc.php';
require_once './../models/general_m.php';
require './../models/task_m.php';

// this is the brains of the operation
// it sends data to the model for validation and processing
// and then sends the final copy to views for display
class taskController {

	protected $t_key;
	protected $u_key;
	protected $task_info;
	protected $t_obj;
	public $instructions;
	public $error;

	// we're gonna need the task model, so prep this variable
	public function __construct() {
		$this->t_obj = new task();
		$this->error = new generalObject;
	}

	// figure out what we're trying to do using the url
	// defaults to an error message if it isn't a preset request
	public function parseInstructions($instructions, $form_info) {
		switch ($instructions) {
			case 'new':
				if (isset($form_info) && !empty($form_info)) {
					$this->createTask($form_info);
				} else {
					$this->createTaskMenu();
				}
				break;

			case 'update':
				if ($form_info && !empty($form_info)) {
					$this->updateTask($form_info, $instructions[2]);
				} else {
					if (isset($this->t_key)) {
						$this->updateTaskMenu($this->t_key);
					} else {
						$error = new generalObject;
						print_r($this->error->generateError(231));
						exit();
					}
				}
				break;

			case 'delete':
				$this->deleteTasks($form_info);
				break;

			case 'markIncomplete':
				if (isset($this->t_key)) {
					$user_url = $this->markTask($t_complete = 0);
					redirectToUserModel($user_url, $this->u_key);
				} else {					
					$error = new generalObject;
					print_r($this->error->generateError(230));
					exit();
				}
				break;

			case 'markComplete':
				if (isset($this->t_key)) {
					$user_url = $this->markTask($t_complete = 1);
					redirectToUserModel($user_url, $this->u_key);
				} else {					
					$error = new generalObject;
					print_r($this->error->generateError(230));
					exit();
				}
				break;

			default:
				$error = new generalObject;
				print_r($this->error->generateError(200));
				break;
		}
	}

	// checks if the user key exists in the inner object, if it is then it was likely set here as well and we can let them know it exists
	public function needsUKey() {
		if ($this->t_obj->u_key !== null) return true;
		return false;
	}

	// if the class already has a user key, make sure the unverified key matches
	// if the class does not have a user key, make sure it's in a valid format
	// return false if anything goes wrong
	public function setUKey($unverified_key) {
		// if we already have a user key....
		if (isset($this->u_key) && isset($this->t_obj->u_key)) {
			// ...make sure the new one matches
			if ($this->u_key === $unverified_key && $this->t_obj->u_key === $unverified_key) {
				return true;
			} else {
				return false;
			}
		// if we dont already have a user key, check that it's a valid value and set it 
		} else {
			if (is_numeric($unverified_key) && strlen($unverified_key) === 4) {
				$this->t_obj->u_key = $unverified_key;
				$this->u_key = $unverified_key;
				return true;
			} else {
				return false;
			}
		}
	}

	// checks that the task key is at least a number before setting the variable in this and the model class
	// return false if anything goes wrong
	public function setTKey($unverified_key) {
		// if it's only numbers...
		if (intval($unverified_key)) {

			// ...set it as the task key
			$this->t_key = $unverified_key;
			$this->t_obj->t_key = $unverified_key;
			return 'true';

		} else {
			return false;
		}
	}

	// sends an array to the validateData() function from the generalObject class, this class is in /models/general_m.php
	public function validateData($array) {
		return $this->t_obj->validateData($array);
	}

	// attempts to create a task using the task model
	// it will either create an error, send the invalid data back tot he view, or display a success message
	public function createTask($post_values) {
		$response = $this->t_obj->createTask($post_values);
		if (is_array($response)) {
			if (isset($response[0])) {
				print_r($this->error->generateError($response[0]));
				exit();
			}

			$this->createTaskMenu($post_values, $response);

		} elseif ($response) {
			$task_id = $response;
			require './../views/create_task_successful.php';
		}
	}

	// gets the task menu view
	public function createTaskMenu($task_values = null, $errors = false) {
		require './../views/create_task.php';
	}

	// attempts to delete the list of tasks, 
	public function deleteTasks($t_keys) {
		$response = $this->t_obj->deleteTasks($t_keys);
		require './../views/delete_tasks_successful.php';
	}

	// gets the task info using the task key and sends that to the task update 
	protected function updateTaskMenu() {
		$response = $this->t_obj->getTask($this->t_key);
		$task_values = $response;
		require './../views/update_task.php';
	}

	// attempts to update a task
	// if validation fails, it sends the original values and the a list of the invalid ones to the view 
	// if everything goes right, it returns a success message
	protected function updateTask($post_values) {
		$get_db_task = $this->t_obj->getTask($this->t_key);
		$response = $this->t_obj->updateTask($post_values);
		if (is_array($response)) {
			$errors = $response;
			$task_values = $post_values;
			require_once './../views/update_task.php';
		} elseif ($response) {
			require_once './../views/update_task_successful.php';
		}
	}

	// attempts to mark a task incomplete or complete
	public function markTask($t_complete) {
		$response = $this->t_obj->markTask($this->t_key, $t_complete);
		($t_complete === 1 ? $user_url = '/primaryView/true' : $user_url = '/historicalView/true');
		return $user_url;
	}
}



// if we have a list of instructions from the url
// attempt to set the user key if necessary
// attempts to set the task key if necessary
// sends the instructions to the controller for further review
// displays an error if anything goes wrong
if (!empty($_GET)) {
	$instructions = explode('/', $_SERVER['QUERY_STRING']);
	$task = new taskController;
	if (isset($instructions[2])) {
		if (!empty($instructions[2]) && $instructions[1] !== 'new' && $instructions[1] !== 'delete') {
			$attempt_to_set_t_key = $task->setTKey($instructions[2]);
			if (!$attempt_to_set_t_key) {
				$error = new generalObject;
				print_r($task->error->generateError(232));
				exit();
			}
		} elseif (isset($instructions[1]) && ($instructions[1] === 'new' || $instructions[1] === 'delete')) {
			if (!$task->needsUKey()) {
				if (isset($_COOKIE) && isset($_COOKIE['mtdUserKey']) && !empty($_COOKIE['mtdUserKey'])) {
					$nv_u_key = $_COOKIE['mtdUserKey'];
					if (!$task->setUKey($nv_u_key)) {
						$error = new generalObject;
						print_r($task->error->generateError(215));
						exit();
					}
				} else {
					print_r($task->error->generateError(218));
					exit();
				}
			}
		} else {
			print_r($task->error->generateError(233));
			exit();
		}
	} else {
		print_r($task->error->generateError(234));
		exit();
	}

	if (isset($instructions[1])) {
		$task->parseInstructions($instructions[1], $form_info);
	} else {
		print_r($task->error->generateError(239));
		exit();
	}

} else {
	print_r($this->error->generateError(240));
	exit();
}

// sends info to the user model
function redirectToUserModel($user_url, $u_key) {
	$_SERVER['QUERY_STRING'] = $user_url;
	require './../controllers/user.php';
}

