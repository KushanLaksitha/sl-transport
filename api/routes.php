<?php
// api/routes.php
function getRoutes() {
    $conn = getDBConnection();
    $sql = "SELECT id, route_number, route_name FROM routes WHERE active = 1";
    $result = $conn->query($sql);
    $routes = [];
    
    while($row = $result->fetch_assoc()) {
        $routes[] = [
            'id' => $row['id'],
            'number' => $row['route_number'],
            'name' => $row['route_name']
        ];
    }
    
    $conn->close();
    return $routes;
}
// Helper functions
function getTimeDifference($timestamp) {
    $now = new DateTime();
    $update = new DateTime($timestamp);
    $diff = $now->diff($update);
    
    if ($diff->i == 0) {
        return "Just now";
    } elseif ($diff->i < 60) {
        return $diff->i . " mins ago";
    } elseif ($diff->h < 24) {
        return $diff->h . " hours ago";
    } else {
        return $diff->d . " days ago";
    }
}

function getVehicleStatus($scheduleTime, $actualTime) {
    $schedule = new DateTime($scheduleTime);
    $actual = new DateTime($actualTime);
    $diff = $actual->diff($schedule);
    
    if ($diff->i <= 5) {
        return 'on-time';
    } elseif ($diff->i <= 10) {
        return 'arriving';
    } else {
        return 'delayed';
    }
}

?>