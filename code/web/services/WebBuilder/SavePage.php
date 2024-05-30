<?php
header("Content-Type: application/json");
include('db.php');

$newGrapesPageContent = json_decode(file_get_contents("php://input"), true);
$templateId = $newGrapesPageContent['templateId'];
$grapesPageId = $newGrapesPageContent['grapesPageId'];
$html = $newGrapesPageContent['html'];
$css = $newGrapesPageContent['css'];
$grapesGenId = $newGrapesPageContent['grapesGenId'];
$projectData = json_encode($newGrapesPageContent['projectData']);

$response = [];

if ($grapesPageId) {
    // Check if the record exists
    $stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM `grapes_web_builder` WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $grapesPageId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            // Update existing record
            $stmt = mysqli_prepare($con, "UPDATE `grapes_web_builder` SET templateContent=?, htmlData=?, cssData=?, grapesGenId=? WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssi", $projectData, $html, $css, $grapesGenId, $grapesPageId);
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
            $stmt = mysqli_prepare($con, "INSERT INTO `grapes_web_builder` (id, templateContent, htmlData, cssData, grapesPageId) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "issss", $grapesPageId, $projectData, $html, $css, $grapesGenId);
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
    $response['message'] = 'Invalid page ID';
}

mysqli_close($con);
echo json_encode($response);
?>
