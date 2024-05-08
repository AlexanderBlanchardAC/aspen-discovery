<?php
require_once ROOT_DIR . '/sys/WebBuilder/LibraryBasicPage.php';
require_once ROOT_DIR . '/sys/DB/LibraryLinkedObject.php';
class GrapesPage extends DB_LibraryLinkedObject {
	public $__table = 'grapes_web_builder';
	public $id;
	public $title;
	public $urlAlias;
	public $teaser;
    public $pageType;
	public $templateNames;
	public $templateId;
	private $_libraries;


	public function getUniquenessFields(): array {
		return [
			'id',
		];
	}

	static function getObjectStructure($context = ''): array {
        // require_once ROOT_DIR . '/services/WebBuilder/Templates.php';
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Basic Pages'));

		require_once ROOT_DIR . '/services/WebBuilder/Templates.php';
        $templateNames = [];
        $templateIds = [];
        $templateOptions = [];

        $templateObject = new Templates();
        $templates = $templateObject->getTemplates();
        foreach ($templates as $template){
            $templateName = $template['templateName'];
            $templateId = $template['templateId'];
            $templateNames[$templateId] = [
                'id' => $templateId,
                'name' => $templateName,
            ];
        }

        array_unshift($templateNames, [
            'id' => null, 
            'name' => "No Template"
        ]);

        usort($templateNames, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

	
       




        return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id within the database',
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
			'templateId' => [
				'property' => 'templateId',
				'type' => 'hidden',
				'description' => 'The unique Id of the template.',
				'hideInLists'=> true,
			],
			'teaser' => [
				'property' => 'teaser',
				'type' => 'textarea',
				'label' => 'Teaser',
				'description' => 'Teaser for page content',
				'maxLength' => 512,
				'hideInLists' => true,
			],
			'templatesSection' => [
				'property' => 'templatesSection',
				'type' => 'section',
				'label' => 'Select a Template',
				'hideInLists' => true,
				'properties' => [
					'availableTemplates' => [
						'property' => 'availableTemplates',
						'type' => 'templates',
						'label' => 'Templates',
						'required' => true,
						'values' => $templateNames,					],
				],
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that use these settings',
				'values' => $libraryList,
				'hideInLists' => true,
			],
		];
	}

    //TODO:: Work out why none of the delete options are working - all causing an AJAX error
    //TODO:: Untangle what from info for tempaltes needs to be / is going to each table. PageType in Grapes 
    //is getting name and sort of contents of template. 
    //TemplateContent column is remaining null. 

    // - templates - get the contents from this table for the template name that matches the one chosen
    // - add this content to the grapes table, also add the tempalte name
    // - add a column to both tables of templateID
    // - create a unique id for each template when insterted to db and use this to get the correct content for the grapes table

	public function getFormattedContents() {
		require_once ROOT_DIR . '/sys/Parsedown/AspenParsedown.php';
		$parsedown = AspenParsedown::instance();
		$parsedown->setBreaksEnabled(true);
		return $parsedown->parse();
	}

	public function applyAvailableTempltes() {

	}

    function getAdditionalListActions(): array {
        $objectActions = [];
    
        $objectActions[] =
         [
            'text' => 'Delete',
            'url' => '/your/delete/endpoint?id=' . $this->id,
            'confirm' => 'Are you sure you want to delete this item?', 
        ];

        $objectActions[] =
        [
           'text' => 'Open Editor',
           'url' => '',
       ];

    
        return $objectActions;
    }
    

	public function insert($context = '') {
		$this->lastUpdate = time();
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return $ret;
	}

	public function update($context = '') {
		$this->lastUpdate = time();
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return $ret;
	}

	public function __get($name) {
		if ($name == "libraries") {
			return $this->getLibraries();
         } else {
			return parent::__get($name);
		}
	}

	public function __set($name, $value) {
		if ($name == "libraries") {
			$this->_libraries = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function delete($useWhere = false) {
		$ret = parent::delete($useWhere);
		if ($ret && !empty($this->id)) {
			$this->clearLibraries();
		}
		return $ret;
	}

	public function getLibraries(): ?array {
		if (!isset($this->_libraries) && $this->id) {
			$this->_libraries = [];
			$libraryLink = new LibraryBasicPage();
			$libraryLink->grapesPageId = $this->id;
			$libraryLink->find();
			while ($libraryLink->fetch()) {
				$this->_libraries[$libraryLink->libraryId] = $libraryLink->libraryId;
			}
		}
		return $this->_libraries;
	}

	public function saveLibraries() {
		if (isset($this->_libraries) && is_array($this->_libraries)) {
			$this->clearLibraries();

			foreach ($this->_libraries as $libraryId) {
				$libraryLink = new LibraryBasicPage();

				$libraryLink->grapesPageId = $this->id;
				$libraryLink->libraryId = $libraryId;
				$libraryLink->insert();
			}
			unset($this->_libraries);
		}
    }

	private function clearLibraries() {
		//Delete links to the libraries
		$libraryLink = new LibraryBasicPage();
		$libraryLink->grapesPageId = $this->id;
		return $libraryLink->delete(true);
	}


	public function canView(): bool {
		
			return true;
		
	}

    public function canDelete(): bool {
        return true;
    }

    public function canEdit(): bool {
        return false;
    }
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Add an event listener to the dropdown
    $('select[name="templateNames"]').change(function() {
        // Get the selected option
        var selectedOption = $(this).find('option:selected');

        // Debugging: Log the selected option and its data attributes
        console.log('Selected Option:', selectedOption);
        console.log('Data Attributes:', selectedOption.data());

        // Retrieve the template name and ID from the selected option
        var selectedTemplateName = selectedOption.text();
        var templateId = selectedOption.data('template-id');

        console.log('Selected Template Name:', selectedTemplateName);
        console.log('Selected Template ID:', templateId);

        // Update the value of the hidden input field (templateId) with the selected template ID
        $('input[name="templateId"]').val(templateId);
    });
});






</script>

