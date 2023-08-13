<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';

class Admin_Variables extends ObjectEditor {

	function getObjectType(): string {
		return 'Variable';
	}

	function getToolName(): string {
		return 'Variables';
	}

	function getPageTitle(): string {
		return 'System Variables';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$variableList = [];

		$object = new Variable();
		$object->orderBy($this->getSort());
		$this->applyFilters($object);
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$object->find();
		while ($object->fetch()) {
			$variableList[$object->id] = clone $object;
		}
		return $variableList;
	}

	function getDefaultSort(): string {
		return 'name asc';
	}

	function getObjectStructure($context = ''): array {
		return Variable::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'name';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function canAddNew() {
		return UserAccount::getActiveUserObj()->isAspenAdminUser();
	}

	function canDelete() {
		return true;
	}

	/**
	 * @param DataObject $existingObject
	 * @return array
	 */
	function getAdditionalObjectActions($existingObject): array {
		$actions = [];
		if ($existingObject && $existingObject->getPrimaryKeyValue() != '') {
			$actions[] = [
				'text' => '<span class="glyphicon glyphicon-time" aria-hidden="true"></span> Set to Current Timestamp (seconds)',
				'url' => "/{$this->getModule()}/{$this->getToolName()}?objectAction=setToNow&amp;id=" . $existingObject->getPrimaryKeyValue(),
			];
			$actions[] = [
				'text' => '<span class="glyphicon glyphicon-time" aria-hidden="true"></span> Set to Current Timestamp (milliseconds)',
				'url' => "/{$this->getModule()}/{$this->getToolName()}?objectAction=setToNow&amp;ms=1&amp;id=" . $existingObject->getPrimaryKeyValue(),
			];
			$actions[] = [
				'text' => '<span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span> Increase by 10,000',
				'url' => "/{$this->getModule()}/{$this->getToolName()}?objectAction=IncrementVariable&amp;direction=up&amp;id=" . $existingObject->getPrimaryKeyValue(),
			];
			$actions[] = [
				'text' => '<span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span> Decrease by 500',
				'url' => "/{$this->getModule()}/{$this->getToolName()}?objectAction=IncrementVariable&amp;direction=down&amp;id=" . $existingObject->getPrimaryKeyValue(),
			];
		}
		return $actions;
	}

	/** @noinspection PhpUnused */
	function setToNow() {
		$id = $_REQUEST['id'];
		$useMilliseconds = isset($_REQUEST['ms']) && ($_REQUEST['ms'] == 1 || $_REQUEST['ms'] == 'true');
		if (!empty($id) && ctype_digit($id)) {
			$variable = new Variable();
			$variable->get($id);
			if ($variable) {
				$variable->value = $useMilliseconds ? time() * 1000 : time();
				$variable->update();
			}
			header("Location: /{$this->getModule()}/{$this->getToolName()}?objectAction=edit&id=" . $id);
		}
	}

	/** @noinspection PhpUnused */
	function IncrementVariable() {
		$id = $_REQUEST['id'];
		if (!empty($id) && ctype_digit($id)) {
			$variable = new Variable();
			$variable->get($id);
			if ($variable) {
				$amount = 0;
				if ($_REQUEST['direction'] == 'up') {
					$amount = 10000;
				} elseif ($_REQUEST['direction'] == 'down') {
					$amount = -500;
				}
				if ($amount) {
					$variable->value += $amount;
					$variable->update();
				}
			}
			header("Location: /{$this->getModule()}/{$this->getToolName()}?objectAction=edit&id=" . $id);
		}
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#system_admin', 'System Administration');
		$breadcrumbs[] = new Breadcrumb('/Admin/Variables', 'Variables');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'system_admin';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer System Variables');
	}

	function canBatchEdit() {
		return false;
	}

	function canCompare() {
		return false;
	}
}