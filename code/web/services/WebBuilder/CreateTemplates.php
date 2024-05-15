<?php
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/WebBuilder/CreateTemplate.php';

class WebBuilder_CreateTemplates extends ObjectEditor {
	function getObjectType(): string {
		return 'CreateTemplate';
	}

	function getToolName(): string {
		return 'CreateTemplates';
	}

	function getModule(): string {
		return 'WebBuilder';
	}

	function getPageTitle(): string {
		return 'Create Templates';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new CreateTemplate();
		$object->orderBy($this->getSort());
		$this->applyFilters($object);
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$userHasExistingObjects = true;
		if (!UserAccount::userHasPermission('Administer All Basic Pages')) {
			$userHasExistingObjects = $this->limitToObjectsForLibrary($object, 'LibraryBasicPage', 'basicPageId');
		}
		$objectList = [];
		if ($userHasExistingObjects) {
			$object->find();
			while ($object->fetch()) {
				$objectList[$object->id] = clone $object;
			}
		}
		return $objectList;
	}

	function getDefaultSort(): string {
		return 'title asc';
	}

	function getObjectStructure($context = ''): array {
		return CreateTemplate::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}


	function getInstructions(): string {
		return 'https://help.aspendiscovery.org/help/webbuilder/pages';
	}

	function getInitializationJs(): string {
		return 'AspenDiscovery.WebBuilder.updateWebBuilderFields()';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#web_builder', 'Web Builder');
		$breadcrumbs[] = new Breadcrumb('/WebBuilder/CreateTemplates', 'Create Templates');
		return $breadcrumbs;
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'Administer All Basic Pages',
			'Administer Library Basic Pages',
		]);
	}

	function canBatchEdit(): bool {
		return false;
	}

 
	function getActiveAdminSection(): string {
		return 'web_builder';
	}

    public function canAddNew(){
        return true;
    }

	public function canCopy() {
		return true;
	}

    public function canDelete() {
        return true;
	}

    public function canExportToCSV() {
        return false;
    }
}