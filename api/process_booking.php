<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $pdo->beginTransaction();

    // Check seat availability
    $stmt = $pdo->prepare("
        SELECT available_seats 
        FROM routes 
        WHERE route_id = :routeId 
        FOR UPDATE
    ");
    $stmt->execute([':routeId' => $data['routeId']]);
    $route = $stmt->fetch();

    if (!$route || $route['available_seats'] < $data['passengers']) {
        throw new Exception('Insufficient seats available');
    }

    // Create booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (
            user_id, route_id, passengers, total_amount, 
            payment_method, booking_status, created_at
        ) VALUES (
            :userId, :routeId, :passengers, :totalAmount, 
            :paymentMethod, 'pending', NOW()
        )
    ");

    $stmt->execute([
        ':userId' => $data['userId'],
        ':routeId' => $data['routeId'],
        ':passengers' => $data['passengers'],
        ':totalAmount' => $data['totalAmount'],
        ':paymentMethod' => $data['paymentMethod']
    ]);

    $bookingId = $pdo->lastInsertId();

    // Update available seats
    $stmt = $pdo->prepare("
        UPDATE routes 
        SET available_seats = available_seats - :passengers 
        WHERE route_id = :routeId
    ");

    $stmt->execute([
        ':passengers' => $data['passengers'],
        ':routeId' => $data['routeId']
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'bookingId' => $bookingId]);
} catch(Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>