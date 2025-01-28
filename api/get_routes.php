<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $date = $_GET['date'] ?? '';
    $transportType = $_GET['transportType'] ?? '';

    $stmt = $pdo->prepare("
        SELECT * FROM routes 
        WHERE departure_city = :from 
        AND arrival_city = :to 
        AND transport_type = :transportType 
        AND DATE(departure_time) = :date
        AND available_seats > 0
    ");

    $stmt->execute([
        ':from' => $from,
        ':to' => $to,
        ':transportType' => $transportType,
        ':date' => $date
    ]);

    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'routes' => $routes]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
