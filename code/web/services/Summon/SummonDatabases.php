<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/Summon/SummonDatabase.php';

class Summon_SummonDatabases extends ObjectEditor {
	function getObjectType(): string {
		return 'SummonDatabase';
	}

	function getToolName(): string {
		return 'SummonDatabases';
	}

	function getModule(): string {
		return 'Summon';
	}

	function getPageTitle(): string {
		return 'Summon Databases';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new SummonDatabase();
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
		return 'displayName asc';
	}

	function getObjectStructure($context = ''): array {
		return SummonDatabase::getObjectStructure($context);
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
		$breadcrumbs[] = new Breadcrumb('/Summon/SummonSearchSettings', 'Summon Search Settings');
		if (!empty($this->activeObject) && $this->activeObject instanceof SummonDatabase) {
			$breadcrumbs[] = new Breadcrumb('/Summon/SummonSearchSettings?objectAction=edit&id=' . $this->activeObject->searchSettingId, 'Summon Search Setting');
		}
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'summon';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer Summon Settings');
	}

	function canDelete() {
		return false;
	}
}