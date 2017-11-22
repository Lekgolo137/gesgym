<?php
require_once(__DIR__."/../model/Comment.php");
require_once(__DIR__."/../model/Session.php");
require_once(__DIR__."/../model/SessionMapper.php");
require_once(__DIR__."/../model/User.php");

require_once(__DIR__."/../core/ViewManager.php");
require_once(__DIR__."/../controller/BaseController.php");

class SessionsController extends BaseController {

	private $sessionMapper;

	public function __construct() {
		parent::__construct();

		$this->sessionMapper = new SessionMapper();
	}

	public function sessionslist() {
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. This action requires login");
		}

		// obtain the data from the database
		$sessions = $this->sessionMapper->findAll();

		// put the array containing Session object to the view
		$this->view->setVariable("sessions", $sessions);

		// render the view (/view/session/index.php)
		$this->view->setLayout("default");
		$this->view->render("sessions", "sessionsList");
	}

	public function add() {
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. This action requires login");
		}
		$sessions = new Session();
		if (isset($_POST["submit"])) { // reaching via HTTP Table...
			// populate the Table object with data form the form
			$sessions->setSessionid($_POST["sessionid"]);
			$sessions->setUsername($_POST["username"]);
			$sessions->setTableid($_POST["tableid"]);
			//$sessions->setFechaInicio($fecha);
			$sessions->setFechaFin(NULL);
			try {
				// save the Table object into the database
				$this->sessionMapper->save($sessions);
				// POST-REDIRECT-GET
				// Everything OK, we will redirect the user to the list of tables
				// We want to see a message after redirection, so we establish
				// a "flash" message (which is simply a Session variable) to be
				// get in the view after redirection.
				$this->view->setFlash(sprintf(i18n("Session \"%s\" successfully added."),$sessions ->getSessionid()));
				$this->view->redirect("users", "profile");
			}catch(ValidationException $ex) {
				// Get the errors array inside the exepction...
				$errors = $ex->getErrors();
				// And put it to the view as "errors" variable
				$this->view->setVariable("errors", $errors);
			}
		}
		// Put the Table object visible to the view
		$this->view->setVariable("sessions", $sessions);
		// render the view (/view/tables/add.php)
		$this->view->setLayout("welcome");
		$this->view->render("sessions", "add");
	}

  public function close() {
    if (!isset($this->currentUser)) {
      throw new Exception("Not in session. This action requires login");
    }

    if (!isset($_POST["id"])) {
      throw new Exception("id is mandatory");
    }


    // Get the Table object from the database
    $sessionsid = $_REQUEST["id"];
    $sessions = $this->sessionMapper->findById($sessionsid);

    // Does the table exist?
    if ($sessions == NULL) {
      throw new Exception("no such table with id: ".$sessionsid);
    }

    // Delete the Table object from the database
    $this->sessionMapper->close($sessions);

    // POST-REDIRECT-GET
    // Everything OK, we will redirect the user to the list of tables
    // We want to see a message after redirection, so we establish
    // a "flash" message (which is simply a Session variable) to be
    // get in the view after redirection.
    $this->view->setFlash(sprintf(i18n("Session \"%s\" successfully closed."),$sessions ->getSessionid()));

    // perform the redirection. More or less:
    // header("Location: index.php?controller=tables&action=index")
    // die();
    $this->view->redirect("sessions", "sessionsList");
  }
}