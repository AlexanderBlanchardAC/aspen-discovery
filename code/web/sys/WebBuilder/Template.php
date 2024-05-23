<?php
require_once ROOT_DIR . '/sys/WebBuilder/LibraryBasicPage.php';

class Template extends DataObject {
    public $__table = 'templates';
    public $id;
    public $templateName;
    public $templateContent;
    public $templateId;
    private $_libraries;


    public function getUniquenessFields(): array {
		return [
			'id',
		];
	}
    static function getObjectStructure($context = ''): array {
        $libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Basic Pages'));

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
        ];
    }


    function getAdditionalListActions(): array {
		require_once ROOT_DIR . '/services/WebBuilder/Templates.php';
        $objectActions = [];
        // $objectActions[] =
    //     [
    //        'text' => 'Open Editor',
    //        'url' => '/WebBuilder/GrapesJSTemplates?objectAction=edit&id=' . $id,
    //    ];
    
        return $objectActions;
    }

    public function getFormattedContents() {
		require_once ROOT_DIR . '/sys/Parsedown/AspenParsedown.php';
		$parsedown = AspenParsedown::instance();
		$parsedown->setBreaksEnabled(true);
		return $parsedown->parse();
	}

    /**
     * @return array
     */
    static function getTemplateList(): array {
        $template = new Template();
        $template->orderBy('templateName');
        $template->find();
        $templateList = [];
        while ($template->fetch()){
            $currentTemplate = new stdClass();
            $currentTemplate->id = $template->id;
            $currentTemplate->templateName = $template->templateName;
            $templateList[$currentTemplate->id] = $currentTemplate->templateName;
        }
        return $templateList;
    }

    function getTemplateById($id) {
      $template = new Template();
      $template->id = $id;
      if ($template->find()) {
        return true;
      }
      return false;
    }

    function saveAsTemplate(){
        $newGrapesTemplate = json_decode(file_get_contents("php://input"), true);
        $html = $newGrapesTemplate['html'];
        $template = new Template();
        $template->htmlData = $html;
        $template->insert();

    }

 
}