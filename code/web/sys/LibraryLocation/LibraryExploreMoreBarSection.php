<?php

require_once ROOT_DIR . '/sys/LibraryLocation/ExploreMoreBarSection.php';

class LibraryExploreMoreBarSection extends ExploreMoreBarSection {
    public $__table = 'library_explore_more_bar_section';
    public $libraryId;

    static function getobjectStructure($context = ''): array {
        $library = new Library();
        $library->orderBy('displayName');
        if (!UserAccount::userHasPermission('Administer All Libraries')) {
			$homeLibrary = Library::getPatronHomeLibrary();
			$library->libraryId = $homeLibrary->libraryId;
		}
		$library->find();
		$libraryList = [];
		while ($library->fetch()) {
			$libraryList[$library->libraryId] = $library->displayName;
		}

        $structure = parent::getObjectStructure($context);
        $structure['libraryId'] = [
            'property' => 'libraryId',
			'type' => 'enum',
			'values' => $libraryList,
			'label' => 'Library',
			'description' => 'The id of a library',
        ];

        return $structure;
    }
}
