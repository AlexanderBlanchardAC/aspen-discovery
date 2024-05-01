<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/Grapes/Page.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';


class GrapesWebBuilder_Pages extends ObjectEditor {	
	function getObjectType(): string {
		return 'Page';
	}

	function getToolName(): string {
		return 'Pages';
	}

	function getModule(): string {
		return 'GrapesWebBuilder';
	}

	function getPageTitle(): string {
		return 'Grapes JS Pages';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new Page();
		// $object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		// $this->applyFilters($object);
		// $object->orderBy($this->getSort());
		// $object->find();
		// $objectList = [];
		while ($object->fetch()) {
			$objectList[$object->id] = clone $object;
		}
		return $objectList;
	}

	function getDefaultSort(): string {
		return 'title asc';
	}

	function getObjectStructure($context = ''): array {
		return Page::getObjectStructure($context);
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
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#grapes_web_builder', 'Grapes Web Builder');
		$breadcrumbs[] = new Breadcrumb('/GrapesWebBuilder/Pages', 'Pages');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'grapes_web_builder';
	}

	function canView(): bool {
        return true;
	}
}