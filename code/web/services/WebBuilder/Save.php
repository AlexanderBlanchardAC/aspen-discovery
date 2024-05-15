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

        // function response($html){
        //     $response['html'] = $html;
        //     $json_response = json_encode($response);
        //     echo $json_response;
        // }
        

       
//}
