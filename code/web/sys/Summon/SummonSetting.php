<?php

class SummonSetting extends DataObject {
	public $__table = 'summon_settings';
	public $id;
	public $name;
	public $summonApiProfile;
	public $summonApiUsername;
	public $summonApiPassword;
	public $summonApiSearchProfile;


	static function getObjectStructure($context = ''): array {

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
			'summonApiProfile' => [
				'property' => 'summonApiProfile',
				'type' => 'text',
				'label' => 'Summon API Profile',
				'description' => 'The profile used when connecting to the Summon API.',
				'hideInLists' => true,
			],
			'summonSearchProfile' => [
				'property' => 'summonSearchProfile',
				'type' => 'text',
				'label' => 'Summon Search Profile',
				'description' => 'The profile to use when linking to Summon',
				'hideInLists' => true,
			],
			'summonApiUsername' => [
				'property' => 'summonApiUsername',
				'type' => 'text',
				'label' => 'Summon API Username',
				'description' => 'The username to use when connecting to the Summon API',
				'hideInLists' => true,
			],
			'summonApiPassword' => [
				'property' => 'summonApiPassword',
				'type' => 'storedPassword',
				'label' => 'Summon API Password',
				'description' => 'The password to use when connecting to the Summon API',
				'hiedInLists' => true,
			],
		];

	}

	public function __get($name) {
		if ($name == "searchSettings") {
			return $this->getSearchSettings();
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * @return SummonSearchSetting[]
	 */
	public function getSearchSettings(): ?array {
		if (!isset($this->_searchSettings) && $this->id) {
			$this->_searchSettings = [];
			$obj = new SummonSearchSetting();
			$obj->settingId = $this->id;
			$obj->find();
			while ($obj->fetch()) {
				$this->_searchSettings[$obj->id] = clone($obj);
			}
		}
		return $this->_searchSettings;
	}

	public function __set($name, $value) {
		if ($name == "searchSettings") {
			$this->_searchSettings = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function update($context = '') {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveSearchSettings();
		}
		return true;
	}

	public function insert($context = '') {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			if (empty($this->_searchSettings)) {
				$searchSettings = new SummonSearchSetting();
				$searchSettings->settingId = $this->id;
				$searchSettings->name = 'default';


				$this->_searchSettings[] = $searchSettings;
			}
			$this->saveSearchSettings();
		}
		return $ret;
	}

	public function saveSearchSettings() {
		if (isset ($this->_searchSettings) && is_array($this->_searchSettings)) {
			$this->saveOneToManyOptions($this->_searchSettings, 'settingId');
			unset($this->_searchSettings);
		}
	}

	public function delete($useWhere = false) {
		$ret = parent::delete($useWhere);
		if ($ret) {
			$this->clearSearchSettings();
		}
		return $ret;
	}

	public function clearSearchSettings() {
		$searchSettings = $this->getSearchSettings();
		foreach ($searchSettings as $searchSetting) {
			$searchSetting->delete();
		}
		$this->clearOneToManyOptions('SummonSearchSetting', 'settingsId');
		$this->_searchSettings = [];
	}
}