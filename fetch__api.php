<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    die(json_encode(['error' => 'Not authenticated']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_url = isset($_POST['api_url']) ? trim($_POST['api_url']) : '';
    
    if (empty($api_url)) {
        echo json_encode(['error' => 'API URL is required']);
        exit();
    }
    
    // Validate URL
    if (!filter_var($api_url, FILTER_VALIDATE_URL)) {
        echo json_encode(['error' => 'Invalid URL format']);
        exit();
    }
    
    // Fetch data from API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        echo json_encode(['error' => "API returned HTTP code: $http_code"]);
        exit();
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Invalid JSON response from API']);
        exit();
    }
    
    if (empty($data)) {
        echo json_encode(['error' => 'No data returned from API']);
        exit();
    }
    
    // Store API endpoint in history
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO api_endpoints (user_id, api_url, endpoint_name) VALUES (?, ?, ?)");
    $endpoint_name = "NYC Collisions - " . date('Y-m-d H:i:s');
    $stmt->bind_param("iss", $user_id, $api_url, $endpoint_name);
    $stmt->execute();
    $stmt->close();
    
    // Clear existing data (optional - remove if you want to keep old data)
    // $conn->query("DELETE FROM nyc_collisions");
    
    // Insert fetched data into database
    $insert_count = 0;
    $error_count = 0;
    
    $stmt = $conn->prepare("INSERT INTO nyc_collisions (
        collision_id, crash_date, crash_time, borough, zip_code,
        latitude, longitude, location, on_street_name, cross_street_name,
        off_street_name, number_of_persons_injured, number_of_persons_killed,
        number_of_pedestrians_injured, number_of_pedestrians_killed,
        number_of_cyclist_injured, number_of_cyclist_killed,
        number_of_motorist_injured, number_of_motorist_killed,
        contributing_factor_vehicle_1, contributing_factor_vehicle_2,
        contributing_factor_vehicle_3, contributing_factor_vehicle_4,
        contributing_factor_vehicle_5, vehicle_type_code_1, vehicle_type_code_2,
        vehicle_type_code_3, vehicle_type_code_4, vehicle_type_code_5
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($data as $item) {
        $collision_id = $item['collision_id'] ?? null;
        $crash_date = !empty($item['crash_date']) ? date('Y-m-d', strtotime($item['crash_date'])) : null;
        $crash_time = !empty($item['crash_time']) ? $item['crash_time'] : null;
        $borough = $item['borough'] ?? null;
        $zip_code = $item['zip_code'] ?? null;
        $latitude = !empty($item['latitude']) ? floatval($item['latitude']) : null;
        $longitude = !empty($item['longitude']) ? floatval($item['longitude']) : null;
        $location = $item['location'] ?? null;
        $on_street_name = $item['on_street_name'] ?? null;
        $cross_street_name = $item['cross_street_name'] ?? null;
        $off_street_name = $item['off_street_name'] ?? null;
        $persons_injured = intval($item['number_of_persons_injured'] ?? 0);
        $persons_killed = intval($item['number_of_persons_killed'] ?? 0);
        $pedestrians_injured = intval($item['number_of_pedestrians_injured'] ?? 0);
        $pedestrians_killed = intval($item['number_of_pedestrians_killed'] ?? 0);
        $cyclist_injured = intval($item['number_of_cyclist_injured'] ?? 0);
        $cyclist_killed = intval($item['number_of_cyclist_killed'] ?? 0);
        $motorist_injured = intval($item['number_of_motorist_injured'] ?? 0);
        $motorist_killed = intval($item['number_of_motorist_killed'] ?? 0);
        $factor1 = $item['contributing_factor_vehicle_1'] ?? null;
        $factor2 = $item['contributing_factor_vehicle_2'] ?? null;
        $factor3 = $item['contributing_factor_vehicle_3'] ?? null;
        $factor4 = $item['contributing_factor_vehicle_4'] ?? null;
        $factor5 = $item['contributing_factor_vehicle_5'] ?? null;
        $vehicle1 = $item['vehicle_type_code_1'] ?? null;
        $vehicle2 = $item['vehicle_type_code_2'] ?? null;
        $vehicle3 = $item['vehicle_type_code_3'] ?? null;
        $vehicle4 = $item['vehicle_type_code_4'] ?? null;
        $vehicle5 = $item['vehicle_type_code_5'] ?? null;
        
        $stmt->bind_param(
            "ssssssddssssiiiiiiiissssssssss",
            $collision_id, $crash_date, $crash_time, $borough, $zip_code,
            $latitude, $longitude, $location, $on_street_name, $cross_street_name,
            $off_street_name, $persons_injured, $persons_killed,
            $pedestrians_injured, $pedestrians_killed,
            $cyclist_injured, $cyclist_killed,
            $motorist_injured, $motorist_killed,
            $factor1, $factor2, $factor3, $factor4, $factor5,
            $vehicle1, $vehicle2, $vehicle3, $vehicle4, $vehicle5
        );
        
        if ($stmt->execute()) {
            $insert_count++;
        } else {
            $error_count++;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully imported $insert_count records" . ($error_count > 0 ? " ($error_count errors)" : ""),
        'total_records' => count($data),
        'imported' => $insert_count,
        'errors' => $error_count
    ]);
    
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>