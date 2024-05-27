<?php
require_once ROOT_DIR . '/sys/WebBuilder/LibraryGrapesPage.php';
require_once ROOT_DIR . '/sys/DB/LibraryLinkedObject.php';
require_once ROOT_DIR . '/sys/WebBuilder/Template.php';
class GrapesPage extends DB_LibraryLinkedObject {
	public $__table = 'grapes_web_builder';
	public $id;
	public $title;
	public $urlAlias;
	public $teaser;
    public $pageType;
	public $templatesSelect;
	public $templateNames;
	private $_libraries;

	/** @var Template[] */
	private $_templates;

	public function getUniquenessFields(): array {
		return [
			'id',
		];
	}

	static function getObjectStructure($context = ''): array {
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Basic Pages'));
		$templateList = Template::getTemplateList();
		require_once ROOT_DIR . '/services/WebBuilder/Templates.php';
        $templateNames = [];
        $templateIds = [];

        // $templateObject = new Template();
        // $templates = $templateObject->getTemplates();
		// $templateOptions = [];

        // foreach ($templates as $template){
        //     $templateName = $template->templateName;
        //     $templateId = $template->id;
		// 	$templateContent = $template->templateContent;
        //     $templateOptions[] = [
        //         'id' => $templateId,
        //         'name' => $templateName,
		// 		'content' =>$templateContent,
        //     ];
		// 	$templateNames[$templateId] = $templateName;
		// 	$templateContents[$templateId] = $templateContent;
		// 	// $templateIds[$templateId] = $tempalteId;
        // }

        // array_unshift($templateOptions, [
        //     'id' => null, 
        //     'name' => "No Template"
        // ]);
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
			'teaser' => [
				'property' => 'teaser',
				'type' => 'textarea',
				'label' => 'Teaser',
				'description' => 'Teaser for page content',
				'maxLength' => 512,
				'hideInLists' => true,
			],
			'templatesSelect' => [
				'property' => 'templatesSelect',
				'type' => 'enum',
				'label' => 'Templates',
				'required' => true,
				// 'values' => $templateNames,	
				'values' => $templateList,
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
		if($context == 'addNew') {
			unset($structure['templateSelect']);
		}

		if($context != 'addNew') {
			unset($structure['templateContent']);
		}
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

		$tplFilePath = $this->getTplFilePath();
		if (file_exists($tplFilePath)) {
			$tplContent = file_get_contents($tplFilePath);
			return $parsedown->parse($tplContent);
		} else {
			return 'Content file not found';
		}
	}

	public function getTplFilePath() {
		$relativePath = 	'code/web/interface/themes/responsive/WebBuilder/grapesjs.tpl';
		return $relativePath;
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
         } elseif ($name == "templateContent") {
			return $this->getTemplates();
		 } else {
			return parent::__get($name);
		}
	}

	public function __set($name, $value) {
		if ($name == "libraries") {
			$this->_libraries = $value;
		} elseif ($name == "templateContent") {
			$this->_templateContent = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function delete($useWhere = false) {
		$ret = parent::delete($useWhere);
		if ($ret && !empty($this->id)) {
			$this->clearLibraries();
			$this->clearTemplateContent();
		}
		return $ret;
	}

	public function getLibraries(): ?array {
		if (!isset($this->_libraries) && $this->id) {
			$this->_libraries = [];
			$libraryLink = new LibraryGrapesPage();
			$libraryLink->grapesPageId = $this->id;
			$libraryLink->find();
			while ($libraryLink->fetch()) {
				$this->_libraries[$libraryLink->libraryId] = $libraryLink->libraryId;
			}
		}
		return $this->_libraries;
	}

	public function getTemplates() {
		if (is_null($this->_templates)) {
			$this->_templates = [];
			require_once ROOT_DIR . '/sys/WebBuilder/Template.php';
			$template = new Template();
			if ($this->id) {
				if (!empty($this->templatesSelect)) {
					$template = new Template();
					$template->id = $this->templatesSelect;
					if ($template->find(true)) {
						$this->_templates[$template->id] = clone $template;
					}
				}
			}
			/** @noinspection SqlResolve */
			// $template->query("SELECT templates.* FROM templates INNER JOIN grapes_web_builder ON templates.id = grapes_web_builder.templatesSelect WHERE templates.id = grapes_web_builder.templatesSelect");
			$template->query("SELECT templates.htmlData, templates.cssData FROM templates INNER JOIN grapes_web_builder ON templates.id = grapes_web_builder.templatesSelect WHERE grapes_web_builder.id = " .(int)$this->id);
			
			// $template->query("SELECT templates.*, grapes_web_builder.templateContent FROM templates INNER JOIN grapes_web_builder ON templates.id = grapes_web_builder.templatesSelect WHERE grapes_web_builder.grapes_page_id = " . (int)$this->id);

			while ($template->fetch()) {
				$this->_templates[$template->id] = new Template();
				$this->_templates[$template->id]->htmlData = $template->htmlData;
				$this->_templates[$template->id]->cssData = $template->cssData;
			}
			
			// while ($template->fetch()) {
			// 	$this->_templates[$template->id] = clone $template;
			// }
		}
		return $this->_templates;
	}

	public function saveLibraries() {
		if (isset($this->_libraries) && is_array($this->_libraries)) {
			$this->clearLibraries();

			foreach ($this->_libraries as $libraryId) {
				$libraryLink = new LibraryGrapesPage();

				$libraryLink->grapesPageId = $this->id;
				$libraryLink->libraryId = $libraryId;
				$libraryLink->insert();
			}
			unset($this->_libraries);
		}
    }

	private function clearLibraries() {
		//Delete links to the libraries
		$libraryLink = new LibraryGrapesPage();
		$libraryLink->grapesPageId = $this->id;
		return $libraryLink->delete(true);
	}

	private function clearTemplateContent() {
		$template = new Template();
		$template->id = $this->id;
		return $template->delete(true);
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


