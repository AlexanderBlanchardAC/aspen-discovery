<?php

class WebBuilder_GrapesJSEditor extends Action {
    /** @var GrapesPage */
    private $grapesPage;

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
}