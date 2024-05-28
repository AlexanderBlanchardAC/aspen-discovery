<?php
header("Content-Type: application/json");
include('db.php');

$grapePageId = $_GET['id']; 
$response = [];

if ($grapePageId) {
    // Fetch the templateContent value from grapes_web_builder table
    $stmt = mysqli_prepare($con, "SELECT templateContent, templatesSelect FROM `grapes_web_builder` WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $grapePageId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $templateContent, $templateSelect);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($templateContent === null && $templateSelect !== null) {
            // If templateContent is empty and templateSelect is not, fetch the content from the templates table based on templateSelect
            $stmt = mysqli_prepare($con, "SELECT htmlData, cssData, templateContent FROM `templates` WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $templateSelect);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $htmlData, $cssData, $templateContent);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                if ($htmlData !== null && $cssData !== null && $templateContent !== null) {
                    // Template found, return data
                    $response['success'] = true;
                    $response['html'] = $htmlData;
                    $response['css'] = $cssData;
                    $response['projectData'] = json_decode($templateContent, true);
                } else {
                    // Template not found
                    $response['success'] = false;
                    $response['message'] = 'Template not found';
                }
            } else {
                // Error preparing SQL statement
                $response['success'] = false;
                $response['message'] = 'Failed to prepare SQL statement: ' . mysqli_error($con);
            }
        } elseif ($templateContent !== null) {
            // If templateContent is not empty, return the content directly from grapes_web_builder table
            $response['success'] = true;
            $response['projectData'] = json_decode($templateContent, true);
        } else {
            // Both template content and template select are empty
            $response['success'] = false;
            $response['message'] = 'Template content and select are both empty';
        }
    } else {
        // Error preparing SQL statement
        $response['success'] = false;
        $response['message'] = 'Failed to prepare SQL statement: ' . mysqli_error($con);
    }
} else {
    // Invalid page ID
    $response['success'] = false;
    $response['message'] = 'Invalid page ID';
}

mysqli_close($con);
echo json_encode($response);
?>
