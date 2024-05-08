<?php

class Template extends DataObject {
    public $__table = 'templates';
    public $id;
    public $templateName;
    public $templateContent;
    public $tempalteId;


    static function getObjectStructure($context = ''): array {
        
        return [
            'id' => [
                'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
            ],
            'templateId' => [
                'property' => 'templateId',
				'type' => 'text',
				'label' => 'Template ID',
				'description' => 'The unique ID assigned to the template',
            ],
            'templateName' => [
				'property' => 'templateName',
				'type' => 'text',
				'label' => 'Template Name',
				'description' => 'The Name assigned to the template',
			],
            'templateContent' => [
                'property' => 'templateContent',
                'type' => 'text',
                'label' => 'Template Content',
                'description' => 'The html content of the template',
            ],
        ];

    }
}