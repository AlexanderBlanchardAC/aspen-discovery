<?php

class WebBuilder_GrapesJSEditor extends Action {
    /** @var GrapesPage */
    private $grapesPage;
  
    function __construct() {
        parent::__construct();
        require_once ROOT_DIR . '/sys/WebBuilder/GrapesPage.php';
    }

    function launch() {
        $this->display('grapesjs.tpl', '', '', false);
    }

    function getBreadCrumbs(): array {
        $breadcrumbs = [];
        $breadcrumbs[] = new Breadcrumb('/', 'Home');
        if ($this->grapesPage != null) {
            $breadcrumbs[] = new Breadcrumb('', $this->grapesPage->title, true);
        }
        return $breadcrumbs;
    }

}