<?php
// require_once ROOT_DIR . '/Action.php';


class CreatedGrapesPages {
    function launch() {
		header('Content-type: application/json');
		$method = $_REQUEST['method'];
		$allowed_methods = [
		    'saveAsGrapesPage',
		];
		if (in_array($method, $allowed_methods)) {
			$response = $this->$method();
		} else {
			$response = ['result' => false];
		}
		echo json_encode($response);
	}

    function saveAsGrapesPage() {
        require_once ROOT_DIR . '/sys/WebBuilder/CreatedGrapesPage.php';
        $newGrapesPageContent = json_decode(file_get_contents("php://input"), true);
        $html = $newGrapesPageContent['html'];
        $id = $newGrapesPageContent['pages'][0]['id'];

        $createdGrapesPage = new CreatedGrapesPage();
        if ($createdGrapesPage->find($id)) {
            $createdGrapesPage->html = $html;
            $createdGrapesPage->update();
        } else {
            $createdGrapesPage->html = $html;
            $createdGrapesPage->id = $id;
            $createdGrapesPage->insert();
        }
        $this->response($id, $html);
    }

    function response($id, $html) {
        $response['id'] = $id;
        $response['html'] = $html;
        $json_response = json_encode($response);
        echo $json_response;
    }

    function getBreadcrumbs(): array {
		return [];
	}
}