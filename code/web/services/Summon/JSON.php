<?php

require_once ROOT_DIR . '/JSON_Action.php';

class Summon_JSON extends JSON_Action {
	/** @noinspection PhpUnused */
	public function dismissResearchStarter(): array {
		if (!isset($_REQUEST['id'])) {
			return [
				'success' => false,
				'message' => 'ID was not provided',
			];
		}
		$result = [
			'success' => false,
			'message' => translate([
				'text' => 'Unknown Error',
				'isPublicFacing' => true,
			]),
		];
		$id = $_REQUEST['id'];

		require_once ROOT_DIR . '/sys/Summon/ResearchStarter.php';
		$researchStarter = new ResearchStarter();
		$researchStarter->id = $id;
		if ($researchStarter->find(true)) {
			require_once ROOT_DIR . '/sys/Summon/ResearchStarterDismissal.php';
			$dismissal = new ResearchStarterDismissal();
			$dismissal->researchStarterId = $id;
			$dismissal->userId = UserAccount::getActiveUserId();
			$dismissal->insert();
			$result = [
				'success' => true,
				'title' => 'Research Starter Dismissed',
				'message' => "This research starter will not be shown again.  You can hide all research starters by editing <a href='/MyAccount/MyPreferences'>your preferences</a>.",
			];
		} else {
			$result['message'] = 'Could not find that Research Starter';
		}

		return $result;
	}

	/** @noinspection PhpUnused */
	public function trackEdsUsage(): array {
		if (!isset($_REQUEST['id'])) {
			return [
				'success' => false,
				'message' => 'ID was not provided',
			];
		}
		$id = $_REQUEST['id'];

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

		return [
			'success' => true,
			'message' => 'Updated usage for Summon record ' . $id,
		];
	}

	/** @noinspection PhpUnused */
	function getResearchStarters(): array {
		global $enabledModules;
		if (array_key_exists('Summon', $enabledModules)) {
			require_once ROOT_DIR . '/sys/SearchObject/SummonSearcher.php';
			$edsSearcher = new SearchObject_SummonSearcher();
			$researchStarters = $summonSearcher->getResearchStarters($_REQUEST['lookfor']);
			$result = [
				'success' => true,
				'researchStarters' => '',
			];
			foreach ($researchStarters as $researchStarter) {
				$result['researchStarters'] .= $researchStarter->getDisplayHtml();
			}
			return $result;
		} else {
			return [
				'success' => true,
				'researchStarters' => '',
			];
		}
	}

	/** @noinspection PhpUnused */
	function getTitleAuthor(): array {
		$result = [
			'success' => false,
			'title' => 'Unknown',
			'author' => 'Unknown',
		];
		require_once ROOT_DIR . '/RecordDrivers/SummonRecordDriver.php';
		$id = $_REQUEST['id'];
		if (!empty($id)) {
			$recordDriver = new SummonRecordDriver($id);
			if ($recordDriver->isValid()) {
				$result['success'] = true;
				$result['title'] = $recordDriver->getTitle();
				$result['author'] = $recordDriver->getAuthor();
			}
		}
		return $result;
	}
}