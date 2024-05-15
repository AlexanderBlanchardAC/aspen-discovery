<?php

class CreatedGrapesPage extends DataObject {
    public $__table = 'created_grapes_page';
    public $id;
    public $title;
    public $urlAlias;
    public $htmlData;
    public $cssData;
    public $assets;
    public $components;
    public $styles;

    static function getObjectStructure($content = ''): array {
        return [
            'id' => [
                'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
            ],
            'title' => [
				'property' => 'title',
				'type' => 'text',
				'label' => 'Title',
				'description' => 'The title of the page',
				'size' => '40',
				'maxLength' => 100,
			],
			'urlAlias' => [
				'property' => 'urlAlias',
				'type' => 'text',
				'label' => 'URL Alias (no domain, should start with /)',
				'description' => 'The url of the page (no domain name)',
				'size' => '40',
				'maxLength' => 100,
			],
            'htmlData' => [
                'property' => 'htmlData',
                'type' => 'text',
                'label' => 'html',
                'description' => 'Grapes Page html'
            ],
            'cssData' => [
                'property' => 'cssData',
                'type' => 'text',
                'label' => 'css',
                'description' => 'Grapes Page css'
            ],
            'components' => [
                'property' => 'components',
                'type' => 'text',
                'label' => 'components',
                'description' => 'Grapes Page Components'
            ],
            'styles' => [
                'property' => 'styles',
                'type' => 'text',
                'label' => 'styles',
                'description' => 'Grapes Page Styles'
            ],
        ];
    }	
}
?>


