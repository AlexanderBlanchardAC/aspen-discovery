<?php
require_once ROOT_DIR . '/sys/DB/DataObject.php';

class Page extends DataObject {
	public $__table = 'new_page';
	public $id;
	public $title;

	function getEncryptedFieldNames(): array {
		return ['summonApiPassword'];
	}

	public static function getObjectStructure($context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'title' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'maxLength' => 100,
				'description' => 'A name for these settings',
				'required' => true,
			],
		];
	}
}