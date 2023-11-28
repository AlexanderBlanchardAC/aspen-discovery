<?php

require_once ROOT_DIR . '/RecordDrivers/SummonRecordDriver.php';

class Summon_AccessOnline extends Action {

    private $recordDriver;

	function launch() {
		global $interface;
		$id = urldecode($_REQUEST['id']);

		$this->recordDriver = new SummonRecordDriver($id);

		if ($this->recordDriver->isValid()) {
			//Make sure the user has been validated to view the record based on IP or login
			$activeIP = IPAddress::getActiveIp();
			$subnet = IPAddress::getIPAddressForIP($activeIP);
			$okToAccess = false;
			if ($subnet != false && $subnet->authenticatedForSummon) {
				$okToAccess = true;
			} else {
				$okToAccess = UserAccount::isLoggedIn();
			}

			if ($okToAccess) {
				//Track usage of the record
				require_once ROOT_DIR . '/sys/Summon/SummonRecordUsage.php';
				$summonRecordUsage = new SummonRecordUsage();
				global $aspenUsage;
				$summonRecordUsage->instance = $aspenUsage->instance;
				$summonRecordUsage->summonId = $id;
				$summonRecordUsage->year = date('Y');
				$summonRecordUsage->month = date('n');
				if ($summonRecordUsage->find(true)) {
					$summonRecordUsage->timesUsed++;
					$ret = $summonRecordUsage->update();
					if ($ret == 0) {
						echo("Unable to update times used");
					}
				} else {
					$summonRecordUsage->timesViewedInSearch = 0;
					$summonRecordUsage->timesUsed = 1;
					$summonRecordUsage->insert();
				}

				$userId = UserAccount::getActiveUserId();
				if ($userId) {
					//Track usage for the user
					require_once ROOT_DIR . '/sys/Summon/UserSummonUsage.php';
					$userSummonUsage = new UserSummonUsage();
					global $aspenUsage;
					$userSummonUsage->instance = $aspenUsage->instance;
					$userSummonUsage->userId = $userId;
					$userSummonUsage->year = date('Y');
					$userSummonUsage->month = date('n');

					if ($userSummonUsage->find(true)) {
						$userSummonUsage->usageCount++;
						$userSummonUsage->update();
					} else {
						$userSummonUsage->usageCount = 1;
						$userSummonUsage->insert();
					}
				}

				header('Location:' . $this->recordDriver->getRecordUrl());
				die();
			} else {
				require_once ROOT_DIR . '/services/MyAccount/Login.php';
				$launchAction = new MyAccount_Login();
				$_REQUEST['followupModule'] = 'Summon';
				$_REQUEST['followupAction'] = 'AccessOnline';
				$_REQUEST['recordId'] = $id;

				$error_msg = translate([
					'text' => 'You must be logged in to access content from Summon',
					'isPublicFacing' => true,
				]);
				$launchAction->launch($error_msg);
			}
		} else {
			$this->display('../Record/invalidRecord.tpl', 'Invalid Record', '');
			die();
		}
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		if (!empty($this->lastSearch)) {
			$breadcrumbs[] = new Breadcrumb($this->lastSearch, 'Article & Database Search Results');
		}
		$breadcrumbs[] = new Breadcrumb('', $this->recordDriver->getTitle());
		return $breadcrumbs;
	}
}
