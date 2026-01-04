<?php
// Application-level configuration used in the UI
$app = [
    'company_name'   => "FAST ‘N’ SAFE MOVERS",
    'company_address'=> "Plot no 163/A Nirmal chambers nr. Saiyed paper mill G.I.D.C. VAPI-396195",
    'company_phones' => ["+917041080840", "+919376758100"],
    'company_pan'    => "BVFPS6811J",
    'bank' => [
        'name'         => "Axis Bank Limited",
        'account_name' => "Fast N Safe Movers",
        'account_no'   => "922020012147268",
        'ifsc'         => "UTIB0000111",
    ],
];
// Minimal PDO connection helper (XAMPP defaults)
$dsn = 'mysql:host=localhost;dbname=billing_app;charset=utf8mb4';
$username = 'root';
$password = '';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, $username, $password, $options);