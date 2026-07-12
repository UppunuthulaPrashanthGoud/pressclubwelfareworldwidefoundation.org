<?php
require_once 'config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $users = [
        [
            'name' => 'Rahul Sharma',
            'email' => 'rahul.sharma@example.com',
            'mobile' => '9876543210',
            'gender' => 'Male',
            'dob' => '1992-05-15',
            'sdw_type' => 'S/O',
            'sdw_name' => 'Ramesh Sharma',
            'profession' => 'Teacher',
            'blood_group' => 'A+',
            'aadhar' => '123456789013',
            'state' => 'Uttar Pradesh',
            'district' => 'Lucknow',
            'address' => '123, MG Road, Lucknow',
            'pincode' => '226001',
            'membership_type' => 'active_membership',
            'registration_id' => 'SDJVP00001',
            'status' => 'approved',
            'user_type' => 'user'
        ],
        [
            'name' => 'Priya Singh',
            'email' => 'priya.singh@example.com',
            'mobile' => '9876543211',
            'gender' => 'Female',
            'dob' => '1995-08-22',
            'sdw_type' => 'D/O',
            'sdw_name' => 'Vijay Singh',
            'profession' => 'Engineer',
            'blood_group' => 'B+',
            'aadhar' => '123456789014',
            'state' => 'Maharashtra',
            'district' => 'Pune',
            'address' => '456, FC Road, Pune',
            'pincode' => '411004',
            'membership_type' => 'free_membership',
            'registration_id' => 'SDJVP00002',
            'status' => 'approved',
            'user_type' => 'user'
        ],
        [
            'name' => 'Amit Kumar',
            'email' => 'amit.kumar@example.com',
            'mobile' => '9876543212',
            'gender' => 'Male',
            'dob' => '1988-03-10',
            'sdw_type' => 'S/O',
            'sdw_name' => 'Suresh Kumar',
            'profession' => 'Doctor',
            'blood_group' => 'O+',
            'aadhar' => '123456789015',
            'state' => 'Gujarat',
            'district' => 'Ahmedabad',
            'address' => '789, SG Highway, Ahmedabad',
            'pincode' => '380015',
            'membership_type' => 'management_membership',
            'registration_id' => 'SDJVP00003',
            'status' => 'approved',
            'user_type' => 'coordinator'
        ],
        [
            'name' => 'Anita Verma',
            'email' => 'anita.verma@example.com',
            'mobile' => '9876543213',
            'gender' => 'Female',
            'dob' => '1990-11-25',
            'sdw_type' => 'W/O',
            'sdw_name' => 'Rakesh Verma',
            'profession' => 'Businesswoman',
            'blood_group' => 'AB+',
            'aadhar' => '123456789016',
            'state' => 'Rajasthan',
            'district' => 'Jaipur',
            'address' => '321, Tonk Road, Jaipur',
            'pincode' => '302015',
            'membership_type' => 'senior_membership',
            'registration_id' => 'SDJVP00004',
            'status' => 'approved',
            'user_type' => 'user'
        ]
    ];

    $password = password_hash('password', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, mobile, gender, dob, sdw_type, sdw_name, profession, blood_group, aadhar, state, district, address, pincode, membership_type, registration_id, status, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($users as $user) {
        $stmt->execute([
            $user['name'],
            $user['email'],
            $password,
            $user['mobile'],
            $user['gender'],
            $user['dob'],
            $user['sdw_type'],
            $user['sdw_name'],
            $user['profession'],
            $user['blood_group'],
            $user['aadhar'],
            $user['state'],
            $user['district'],
            $user['address'],
            $user['pincode'],
            $user['membership_type'],
            $user['registration_id'],
            $user['status'],
            $user['user_type']
        ]);
    }
    
    echo "Users inserted successfully!";
    
} catch (PDOException $e) {
    echo "Error inserting users: " . $e->getMessage();
}
?>