<?php
require_once ROOT_DIR . '/sys/Summon/SummonDatabase.php';


class SummonSearchSetting extends DataObject {
	public $__table = 'summon_search_options';
	public $id;
	public $name;
	public $settingId;
	private $_libraries;
	private $_locations;
	private $_databases;

	static function getObjectStructure($context = ''): array {
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
		$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer All Libraries') || UserAccount::userHasPermission('Administer Home Library Locations'));

		require_once ROOT_DIR . '/sys/Summon/SummonDatabase.php';
		$databaseSearchStructure = SummonDatabase::getObjectStructure($context);

		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'maxLength' => 50,
				'description' => 'A name for these settings',
				'required' => true,
			],
			'databases' => [
				'property' => 'databases',
				'type' => 'oneToMany',
				'label' => 'Databases',
				'description' => 'Databases that are searched',
				'keyThis' => 'id',
				'keyOther' => 'browseCategoryId',
				'subObjectType' => 'SummonDatabase',
				'structure' => $databaseSearchStructure,
				'sortable' => false,
				'storeDb' => true,
				'allowEdit' => true,
				'canEdit' => true,
				'canAddNew' => false,
				'canDelete' => false,
			],

			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that use this setting',
				'values' => $libraryList,
			],

			'locations' => [
				'property' => 'locations',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Locations',
				'description' => 'Define locations that use this setting',
				'values' => $locationList,
			],
		];
	}

	public function __get($name) {
		if ($name == "libraries") {
			if (!isset($this->_libraries) && $this->id) {
				$this->_libraries = [];
				$obj = new Library();
				$obj->summonSearchSettingId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_libraries[$obj->libraryId] = $obj->libraryId;
				}
			}
			return $this->_libraries;
		} elseif ($name == "locations") {
			if (!isset($this->_locations) && $this->id) {
				$this->_locations = [];
				$obj = new Location();
				$obj->summonSearchSettingId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_locations[$obj->locationId] = $obj->locationId;
				}
			}
			return $this->_locations;
		} elseif ($name == "databases") {
			return $this->getDatabases();
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * @return SummonDatabase[]
	 */
	public function getDatabases(): array {
		if (!isset($this->_databases)) {
			$this->_databases = [];
			if ($this->id) {
				$obj = new SummonDatabase();
				$obj->searchSettingId = $this->id;
				$obj->orderBy('displayName');
				$obj->find();
				while ($obj->fetch()) {
					$this->_databases[$obj->id] = clone $obj;
				}
			}
		}
		return $this->_databases;
	}

	public function __set($name, $value) {
		if ($name == "libraries") {
			$this->_libraries = $value;
		} elseif ($name == "locations") {
			$this->_locations = $value;
		} elseif ($name == "databases") {
			$this->_databases = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	/**
	 * @return int|bool
	 */
	public function update($context = '') {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLibraries();
			$this->saveLocations();
			$this->saveDatabases();
		}
		return $ret;
	}

	public function insert($context = '') {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLibraries();
			$this->saveLocations();
			$this->updateDatabasesFromSummon();
			$this->saveDatabases();
		}
		return $ret;
	}

	public function delete($useWhere = false) {
		if (!$useWhere) {
			$obj = new Library();
			$obj->summonSearchSettingId = $this->id;
			$obj->find();
			$libraries = [];
			while ($obj->fetch()) {
				$libraries[] = clone $obj;
			}
			foreach ($libraries as $library) {
				$library->summonSearchSettingId = -1;
				$library->update();
			}
			$obj = new Location();
			$obj->summonSearchSettingId = $this->id;
			$obj->find();
			$locations = [];
			while ($obj->fetch()) {
				$locations[] = clone $obj;
			}
			foreach ($locations as $location) {
				$location->summonSearchSettingId = -2;
				$location->update();
			}
			$this->clearOneToManyOptions('SummonDatabase', 'searchSettingId');
		}
		return parent::delete($useWhere);
	}

	public function updateDatabasesFromSummon() {
		$currentDatabases = $this->getDatabases();
		/** @var SearchObject_SummonSearcher $summonSearch */
		$summonSearch = SearchObjectFactory::initSearchObject('Summon');

		$summonSettings = new SummonSetting();
		$summonSettings->id = $this->settingId;
		if ($summonSettings->find(true)) {
			$summonSearch->setSettings($summonSettings);
		}

		$databaseList = $summonSearch->getDatabases();
		//Get a list of all databases that exist to check for things that have been removed.
		$removedDatabases = [];
		foreach ($currentDatabases as $currentDatabase) {
			$removedDatabases[$currentDatabase->shortName] = $currentDatabase;
		}
		foreach ($databaseList as $shortName => $databaseInfo) {
			unset ($removedDatabases[$shortName]);
			$foundDatabase = false;
			foreach ($currentDatabases as $dbInfo) {
				if ($dbInfo->shortName == $shortName) {
					$foundDatabase = true;
				}
			}
			if (!$foundDatabase) {
				$newDatabase = new SummonDatabase();
				$newDatabase->shortName = $shortName;
				$newDatabase->searchSettingId = $this->id;
				$newDatabase->displayName = $databaseInfo['longName'];
				$newDatabase->allowSearching = true;
				if ($databaseInfo['hasRelevancySort'] && $databaseInfo['hasDateSort']) {
					$newDatabase->hasDateAndRelevancySorting = true;
					$newDatabase->searchByDefault = true;
				} else {
					$newDatabase->hasDateAndRelevancySorting = false;
					$newDatabase->searchByDefault = false;
				}
				if (in_array($shortName, [
					'a9h',
					'bth',
					'f6h',
					'cmedm',
					'imh',
					'aph',
					'buh',
				])) {
					$newDatabase->showInExploreMore = true;
					$newDatabase->showInCombinedResults = true;
				}
				$newDatabase->insert();
			}
		}

		foreach ($removedDatabases as $databaseInfo) {
			$databaseInfo->delete();
		}
	}

	public function saveDatabases() {
		if (isset ($this->_databases) && is_array($this->_databases)) {
			$this->saveOneToManyOptions($this->_databases, 'searchSettingId');
			unset($this->_databases);
		}
	}

	public function saveLibraries() {
		if (isset ($this->_libraries) && is_array($this->_libraries)) {
			$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
			foreach ($libraryList as $libraryId => $displayName) {
				$library = new Library();
				$library->libraryId = $libraryId;
				$library->find(true);
				if (in_array($libraryId, $this->_libraries)) {
					//We want to apply the scope to this library
					if ($library->summonSearchSettingId != $this->id) {
						$library->summonSearchSettingId = $this->id;
						$library->update();
					}
				} else {
					//It should not be applied to this scope. Only change if it was applied to the scope
					if ($library->summonSearchSettingId == $this->id) {
						$library->summonSearchSettingId = -1;
						$library->update();
					}
				}
			}
			unset($this->_libraries);
		}
	}

	public function saveLocations() {
		if (isset ($this->_locations) && is_array($this->_locations)) {
			$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer All Libraries') || UserAccount::userHasPermission('Administer Home Library Locations'));
			/**
			 * @var int $locationId
			 * @var Location $location
			 */
			foreach ($locationList as $locationId => $displayName) {
				$location = new Location();
				$location->locationId = $locationId;
				$location->find(true);
				if (in_array($locationId, $this->_locations)) {
					//We want to apply the scope to this library
					if ($location->summonSearchSettingId != $this->id) {
						$location->summonSearchSettingId = $this->id;
						$location->update();
					}
				} else {
					//It should not be applied to this scope. Only change if it was applied to the scope
					if ($location->summonSearchSettingId == $this->id) {
						$library = new Library();
						$library->libraryId = $location->libraryId;
						$library->find(true);
						if ($library->summonSearchSettingId != -1) {
							$location->summonSearchSettingId = -1;
						} else {
							$location->summonSearchSettingId = -2;
						}
						$location->update();
					}
				}
			}
			unset($this->_locations);
		}
	}

	// public function getEditLink($context): string {

	// }

	/**
	 * @return string[]
	 */
	public function getDefaultSearchDatabases(): array {
		$allDatabases = $this->getDatabases();
		$defaultSearchDatabases = [];
		foreach ($allDatabases as $dbInfo) {
			if ($dbInfo->allowSearching && $dbInfo->searchByDefault) {
				$defaultSearchDatabases[$dbInfo->shortName] = $dbInfo->shortName;
			}
		}
		return $defaultSearchDatabases;
	}
} 