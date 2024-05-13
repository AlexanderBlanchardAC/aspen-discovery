<?php
// require_once ROOT_DIR . '/sys/DBMaintenance/grapes_web_builder_updates.php';

class Templates {

	function getTemplates() {
        // addTemplatesToDatabase();

		global $aspen_db;
		$stmt = $aspen_db->prepare("SELECT templateId, templateName, templateContent FROM templates");
		$stmt->execute();
		$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $templates;
	}

    // function getTemplateContents($templateName) {
    //     global $aspen_db;

    //     $query = "SELECT  templateContent FROM templates WHERE templateName = ?";
    //     $statement = $aspen_db->prepare($query);
    //     $statement->execute([$templateName]);

    //     $result = $statement->fetch(PDO::FETCH_ASSOC);

    //     return $result['tempalteContent'] ?? '';
    // }

}
