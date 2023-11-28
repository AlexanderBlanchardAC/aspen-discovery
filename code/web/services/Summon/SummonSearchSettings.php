<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/Summon/SummonSetting.php';
require_once ROOT_DIR . '/sys/Summon/SummonSearchSetting.php';

class Summon_SummonSearchSettings extends ObjectEditor {
	function getObjectType(): string {
		return 'SummonSetting';
	}

	function getToolName(): string {
		return 'SummonSearchSettings';
	}

	function getModule(): string {
		return 'Summon';
	}

	function getPageTitle(): string {
		return 'Summon Search Settings';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new SummonSearchSetting();
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
		return SummonSearchSetting::getObjectStructure($context);
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
		return '';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#summon', 'Summon');
		$breadcrumbs[] = new Breadcrumb('/Summon/SummonSearchSettings', 'SummonSearch Settings');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'summon';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer Summon Settings');
	}
}