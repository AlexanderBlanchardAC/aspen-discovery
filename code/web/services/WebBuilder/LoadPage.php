<?php
header("Content-Type: application/json");
include('db.php');

$grapePageId = $_GET['id'];
$response = [];

if ($grapePageId) {
    // Fetch the templateContent, templatesSelect, htmlData, and cssData values from grapes_web_builder table
    $stmt = mysqli_prepare($con, "SELECT templateContent, templatesSelect, htmlData, cssData FROM `grapes_web_builder` WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $grapePageId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $templateContent, $templateSelect, $htmlData, $cssData);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($templateContent === null && $templateSelect !== null) {
            // If templateContent is empty and templateSelect is not, fetch the content from the templates table based on templateSelect
            $stmt = mysqli_prepare($con, "SELECT htmlData, cssData, templateContent FROM `templates` WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $templateSelect);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $htmlDataFromTemplate, $cssDataFromTemplate, $templateContentFromTemplate);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                if ($htmlDataFromTemplate !== null && $cssDataFromTemplate !== null && $templateContentFromTemplate !== null) {
                    // Template found, return data
                    $response['success'] = true;
                    $response['html'] = $htmlDataFromTemplate;
                    $response['css'] = $cssDataFromTemplate;
                    $response['projectData'] = json_decode($templateContentFromTemplate, true);

                    // Update the grapes_web_builder table with the fetched templateContent
                    $updateStmt = mysqli_prepare($con, "UPDATE grapes_web_builder SET templateContent=?, htmlData=?, cssData=? WHERE id=?");
                    if ($updateStmt) {
                        mysqli_stmt_bind_param($updateStmt, "sssi", $templateContentFromTemplate, $htmlDataFromTemplate, $cssDataFromTemplate, $grapePageId);
                        mysqli_stmt_execute($updateStmt);
                        mysqli_stmt_close($updateStmt);
                    }
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
            $response['html'] = $htmlData;
            $response['css'] = $cssData;
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
