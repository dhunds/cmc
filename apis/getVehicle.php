<?php
include('connection.php');

$keyword = $_POST['q'];
$sql = "SELECT * FROM vehicle WHERE vehicleModel LIKE '%$keyword%'";

$stmt = $con->query($sql);
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response['status'] = 'success';
$response['data'] = $vehicles;

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($response);
exit;