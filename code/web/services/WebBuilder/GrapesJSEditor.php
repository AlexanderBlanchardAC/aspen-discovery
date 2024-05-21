<?php

class WebBuilder_GrapesJSEditor extends Action {
    /** @var GrapesPage */
    private $grapesPage;
    private $templateId;
    private $pageId;

    function __construct(){
        parent::__construct();
        $this->pageId = $_GET['id'] ?? null;
        $this->templateId = $this->fetchTemplateId($this->pageId);

    }

    function launch() {

        $this->display('grapesjs.tpl', '', '', false);

    }

    function getBreadCrumbs(): array {
        $breadcrumbs = [];
        $breadcrumbs[] = new Breadcrumb('/', 'Home');
        if ($this->grapesPage != null) {
            $breadcrumbs[] = new Breadcrumb('', $this->grapesPage->title, true);
            // $breadcrumbs[] = new Breadcrumb('/WebBuilder/GrapesPages?id=')
        }
        return $breadcrumbs;
    }

    // function getTemplateId(): ?string {
    //     return $this->templateId;
    // }

    function fetchTemplateId($pageId): ?string {

    }
}