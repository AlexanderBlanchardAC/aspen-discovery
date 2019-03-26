<?php

require_once ROOT_DIR . '/sys/DB/DataObject.php';
class OpenArchivesCollection extends DataObject
{
    public $__table = 'open_archives_collection';
    public $id;
    public $name;
    public $baseUrl;
    public $setName;
    public $fetchFrequency;
    public $lastFetched;

    static function getObjectStructure(){
        return [
            'name' => array('property'=>'name', 'type'=>'text', 'label'=>'Name', 'description'=>'A name to identify the open archives collection in the system', 'size'=>'100'),
            'baseURL' => array('property'=>'baseUrl', 'type'=>'url', 'label'=>'Base URL', 'description'=>'The url of the open archives site', 'size'=>'255'),
            'setName' => array('property'=>'setName', 'type'=>'text', 'label'=>'Set Name (separate multiple values with commas)', 'description'=>'The name of the set to harvest', 'size'=>'100'),
            'fetchFrequency' => array('property'=>'fetchFrequency', 'type'=>'enum', 'values' => ['hourly'=>'Hourly', 'daily'=>'Daily', 'weekly'=>'Weekly', 'monthly'=>'Monthly', 'yearly'=>'Yearly', 'once'=>'Once'], 'label'=>'Frequency to Fetch', 'description'=>'How often the records should be fetched'),
            'lastFetched' => array('property'=>'lastFetched', 'type'=>'integer', 'label'=>'Last Fetched (clear to force a new fetch)', 'description'=>'When the record was last fetched'),
        ];
    }
}