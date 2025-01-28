<?php
// api/vehicles.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function getVehicles() {
    $conn = getDBConnection();
    $sql = "SELECT v.*, 
            r.route_name, 
            r.route_number,
            s.stop_name as next_stop,
            (SELECT COUNT(*) FROM vehicle_capacity vc WHERE vc.vehicle_id = v.id) as current_passengers
            FROM vehicles v
            LEFT JOIN routes r ON v.route_id = r.id
            LEFT JOIN stops s ON v.next_stop_id = s.id
            WHERE v.active = 1";
    
    $result = $conn->query($sql);
    $vehicles = [];
    
    while($row = $result->fetch_assoc()) {
        $capacity_percentage = ($row['current_passengers'] / $row['max_capacity']) * 100;
        $vehicles[] = [
            'id' => $row['id'],
            'type' => $row['vehicle_type'],
            'number' => $row['vehicle_number'],
            'route' => $row['route_number'],
            'routeName' => $row['route_name'],
            'lat' => $row['latitude'],
            'lng' => $row['longitude'],
            'lastUpdate' => getTimeDifference($row['last_update']),
            'status' => getVehicleStatus($row['schedule_time'], $row['actual_time']),
            'speed' => $row['current_speed'] . ' km/h',
            'nextStop' => $row['next_stop'],
            'capacity' => $capacity_percentage . '%'
        ];
    }
    
    $conn->close();
    return $vehicles;
}







?>