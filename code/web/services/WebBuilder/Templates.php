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

    function getTemplateById($id) {
        $template = new Template();
        $template->find();
       while ($template->fetch()){
        if ($template->id == $id) {
            return clone $template;
        }
       }
    }

    function getTemplateByName($templateName) {
        $template = new Template();
        $template->find();
        while ($template->fetch()){
            if ($template->temaplteName == $templateName) {
                return clone $template;
            }
        }
    }

    function saveAsTemplate(){
        $newGrapesTemplate = json_decode(file_get_contents("php://input"), true);
        $html = $newGrapesTemplate['html'];
        $template = new Template();
        $template->html = $html;
        $template->insert();

    }
}
