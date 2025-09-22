<?php
// Пример данных о транспортных средствах
// В реальном приложении эти данные будут извлекаться из базы данных
$vehicles = [
    'KMHDH4AE3EU123833' => [
        'vin' => 'KMHDH4AE3EU123833',
        'type' => 'Sedan',
        'color' => 'Blue',
        'price' => 230,
        'pickup_date' => '17.9.2025',
        'from' => 'Maryland (MD)',
        'to' => 'New Jersey (NJ)',
        'distance' => '~250 miles',
        'estimated_time' => '4-5 hours',
        'payment_status' => 'paid',
        'payment_amount' => 230,
        'payment_date' => '15.9.2025',
        'payment_method' => 'Credit Card',
        'contact_phone' => '+1 (123) 931-3354',
        'contact_email' => 'customer@example.com',
        'contact_person' => 'John Doe',
        'notes' => 'Vehicle has minor scratches on the rear bumper. Customer has been notified and agreed to the condition.'
    ],
    '1G1YW2D72K5118926' => [
        'vin' => '1G1YW2D72K5118926',
        'type' => 'SUV',
        'color' => 'Black',
        'price' => 130,
        'pickup_date' => '17.9.2025',
        'from' => 'California (CA)',
        'to' => 'California (CA)',
        'distance' => '~120 miles',
        'estimated_time' => '2-3 hours',
        'payment_status' => 'paid',
        'payment_amount' => 130,
        'payment_date' => '14.9.2025',
        'payment_method' => 'PayPal',
        'contact_phone' => '+1 (456) 782-9012',
        'contact_email' => 'client@example.com',
        'contact_person' => 'Jane Smith',
        'notes' => 'No issues reported.'
    ],
    // Добавьте другие транспортные средства по аналогии
];
// Функция для получения информации о транспортном средстве по VIN
function getVehicleByVin($vin) {
    global $vehicles;
    return isset($vehicles[$vin]) ? $vehicles[$vin] : null;
}
// Функция для получения всех транспортных средств
function getAllVehicles() {
    global $vehicles;
    return $vehicles;
}
?>