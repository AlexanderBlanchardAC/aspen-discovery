<?php

class Templates {


    
	function getTemplates() {
        addTemplatesToDatabase();
        
		global $aspen_db;
		$stmt = $aspen_db->prepare("SELECT templateName, templateContent FROM templates");
		$stmt->execute();
		$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $templates;
	}

}