<?php
    header("Content-Type: application/json");
    include('db.php');

    $newGrapesPageContent = json_decode(file_get_contents("php://input"), true);
    $html = $newGrapesPageContent['html'];
    $projectData = $newGrapesPageContent['projectData'];
    $id = $projectData['pages'][0]['id']; // Corrected the ID retrieval
    // Check if the ID exists in the database
    $stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM `created_grapes_page` WHERE grapes_page_id=?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($count > 0) {
        // Update existing record
        $stmt = mysqli_prepare($con, "UPDATE `created_grapes_page` SET htmlData=? WHERE grapes_page_id=?");
        mysqli_stmt_bind_param($stmt, "ss", $html, $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // Insert new record
        $stmt = mysqli_prepare($con, "INSERT INTO `created_grapes_page` (grapes_page_id, htmlData) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $id, $html);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($result !== false) {
        $response['success'] = true;
        $response['message'] = ($count > 0) ? 'Data updated' : 'Data inserted';
    } else {
        $response['success'] = false;
        $response['message'] = 'Error: ' . mysqli_error($con);
    }

    mysqli_close($con);
    echo json_encode($response);
?>


