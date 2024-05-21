<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/Indexing/IndexingProfile.php';

class ILS_IndexingProfiles extends ObjectEditor {
	function launch() {
		global $interface;
		$objectAction = isset($_REQUEST['objectAction']) ? $_REQUEST['objectAction'] : null;
		if ($objectAction == 'viewMarcFiles') {
			$id = $_REQUEST['id'];
			$interface->assign('id', $id);
			$files = [];
			$indexProfile = new IndexingProfile();
			if ($indexProfile->get($id) && !empty($indexProfile->marcPath)) {

				$marcPath = $indexProfile->marcPath;
				if ($handle = opendir($marcPath)) {
					while (false !== ($entry = readdir($handle))) {
						if ($entry != "." && $entry != "..") {
							$files[$entry] = filectime($marcPath . DIRECTORY_SEPARATOR . $entry);
						}
					}
					closedir($handle);
					$interface->assign('files', $files);
					$interface->assign('IndexProfileName', $indexProfile->name);
					$this->display('marcFiles.tpl', 'Marc Files');
				}
			}
		} elseif ($objectAction == 'loadDefaultBibFormatMappings') {
			$id = $_REQUEST['id'];
			$interface->assign('id', $id);
			$indexProfile = new IndexingProfile();
			if ($indexProfile->get($id) && !empty($indexProfile->marcPath)) {
				$defaultFormatMapValues = getTranslationMap('format');
				$defaultFormatBoostMapValues = getTranslationMap('format_boost');
				$defaultFormatCategoryMapValues = getTranslationMap('format_category');
				foreach ($defaultFormatMapValues as $originalValue => $mappedValue) {
					$formatMapValue = new FormatMapValue();
					$formatMapValue->value = $originalValue;
					$formatMapValue->indexingProfileId = $indexProfile->id;
					if (!$formatMapValue->find(true)) {
						$formatMapValue->format = $mappedValue;
						$formatMapValue->formatCategory = $defaultFormatCategoryMapValues[$originalValue] ?? 'Other';
						$formatMapValue->formatBoost = $defaultFormatBoostMapValues[$originalValue] ?? 1;
						$formatMapValue->insert();
					}
				}
			}
			parent::launch();
		} else {
			parent::launch();
		}
	}

	function getObjectType(): string {
		return 'IndexingProfile';
	}

	function getModule(): string {
		return "ILS";
	}

	function getToolName(): string {
		return 'IndexingProfiles';
	}

	function getPageTitle(): string {
		return 'ILS Indexing Information';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$list = [];

		$object = new IndexingProfile();
		$object->orderBy($this->getSort());
		$this->applyFilters($object);
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$object->find();
		while ($object->fetch()) {
			$list[$object->id] = clone $object;
		}

		return $list;
	}

	function getDefaultSort(): string {
		return 'name asc';
	}

	function getObjectStructure($context = ''): array {
		return IndexingProfile::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function canAddNew() {
		return true;
	}

	function canDelete() {
		return true;
	}

	function getInstructions(): string {
		return 'https://help.aspendiscovery.org/ilsintegration';
	}

	function getAdditionalObjectActions($existingObject): array {
		$actions = [];
		if ($existingObject && $existingObject->id != '') {
			$actions[] = [
				'text' => 'View MARC files',
				'url' => '/ILS/IndexingProfiles?objectAction=viewMarcFiles&id=' . $existingObject->id,
			];
			$actions[] = [
				'text' => 'Load Default Bib Format Mappings',
				'url' => '/ILS/IndexingProfiles?objectAction=loadDefaultBibFormatMappings&id=' . $existingObject->id,
			];
		}

		return $actions;
	}

	function getInitializationJs(): string {
		return 'return AspenDiscovery.Admin.updateIndexingProfileFields();';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ils_integration', 'ILS Integration');
		$breadcrumbs[] = new Breadcrumb('/ILS/IndexingProfiles', 'Indexing Profiles');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ils_integration';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer Indexing Profiles');
	}
}