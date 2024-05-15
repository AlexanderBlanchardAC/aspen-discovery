<?php
$con = mysqli_connect("aspen-db", "aspensuper", "aspensuper", "aspen");
if (mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}