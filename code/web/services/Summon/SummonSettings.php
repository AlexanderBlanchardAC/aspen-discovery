<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/Summon/SummonSetting.php';

class Summon_SummonSettings extends ObjectEditor {
	function getObjectType(): string {
		return 'SummonSetting';
	}

	function getToolName(): string {
		return 'SummonSettings';
	}

	function getModule(): string {
		return 'Summon';
	}

	function getPageTitle(): string {
		return 'Summon Settings';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new SummonSetting();
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$this->applyFilters($object);
		$object->orderBy($this->getSort());
		$object->find();
		$objectList = [];
		while ($object->fetch()) {
			$objectList[$object->id] = clone $object;
		}
		return $objectList;
	}

	function getDefaultSort(): string {
		return 'name asc';
	}

	function getObjectStructure($context = ''): array {
		return SummonSetting::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function getAdditionalObjectActions($existingObject): array {
		return [];
	}

	function getInstructions(): string {
		return 'https://help.aspendiscovery.org/summon';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#summon', 'Summon');
		$breadcrumbs[] = new Breadcrumb('/Summon/SummonSettings', 'SummonSettings');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'summon';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer Summon Settings');
	}

	function viewIndividualObject($structure) {
		//Update the list of databases when the user edits
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
		if (!empty($id) && $id > 0) {
			/** @var SummonSetting $curObject */
			$curObject = $this->getExistingObjectById($id);
			$searchSettings = $curObject->getSearchSettings();
			foreach ($searchSettings as $searchSetting) {
				$searchSetting->updateDatabasesFromSummon();
			}
		}
		parent::viewIndividualObject($structure);
	}
}