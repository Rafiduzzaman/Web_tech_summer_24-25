<?php
// Test file to verify all classes are loading correctly

// Include all model files
require_once 'model/User.php';
require_once 'model/Task.php';
require_once 'model/Notification.php';
require_once 'model/ActivityLog.php';
require_once 'model/Profile.php';

echo "Testing class loading...\n";

// Test User class
try {
    $user = new User();
    echo "✓ User class loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Error loading User class: " . $e->getMessage() . "\n";
}

// Test Task class
try {
    $task = new Task(1); // Pass a user ID
    echo "✓ Task class loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Error loading Task class: " . $e->getMessage() . "\n";
}

// Test Notification class
try {
    $notification = new Notification();
    echo "✓ Notification class loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Error loading Notification class: " . $e->getMessage() . "\n";
}

// Test ActivityLog class
try {
    $activityLog = new ActivityLog();
    echo "✓ ActivityLog class loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Error loading ActivityLog class: " . $e->getMessage() . "\n";
}

// Test Profile class
try {
    $profile = new Profile(1); // Pass a user ID
    echo "✓ Profile class loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Error loading Profile class: " . $e->getMessage() . "\n";
}

echo "\nAll tests completed!\n";
?> 