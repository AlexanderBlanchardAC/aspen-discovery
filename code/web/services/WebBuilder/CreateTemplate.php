<?php


class WebBuilder_CreateTemplate extends Action {
	/** @var CreateTemplate */
	private $createTemplate;

	function __construct() {
		parent::__construct();

		require_once ROOT_DIR . '/sys/WebBuilder/CreateTemplate.php';

		global $interface;

		$id = strip_tags($_REQUEST['id']);
		$this->createTemplate = new CreateTemplate();
		$this->createTemplate->id = $id;

		if (!$this->createTemplate->find(true)) {
			$interface->assign('module', 'Error');
			$interface->assign('action', 'Handle404');
			require_once ROOT_DIR . "/services/Error/Handle404.php";
			$actionClass = new Error_Handle404();
			$actionClass->launch();
			die();
		} elseif (!$this->canView()) {
			$interface->assign('module', 'Error');
			$interface->assign('action', 'Handle401');
			$interface->assign('followupModule', 'WebBuilder');
			$interface->assign('followupAction', 'CreateTemplate');
			$interface->assign('id', $id);
			require_once ROOT_DIR . "/services/Error/Handle401.php";
			$actionClass = new Error_Handle401();
			$actionClass->launch();
			die();
		}
	}

	function launch() {
		global $interface;

		$title = $this->createTemplate->title;
		$interface->assign('id', $this->createTemplate->id);
		$interface->assign('contents', $this->createTemplate->getFormattedContents());
		$interface->assign('title', $title);

		$this->display('createTemplate.tpl', $title, '', false);
	}

	function canView(): bool {
		return true;
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/', 'Home');
		if ($this->createTemplate != null) {
			$breadcrumbs[] = new Breadcrumb('', $this->createTemplate->title, true);
			if (UserAccount::userHasPermission([
				'Administer All Grapes Pages',
				'Administer Library Grapes Pages',
			])) {
				$breadcrumbs[] = new Breadcrumb('/WebBuilder/CreateTemplates?id=' . $this->createTemplate->id . '&objectAction=createFromBlankPage', 'Edit', true);
			}
		}
		return $breadcrumbs;
	}
}