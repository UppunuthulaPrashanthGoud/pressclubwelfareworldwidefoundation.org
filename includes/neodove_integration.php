<?php
/**
 * Neodove API Integration
 */
function sendToNeodove($name, $mobile, $email, $detail1, $detail2) {
    $url = 'https://b5aba341-38ea-4f6c-8b4b-93656099cd2d.neodove.com/integration/custom/71ccb719-b252-402f-8c90-865eb3453a8f/leads';
    
    // Clean mobile number to contain only digits
    $mobile = preg_replace('/[^0-9]/', '', $mobile);
    if (empty($mobile)) {
        return false;
    }
    
    $data = [
        'name' => $name,
        'mobile' => (int) $mobile,
        'email' => $email ? $email : 'noemail@example.com', // API might require email
        'detail' => $detail1,
        'detail1' => $detail1, // Sending both detail and detail1 to match both JSON and cURL examples
        'detail2' => $detail2
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // 3 second timeout so the user isn't stuck if Neodove API is slow
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}
