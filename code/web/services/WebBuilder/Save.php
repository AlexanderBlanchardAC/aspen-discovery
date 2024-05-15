<?php
//function saveGrapesPageAsPage() {
    header("Content-Type: application/json");
    include('db.php');
    // global $aspen_db;

        $newGrapesPageContent = json_decode(file_get_contents("php://input"), true);
        $html = $newGrapesPageContent['html'];

        $result = mysqli_query(
            $con,
            "INSERT INTO `created_grapes_page` (htmlData) VALUES ('$html')"
        );

        if($result){
            $response['success'] = true;
            $response[',essage'] = 'Data inserted';
        } else {
            $response['success'] = false;
            $response['message'] = 'Error inserting data: ' . mysqli_error($con);
        }
        mysqli_close($con);
        echo json_encode($response);

      //TODO:: Check ID handle update else insert
      //TODO:: Handle opening editor with page data already contained
      //TODO:: if saved as template, handle saving to both page table (to open
      //in editor and the tempaltes table to be slectable from the dropdown)
        //TODO:: Retrieve info from grapes_web_builder table when saving to the grapes page
        //table, e.g. title, urlalias etc
       
//}
