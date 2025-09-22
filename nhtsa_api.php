<?php
require_once 'config.php';

if (isset($_GET['vin'])) {
    $vin = $_GET['vin'];
    
    // Check VIN length
    if (strlen($vin) !== 17) {
        echo json_encode(['success' => false, 'message' => 'Invalid VIN length']);
        exit;
    }
    
    // Request data from API
    $url = "https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/{$vin}?format=json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch data from NHTSA']);
        exit;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['Results'][0])) {
        $result = $data['Results'][0];
        $vehicleInfo = [
            'make' => $result['Make'] ?? '',
            'model' => $result['Model'] ?? '',
            'year' => $result['ModelYear'] ?? '',
            // Removed color parsing
            'body_type' => $result['BodyClass'] ?? '',
            'vehicle_type' => $result['VehicleType'] ?? '',
            'country' => $result['PlantCountry'] ?? '',
            'engine' => $result['EngineDisplacement'] ?? '',
            'cylinders' => $result['Cylinders'] ?? '',
            'transmission' => $result['Transmission'] ?? '',
            'drive_type' => $result['DriveType'] ?? ''
        ];
        
        // Return only the basic required data (Make, Model, Year)
        echo json_encode(['success' => true, 'data' => [
            'make' => $vehicleInfo['make'],
            'model' => $vehicleInfo['model'],
            'year' => $vehicleInfo['year']
        ]]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Vehicle data not found for this VIN']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'VIN parameter is missing']);
    exit;
}
?>