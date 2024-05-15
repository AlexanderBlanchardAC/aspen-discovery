<?php
header("Content-Type: application/json");
include('db.php');

$newGrapesPageContent = json_decode(file_get_contents("php://input"), true);
$templateId = $newGrapesPageContent['templateId'];
$html = $newGrapesPageContent['html'];
$projectData = $newGrapesPageContent['projectData'];
$templateName = $newGrapesPageContent['templateName'];

$response = [];

if ($templateId) {
    // Check if the record exists
    $stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM `templates` WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $templateId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            // Update existing record
            $stmt = mysqli_prepare($con, "UPDATE `templates` SET htmlData=? WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $html, $templateId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);
                
                if ($result !== false) {
                    $response['success'] = true;
                    $response['message'] = 'Data updated';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Failed to update record: ' . mysqli_error($con);
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Failed to prepare update SQL statement: ' . mysqli_error($con);
            }
        } else {
            // Insert new record
            $stmt = mysqli_prepare($con, "INSERT INTO `templates` (id, htmlData) VALUES (?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $templateId, $html);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);

                if ($result !== false) {
                    $response['success'] = true;
                    $response['message'] = 'Data inserted';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Failed to insert record: ' . mysqli_error($con);
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Failed to prepare insert SQL statement: ' . mysqli_error($con);
            }
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to prepare select SQL statement: ' . mysqli_error($con);
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid template ID';
}

mysqli_close($con);
echo json_encode($response);
?>
