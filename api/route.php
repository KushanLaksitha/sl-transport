<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../config.php');

$db = new Database();
$conn = $db->getConnection();

$action = $_GET['action'] ?? '';

switch($action) {
    case 'search':
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';
        $type = $_GET['type'] ?? 'all';
        $sort = $_GET['sort'] ?? 'duration';
        
        try {
            $query = "SELECT r.*, v.type_id, vt.name as vehicle_type 
                     FROM routes r 
                     JOIN vehicle_assignments va ON r.route_id = va.route_id
                     JOIN vehicles v ON va.vehicle_id = v.vehicle_id
                     JOIN vehicle_types vt ON v.type_id = vt.type_id
                     WHERE (:from = '' OR r.start_location LIKE :from_location)
                     AND (:to = '' OR r.end_location LIKE :to_location)
                     AND (:type = 'all' OR vt.name = :vehicle_type)";
            
            if ($sort === 'duration') {
                $query .= " ORDER BY r.estimated_duration_minutes ASC";
            } else if ($sort === 'price') {
                $query .= " ORDER BY r.base_fare ASC";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':from_location' => "%$from%",
                ':to_location' => "%$to%",
                ':vehicle_type' => $type
            ]);
            
            $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $routes]);
        } catch(PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
        
    case 'schedule':
        try {
            $query = "SELECT s.*, r.route_name, v.vehicle_number, vt.name as vehicle_type
                     FROM schedules s
                     JOIN routes r ON s.route_id = r.route_id
                     JOIN vehicle_assignments va ON r.route_id = va.route_id
                     JOIN vehicles v ON va.vehicle_id = v.vehicle_id
                     JOIN vehicle_types vt ON v.type_id = vt.type_id
                     WHERE s.day_of_week = :day
                     ORDER BY s.departure_time ASC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':day' => date('l')]);
            
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $schedules]);
        } catch(PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
        
    case 'stops':
        $routeId = $_GET['route_id'] ?? '';
        
        try {
            $query = "SELECT s.* 
                     FROM stops s
                     JOIN route_stops rs ON s.stop_id = rs.stop_id
                     WHERE rs.route_id = :route_id
                     ORDER BY rs.stop_order ASC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':route_id' => $routeId]);
            
            $stops = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $stops]);
        } catch(PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>