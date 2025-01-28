<?php
// api/update-location.php
function updateVehicleLocation($vehicleId, $lat, $lng, $speed) {
    $conn = getDBConnection();
    $sql = "UPDATE vehicles 
            SET latitude = ?, 
                longitude = ?, 
                current_speed = ?,
                last_update = NOW() 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddi", $lat, $lng, $speed, $vehicleId);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}



?>