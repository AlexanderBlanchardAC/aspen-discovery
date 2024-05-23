<?php
header("Content-Type: application/json");
include('db.php');

$templateId = $_GET['id']; // Assuming the template ID is passed as a query parameter
$response = [];

if ($templateId) {
    // Prepare the SQL statement to fetch the template data
    $stmt = mysqli_prepare($con, "SELECT htmlData, cssData, templateContent FROM `templates` WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $templateId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $htmlData, $cssData, $templateContent);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($htmlData !== null && $cssData !== null && $templateContent !== null) {
            $response['success'] = true;
            $response['html'] = $htmlData;
            $response['css'] = $cssData;
            $response['projectData'] = json_decode($templateContent, true);
        } else {
            $response['success'] = false;
            $response['message'] = 'Template not found';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to prepare SQL statement: ' . mysqli_error($con);
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid template ID';
}

mysqli_close($con);
echo json_encode($response);
?>
