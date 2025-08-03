<?php
require_once 'config/admin_auth_check.php';
require_once 'includes/linkedin_helper.php';

echo "<h1>Improved LinkedIn Profile Picture System Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-case { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .result { margin: 10px 0; padding: 10px; border-radius: 4px; }
    .success { background: #d4edda; color: #155724; }
    .info { background: #cce7ff; color: #004085; }
    .placeholder { background: #f8f9fa; color: #6c757d; }
    img { max-width: 100px; height: auto; margin: 10px 0; border: 1px solid #ddd; }
</style>";

// Test cases
$test_cases = [
    [
        'name' => 'User with LinkedIn Profile',
        'data' => [
            'first_name' => 'Rose',
            'last_name' => 'Webb', 
            'linkedin_profile' => 'https://www.linkedin.com/in/rose-webb-798014215/',
            'avatar' => null
        ]
    ],
    [
        'name' => 'User with Upload Avatar',
        'data' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'linkedin_profile' => 'https://www.linkedin.com/in/johndoe/',
            'avatar' => 'uploads/avatars/sample.jpg'
        ]
    ],
    [
        'name' => 'User with No LinkedIn or Avatar',
        'data' => [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'linkedin_profile' => '',
            'avatar' => null
        ]
    ],
    [
        'name' => 'User with Problematic LinkedIn Username',
        'data' => [
            'first_name' => 'Test',
            'last_name' => 'User',
            'linkedin_profile' => 'https://www.linkedin.com/in/rose-webb-798014215/',
            'avatar' => null
        ]
    ]
];

foreach ($test_cases as $test) {
    echo "<div class='test-case'>";
    echo "<h3>" . htmlspecialchars($test['name']) . "</h3>";
    
    $profile_pic = getUserProfilePicture($test['data']);
    echo "<div class='result info'>Profile Picture URL: " . htmlspecialchars($profile_pic) . "</div>";
    
    if ($profile_pic) {
        echo "<img src='" . htmlspecialchars($profile_pic) . "' alt='Profile Picture'>";
    }
    
    $message = getProfilePictureMessage($test['data']);
    echo $message;
    
    // Test individual functions
    $name = trim($test['data']['first_name'] . ' ' . $test['data']['last_name']);
    $placeholder = generateProfessionalPlaceholder($name);
    echo "<div class='result placeholder'>Professional Placeholder: " . htmlspecialchars($placeholder) . "</div>";
    echo "<img src='" . htmlspecialchars($placeholder) . "' alt='Placeholder'>";
    
    echo "</div>";
}

echo "<div class='test-case'>";
echo "<h3>Direct Placeholder Tests</h3>";
echo "<p>These should show proper initials instead of weird combinations:</p>";

$name_tests = ['Rose Webb', 'John Doe', 'Jane Smith', 'A B', 'SingleName'];
foreach ($name_tests as $name) {
    $placeholder = generateProfessionalPlaceholder($name);
    echo "<div style='display: inline-block; margin: 10px; text-align: center;'>";
    echo "<div>" . htmlspecialchars($name) . "</div>";
    echo "<img src='" . htmlspecialchars($placeholder) . "' alt='" . htmlspecialchars($name) . "'>";
    echo "</div>";
}
echo "</div>";
?>
