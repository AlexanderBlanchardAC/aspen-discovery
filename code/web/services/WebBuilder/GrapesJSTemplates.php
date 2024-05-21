<?php

class WebBuilder_GrapesJSTemplates extends Action {
    /** @var CreateTemplate */
    private $createTemplate;

    function launch() {

        $this->display('createTemplatejs.tpl', '', '', false);

    }

    function getBreadCrumbs(): array {
        $breadcrumbs = [];
        $breadcrumbs[] = new Breadcrumb('/', 'Home');
        if ($this->createTemplate != null) {
            $breadcrumbs[] = new Breadcrumb('', $this->createTemplate->title, true);
            // $breadcrumbs[] = new Breadcrumb('/WebBuilder/GrapesPages?id=')
        }
        return $breadcrumbs;
    }
}