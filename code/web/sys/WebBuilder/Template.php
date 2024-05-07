<?php

class Template extends DataObject {
    public $__table = 'templates';
    public $id;
    public $templateName;
    public $templateContent;


    static function getObjectStructure($context = ''): array {
        
        return [
            'id' => [
                'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
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