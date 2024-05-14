<?php
header("Content-Type:application/json");
$data = json_decode(file_get_contents("php://input"));
$projectData = $data;
$assets = $data[$assets];
$css = $data[$css];
$html = $data[$html];
$aspen_db;
