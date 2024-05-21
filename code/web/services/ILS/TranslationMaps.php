<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/Indexing/TranslationMap.php';

class ILS_TranslationMaps extends ObjectEditor {
	function launch() {
		global $interface;
		$objectAction = isset($_REQUEST['objectAction']) ? $_REQUEST['objectAction'] : null;
		if ($objectAction == 'loadFromFile') {
			$id = $_REQUEST['id'];
			$translationMap = new TranslationMap();
			if ($translationMap->get($id)) {
				$interface->assign('mapName', $translationMap->name);
				$interface->assign('additionalObjectActions', $this->getAdditionalObjectActions($translationMap));
			}
			$interface->assign('id', $id);
			$this->display('../ILS/importTranslationMapData.tpl', "Import Translation Map Data");
			exit();
		} elseif ($objectAction == 'doAppend' || $objectAction == 'doReload') {
			$id = $_REQUEST['id'];

			$translationMapData = $_REQUEST['translationMapData'];
			//Truncate the current data
			$translationMap = new TranslationMap();
			$translationMap->id = $id;
			if ($translationMap->find(true)) {
				$newValues = [];
				if ($objectAction == 'doReload') {
					/** @var TranslationMapValue $value */
					/** @noinspection PhpUndefinedFieldInspection */
					foreach ($translationMap->translationMapValues as $value) {
						$value->delete();
					}
					/** @noinspection PhpUndefinedFieldInspection */
					$translationMap->translationMapValues = [];
					$translationMap->update();
				} else {
					/** @noinspection PhpUndefinedFieldInspection */
					foreach ($translationMap->translationMapValues as $value) {
						$newValues[$value->value] = $value;
					}
				}

				//Parse the new data
				$data = preg_split('/\\r\\n|\\r|\\n/', $translationMapData);

				foreach ($data as $dataRow) {
					if (strlen(trim($dataRow)) != 0 && $dataRow[0] != '#') {
						$dataFields = preg_split('/[,=]/', $dataRow, 2);
						$value = trim(str_replace('"', '', $dataFields[0]));
						if (array_key_exists($value, $newValues)) {
							$translationMapValue = $newValues[$value];
						} else {
							$translationMapValue = new TranslationMapValue();
						}
						$translationMapValue->value = $value;
						$translationMapValue->translation = trim(str_replace('"', '', $dataFields[1]));
						$translationMapValue->translationMapId = $id;

						$newValues[$translationMapValue->value] = $translationMapValue;
					}
				}
				/** @noinspection PhpUndefinedFieldInspection */
				$translationMap->translationMapValues = $newValues;
				$translationMap->update();
			} else {
				$interface->assign('error', "Sorry we could not find a translation map with that id");
			}

			//Show the results
			$_REQUEST['objectAction'] = 'edit';
		} elseif ($objectAction == 'viewAsINI') {
			$id = $_REQUEST['id'];
			$translationMap = new TranslationMap();
			$translationMap->id = $id;
			if ($translationMap->find(true)) {
				$interface->assign('id', $id);
				$interface->assign('additionalObjectActions', $this->getAdditionalObjectActions($translationMap));
				/** @noinspection PhpUndefinedFieldInspection */
				$interface->assign('translationMapValues', $translationMap->translationMapValues);
				$this->display('../ILS/viewTranslationMapAsIni.tpl', 'View Translation Map Data');
				exit();
			} else {
				$interface->assign('error', "Sorry we could not find a translation map with that id");
			}
		} elseif ($objectAction == 'downloadAsINI') {
			$id = $_REQUEST['id'];
			$translationMap = new TranslationMap();
			$translationMap->id = $id;
			if ($translationMap->find(true)) {
				$interface->assign('id', $id);

				$translationMapAsCsv = '';
				/** @var TranslationMapValue $mapValue */
				foreach ($translationMap->translationMapValues as $mapValue) {
					$translationMapAsCsv .= $mapValue->value . ' = ' . $mapValue->translation . "\r\n";
				}
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header("Content-Disposition: attachment; filename={$translationMap->name}.ini");
				header('Content-Transfer-Encoding: utf-8');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');

				header('Content-Length: ' . strlen($translationMapAsCsv));
				ob_clean();
				flush();
				echo $translationMapAsCsv;
				exit();
			} else {
				$interface->assign('error', "Sorry we could not find a translation map with that id");
			}
		}
		parent::launch();
	}

	function getObjectType(): string {
		return 'TranslationMap';
	}

	function getModule(): string {
		return "ILS";
	}

	function getToolName(): string {
		return 'TranslationMaps';
	}

	function getPageTitle(): string {
		return 'Translation Maps';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$list = [];

		$object = new TranslationMap();
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
		return TranslationMap::getObjectStructure($context);
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

	/**
	 * @param TranslationMap $existingObject
	 * @return array
	 */
	function getAdditionalObjectActions($existingObject): array {
		$actions = [];
		if ($existingObject && $existingObject->id != '') {
			$actions[] = [
				'text' => 'Load From CSV/INI',
				'url' => '/ILS/TranslationMaps?objectAction=loadFromFile&id=' . $existingObject->id,
			];
			$actions[] = [
				'text' => 'View as INI',
				'url' => '/ILS/TranslationMaps?objectAction=viewAsINI&id=' . $existingObject->id,
			];
			$actions[] = [
				'text' => 'Download as INI',
				'url' => '/ILS/TranslationMaps?objectAction=downloadAsINI&id=' . $existingObject->id,
			];
		}

		return $actions;
	}

    function getInstructions(): string {
        return 'https://help.aspendiscovery.org/ilsintegration';
    }

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ils_integration', 'ILS Integration');
		if (!empty($this->activeObject) && $this->activeObject instanceof TranslationMap) {
			$breadcrumbs[] = new Breadcrumb('/ILS/IndexingProfiles?objectAction=edit&id=' . $this->activeObject->indexingProfileId, 'Indexing Profile');
		}
		$breadcrumbs[] = new Breadcrumb('/ILS/TranslationMaps', 'Translation Maps');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ils_integration';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer Translation Maps');
	}
}