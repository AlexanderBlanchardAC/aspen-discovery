<?php /** @noinspection PhpMissingFieldTypeInspection */


class APIUsage extends DataObject {
	public $__table = 'api_usage';
	public $id;
	public $instance;
	public $year;
	public $month;
	public $module;
	public $method;
	public $numCalls;
	public $libraryId;

	public function getUniquenessFields(): array {
		return [
			'instance',
			'year',
			'month',
			'module',
			'method',
			// 'libraryId',
		];
	}

	static function incrementStat($module, $method, /*$libraryId = null*/) : void {
		global $logger; 
		$logger->log("incrementStat called - method: " . $method, Logger::LOG_ERROR);
		try {
			$apiUsage = new APIUsage();
			$apiUsage->year = date('Y');
			$apiUsage->month = date('n');
			global $aspenUsage;
			$apiUsage->instance = $aspenUsage->getInstance();
			$apiUsage->module = $module;
			$apiUsage->method = $method;
			// $apiUsage->libraryId = $libraryId;
			$logger->log("Looking for existing record...", Logger::LOG_ERROR);

			if ($apiUsage->find(true)) {
				$apiUsage->numCalls++;
				$logger->log("Found existing record, updating. New count: " . $apiUsage->numCalls, Logger::LOG_ERROR);

				$apiUsage->update();
			} else {
				$apiUsage->numCalls = 1;
				$logger->log("No existing record, inserting new record", Logger::LOG_ERROR);

				$apiUsage->insert();
			}
			        $logger->log("incrementStat completed successfully", Logger::LOG_ERROR);

		} catch (PDOException) {
			//This happens if the table has not been created, ignore it
			        $logger->log("PDOException in incrementStat: " . $e->getMessage(), Logger::LOG_ERROR);

		} catch (Exception $e) {
			        $logger->log("Exception in incrementStat: " . $e->getMessage(), Logger::LOG_ERROR);

		}
	}

	public function okToExport(array $selectedFilters): bool {
		$okToExport = parent::okToExport($selectedFilters);
		if (in_array($this->instance, $selectedFilters['instances'])) {
			$okToExport = true;
		}
		return $okToExport;
	}
}