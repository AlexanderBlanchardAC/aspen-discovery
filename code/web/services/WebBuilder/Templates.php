<?php
// require_once ROOT_DIR . '/sys/DBMaintenance/grapes_web_builder_updates.php';
require_once ROOT_DIR . '/sys/WebBuilder/Template.php';

class Templates {

	function getTemplates() {
        addTemplatesToDatabase();

        $template = new Template();
        $template->find();
        $templates = $template->fetchAll();

		return $templates;
	}

    function getTemplateContent() {
        $template = new Template();
        $template->find($template->id);
        $template = $template->fetch($template->id);
    }

}
