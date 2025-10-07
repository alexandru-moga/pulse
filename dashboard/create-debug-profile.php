<?php
// Save the original profile-edit.php and create a debug version
$originalFile = file_get_contents(__DIR__ . '/profile-edit.php');

// Create a backup
file_put_contents(__DIR__ . '/profile-edit-backup.php', $originalFile);

echo "Backup created successfully as profile-edit-backup.php\n";

// Create the debug version
$debugVersion = str_replace(
    '$success = "Profile updated successfully!";',
    '$success = "Profile updated successfully! DEBUG: Update worked at " . date("H:i:s");',
    $originalFile
);

// Add debug logging right after the update
$debugVersion = str_replace(
    '$stmt->execute([$newFirst, $newLast, $newDesc, $newSchool, $newPhone, $currentUser->id]);',
    '$stmt->execute([$newFirst, $newLast, $newDesc, $newSchool, $newPhone, $currentUser->id]);
            error_log("DEBUG: Profile update executed for user " . $currentUser->id . " at " . date("Y-m-d H:i:s"));',
    $debugVersion
);

file_put_contents(__DIR__ . '/profile-edit.php', $debugVersion);

echo "Debug version created successfully\n";
?>