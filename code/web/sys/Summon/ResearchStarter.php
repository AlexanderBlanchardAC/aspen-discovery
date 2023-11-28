<?php

require_once ROOT_DIR . '/RecordDrivers/SummonRecordDriver.php';

class ResearchStarter extends DataObject {
	public $__table = 'summon_research_starter';
	public $id;
	public $summonId;
	public $title;
	private $_recordDriver;

	function setRecordDriver(SummonRecordDriver $recordDriver) {
		$this->_recordDriver = $recordDriver;
		//Get the appropriate record
		$this->summonId = $this->_recordDriver->getUniqueID();
		if (!$this->find(true)) {
			$this->title = $this->_recordDriver->getTitle();
			if (strlen($this->title) > 255) {
				require_once ROOT_DIR . '/sys/Utils/StringUtils.php';
				$this->title = StringUtils::trimStringToLengthAtWordBoundary($this->title, 255, true);
			}
			$this->insert();
		}
	}

	function getDisplayHtml() {
		global $interface;
		$interface->assign('id', $this->id);
		$interface->assign('title', $this->_recordDriver->getTitle());
		$interface->assign('description', $this->_recordDriver->getDescription());
		$interface->assign('link', $this->_recordDriver->getLinkUrl());
		$interface->assign('image', $this->_recordDriver->getBookcoverUrl('medium'));

		return $interface->fetch('Summon/researchStarter.tpl');
	}

	function isHidden() {
		if (UserAccount::isLoggedIn() == false) {
			return false;
		} else {
			//Check to see if the active user hid this
			require_once ROOT_DIR . '/sys/Summon/ResearchStarterDismissal.php';
			$dismissal = new ResearchStarterDismissal();
			$dismissal->userId = UserAccount::getActiveUserId();
			$dismissal->researchStarterId = $this->id;
			return $dismissal->find(true);
		}
	}
}