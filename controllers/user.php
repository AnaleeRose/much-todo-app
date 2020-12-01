<?php
// get the prerequisite files
require_once './../p_includes/config.inc.php';
require_once './../models/general_m.php';
require './../models/user_m.php';

// this is the brains of the operation
// it sends data to the model for validation and processing
// and then sends the final copy to views for display
class userController {

	protected $u_key;
	protected $task_list;
	protected $should_provide_key = false;
	protected $marked_task = false;
	protected $pg_num = 1;
	protected $pg_limit;
	protected $u_obj;
	public $total_pages = 0;
	public $unverified_key;
	public $instructions;
	public $error;

	// we're gonna need the user model, so prep this variable
	public function __construct() {
		$this->u_obj = new user();
		$this->error = new generalObject;
	}

	// figure out what we're trying to do using the url
	// defaults to an error message if it isn't a preset request
	public function parseInstructions($instructions) {
		if (isset($instructions[2])) {
			switch ($instructions[2]) {
				case 'true':
					$this->marked_task = true;
					break;

				case strpos($instructions[2],'pg_') !== false:
					$nv_pg_num = substr($instructions[2], 3);
					if (!$this->setPgNum($nv_pg_num)) {
						$this->setPgNum(1);
					}
					break;

				case $this->u_key:
					break;

				default:
					$this->setPgNum(1);
					break;
			}			
		} 

		switch ($instructions[1]) {
			case 'new':
				$this->createUser();
				$this->should_provide_key = true;
				$this->getPrimaryView($this->marked_task, $this->pg_num);
				break;

			case 'primaryView':
				if ($this->marked_task == true) $this->generateSuccessNotice_m();
				$this->getPrimaryView($this->marked_task, $this->pg_num);
				break;

			case 'historicalView':
				if ($this->marked_task == true) $this->generateSuccessNotice_m();
				$this->getHistoricalView($this->marked_task, $this->pg_num);
				break;

			case 'managementView':
				$this->getManagementView();
				break;

			case 'deleteView':
				$this->getDeleteView($this->marked_task, $this->pg_num);
				break;

			default:
				print_r($this->error->generateError(242));
				exit();
				break;
		}
	}

	// sends an array to the validateData() function from the generalObject class, this class is in /models/general_m.php
	protected function validateData($array) {
		return $this->u_obj->validateData($array);
	}

	// checks if the user key exists
	public function uKeyExists() {
		if (isset($this->u_obj->u_key) && !empty($this->u_obj->u_key)) return true;
		return false;
	}

	// checks if an unverified key matches the current user key
	public function matchUKey($unverified_key) {
		if ($this->u_obj->u_key === $unverified_key) {
			$this->u_key = $this->u_obj->u_key;
			return true;
		}
		print_r($this->error->generateError(215));
		exit();
	}

	// sets the current page number
	protected function setPgNum($unverified_num) {
		if (intval($unverified_num)) {
			$this->pg_num = $unverified_num;	
			$this->u_obj->pg_num = $unverified_num;	
			return true;
		} else {
			$this->pg_num = $unverified_num;	
			$this->u_obj->pg_num = $unverified_num;	
			return false;
		}

	}


	// validates the key and sets the internal variables
	public function setUKey($unverified_key) {
		if (is_numeric($unverified_key) && strlen($unverified_key) === 4) {
			$this->u_obj->u_key = $unverified_key;
			$this->u_key = $unverified_key;
			return true;
		}
		print_r($this->error->generateError(255));
		exit();
	}

	// attempts to create a new user and saves the user key for later
	protected function createUser() {
		$this->u_key = null;
		$this->u_key = $this->u_obj->create();
	}

	// gets the incomplete tasks from the model and passes that to the primary view for display
	protected function getPrimaryView($marked_task, $page_num) {
		$c_view = 'primaryView';
		$getIncompleteTasks = $this->u_obj->getIncompleteTasks($page_num);
		if ($this->should_provide_key) $provide_key = $this->u_key;
		$total_pages = $this->u_obj->total_pages;
		$pg_num = $this->pg_num;

		if ($getIncompleteTasks[0] === 'empty') {
			$no_tasks = true;
			require './../views/primary_view_n.php';
		} else {
			$task_list = $getIncompleteTasks;
			require './../views/primary_view.php';
		}
	}

	// gets the complete tasks from the model and passes that to the historical view for display
	protected function getHistoricalView($marked_task, $page_num) {
		$c_view = 'historicalView';
		$getCompleteTasks = $this->u_obj->getCompleteTasks($page_num);
		$total_pages = $this->u_obj->total_pages;
		$pg_num = $this->pg_num;

		if ($getCompleteTasks[0] === 'empty') {
			$no_tasks = true;
			require './../views/historical_view_n.php';
		} else {
			$task_list = $getCompleteTasks;
			require './../views/historical_view.php';
		}
	}

	// gets all tasks from the model and passes that to the delete view for display
	protected function getDeleteView($marked_task, $page_num) {
		$c_view = 'deleteView';
		$getAllTasks = $this->u_obj->getAllTasks($page_num);
		$total_pages = $this->u_obj->total_pages;
		$pg_num = $this->pg_num;

		if ($getAllTasks[0] === 'empty') {
			$no_tasks = true;
			require './../views/delete_view_n.php';
		} else {
			$task_list = $getAllTasks;
			require './../views/delete_view.php';
		}
	}

	// gets the management view for display
	protected function getManagementView() {
		$u_key = $this->u_key;
		require './../views/management_view_n.php';
	}

	// generates a success notice for a marked task
	protected function generateSuccessNotice_m() {
		require './../views/marked_task_success_notice.php';
	}
}

// if we have a list of instructions from the url
// attempt to set the user key if necessary, 
// sends the instructions to the controller for further review
// displays an error if anything goes wrong
if (!empty($_GET)) {
	$instructions = explode('/', $_SERVER['QUERY_STRING']);
	$user = new userController;

	if (isset($_COOKIE) && isset($_COOKIE['mtdUserKey']) && !empty($_COOKIE['mtdUserKey'])) {
		$nv_u_key = $_COOKIE['mtdUserKey'];
		if ($user->uKeyExists()) {
			$user->matchUKey($nv_u_key);
		} else {
			if (!$user->setUKey($nv_u_key)) {
				print_r($user->error->generateError(215));
				exit();
			}
		}
		
	} else {
		if ($instructions[1] != 'new') {
			print_r($user->error->generateError(218));
			exit();
		}
	}

	if (isset($instructions[1])) {
		$user->parseInstructions($instructions);
	} else {
		print_r($user->error->generateError(239));
		exit();
	}

} else {
	print_r($this->error->generateError(260));
	exit();
}