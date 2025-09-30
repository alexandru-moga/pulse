<?php<?php<?php

/**

 * Profile Edit Page/**/**

 * Allows users to update their personal information, profile image, and linked accounts

 */ * Profile Edit Page * Profile Edit Page



require_once __DIR__ . '/../core/init.php'; * Allows users to update their personal information, profile image, and linked accounts * Allows users to update their personal information, profile image, and linked accounts

require_once __DIR__ . '/../core/classes/DiscordOAuth.php';

require_once __DIR__ . '/../core/classes/GitHubOAuth.php'; */ */

require_once __DIR__ . '/../core/classes/GoogleOAuth.php';

require_once __DIR__ . '/../core/classes/SlackOAuth.php';



// Security checksrequire_once __DIR__ . '/../core/init.php';require_once __DIR__ . '/../core/init.php';

checkActiveOrLimitedAccess();

require_once __DIR__ . '/../core/classes/DiscordOAuth.php';require_once __DIR__ . '/../core/classes/DiscordOAuth.php';

global $db, $currentUser, $settings;

require_once __DIR__ . '/../core/classes/GitHubOAuth.php';require_once __DIR__ . '/../core/classes/GitHubOAuth.php';

if (!$currentUser) {

    header('Location: /dashboard/login.php');require_once __DIR__ . '/../core/classes/GoogleOAuth.php';require_once __DIR__ . '/../core/classes/GoogleOAuth.php';

    exit;

}require_once __DIR__ . '/../core/classes/SlackOAuth.php';require_once __DIR__ . '/../core/classes/SlackOAuth.php';



// Initialize OAuth handlers

$discord = new DiscordOAuth($db);

$github = new GitHubOAuth($db);// Security checks// Security checks

$google = new GoogleOAuth($db);

$slack = new SlackOAuth($db);checkActiveOrLimitedAccess();checkActiveOrLimitedAccess();



// Check OAuth configurations

$discordConfigured = $discord->isConfigured();

$githubConfigured = $github->isConfigured();global $db, $currentUser, $settings;global $db, $currentUser, $settings;

$googleConfigured = $google->isConfigured();

$slackConfigured = $slack->isConfigured();



// Get current OAuth linksif (!$currentUser) {if (!$currentUser) {

$discordLink = $discord->getUserDiscordLink($currentUser->id);

$githubLink = $github->getUserGitHubLink($currentUser->id);    header('Location: /dashboard/login.php');    header('Location: /dashboard/login.php');

$googleLink = $google->getUserGoogleLink($currentUser->id);

$slackLink = $slack->getUserSlackLink($currentUser->id);    exit;    exit;



// Handle session messages}}

$success = $_SESSION['account_link_success'] ?? null;

$error = $_SESSION['account_error'] ?? null;

unset($_SESSION['account_link_success'], $_SESSION['account_error']);

// Initialize OAuth handlers// Initialize OAuth handlers

/**

 * Process form submissions$oauthHandlers = [$oauthHandlers = [

 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    'discord' => new DiscordOAuth($db),    'discord' => new DiscordOAuth($db),

    // Handle OAuth unlinking

    if (isset($_POST['unlink_discord'])) {    'github' => new GitHubOAuth($db),    'github' => new GitHubOAuth($db),

        $discord->unlinkDiscordAccount($currentUser->id);

        $success = "Discord account unlinked successfully!";    'google' => new GoogleOAuth($db),    'google' => new GoogleOAuth($db),

        $discordLink = null;

    } elseif (isset($_POST['unlink_github'])) {    'slack' => new SlackOAuth($db)    'slack' => new SlackOAuth($db)

        $github->unlinkGitHubAccount($currentUser->id);

        $success = "GitHub account unlinked successfully!";];];

        $githubLink = null;

    } elseif (isset($_POST['unlink_google'])) {

        $google->unlinkGoogleAccount($currentUser->id);

        $success = "Google account unlinked successfully!";// Check OAuth configurations// Check OAuth configurations

        $googleLink = null;

    } elseif (isset($_POST['unlink_slack'])) {$oauthConfig = array_map(fn($handler) => $handler->isConfigured(), $oauthHandlers);$oauthConfig = array_map(fn($handler) => $handler->isConfigured(), $oauthHandlers);

        $slack->unlinkSlackAccount($currentUser->id);

        $success = "Slack account unlinked successfully!";

        $slackLink = null;

    } elseif (isset($_POST['save_profile'])) {// Get current OAuth links// Get current OAuth links

        // Handle profile updates

        $result = processProfileUpdate();$oauthLinks = [$oauthLinks = [

        if ($result['success']) {

            $success = $result['message'];    'discord' => $oauthHandlers['discord']->getUserDiscordLink($currentUser->id),    'discord' => $oauthHandlers['discord']->getUserDiscordLink($currentUser->id),

            // Refresh user data

            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");    'github' => $oauthHandlers['github']->getUserGitHubLink($currentUser->id),    'github' => $oauthHandlers['github']->getUserGitHubLink($currentUser->id),

            $stmt->execute([$currentUser->id]);

            $currentUser = $stmt->fetch(PDO::FETCH_OBJ);    'google' => $oauthHandlers['google']->getUserGoogleLink($currentUser->id),    'google' => $oauthHandlers['google']->getUserGoogleLink($currentUser->id),

        } else {

            $error = $result['message'];    'slack' => $oauthHandlers['slack']->getUserSlackLink($currentUser->id)    'slack' => $oauthHandlers['slack']->getUserSlackLink($currentUser->id)

        }

    }];];

}



/**

 * Process profile update// Handle session messages// Handle session messages

 */

function processProfileUpdate(): array {$success = $_SESSION['account_link_success'] ?? null;$success = $_SESSION['account_link_success'] ?? null;

    global $db, $currentUser;

    $error = $_SESSION['account_error'] ?? null;$error = $_SESSION['account_error'] ?? null;

    // Sanitize input data

    $profileData = [unset($_SESSION['account_link_success'], $_SESSION['account_error']);unset($_SESSION['account_link_success'], $_SESSION['account_error']);

        'first_name' => trim($_POST['first_name'] ?? ''),

        'last_name' => trim($_POST['last_name'] ?? ''),

        'description' => trim($_POST['description'] ?? ''),

        'bio' => trim($_POST['bio'] ?? ''),/**/**

        'school' => trim($_POST['school'] ?? ''),

        'phone' => trim($_POST['phone'] ?? ''), * Process form submissions * Process form submissions

        'profile_public' => isset($_POST['profile_public']) ? 1 : 0

    ]; */ */



    // Validate required fieldsif ($_SERVER['REQUEST_METHOD'] === 'POST') {if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = [];

    if (empty($profileData['first_name'])) $errors[] = "First name is required";    // Handle OAuth unlinking    // Handle OAuth unlinking

    if (empty($profileData['last_name'])) $errors[] = "Last name is required";

    foreach (['discord', 'github', 'google', 'slack'] as $service) {    foreach (['discord', 'github', 'google', 'slack'] as $service) {

    // Handle profile image upload

    $profileImageResult = handleProfileImageUpload();        if (isset($_POST["unlink_$service"])) {        if (isset($_POST["unlink_$service"])) {

    if (!$profileImageResult['success']) {

        $errors[] = $profileImageResult['message'];            $method = "unlink" . ucfirst($service) . "Account";            $method = "unlink" . ucfirst($service) . "Account";

    } else if ($profileImageResult['filename']) {

        $profileData['profile_image'] = $profileImageResult['filename'];            $oauthHandlers[$service]->$method($currentUser->id);            $oauthHandlers[$service]->$method($currentUser->id);

    }

            $success = ucfirst($service) . " account unlinked successfully!";            $success = ucfirst($service) . " account unlinked successfully!";

    if (!empty($errors)) {

        return ['success' => false, 'message' => implode('<br>', $errors)];            $oauthLinks[$service] = null;            $oauthLinks[$service] = null;

    }

            break;            break;

    // Update database

    try {        }        }

        $query = "UPDATE users SET first_name = ?, last_name = ?, description = ?, school = ?, phone = ?";

        $params = [$profileData['first_name'], $profileData['last_name'], $profileData['description'], $profileData['school'], $profileData['phone']];    }    }

        

        // Add optional fields if they exist in the database

        if (isset($profileData['profile_image'])) {

            $query .= ", profile_image = ?";    // Handle profile updates    // Handle profile updates

            $params[] = $profileData['profile_image'];

        }    if (isset($_POST['save_profile']) || !array_intersect_key($_POST, array_flip(['unlink_discord', 'unlink_github', 'unlink_google', 'unlink_slack']))) {    if (isset($_POST['save_profile']) || !array_intersect_key($_POST, array_flip(['unlink_discord', 'unlink_github', 'unlink_google', 'unlink_slack']))) {

        

        // Check if bio column exists        $result = processProfileUpdate();        $result = processProfileUpdate();

        try {

            $db->query("SELECT bio FROM users LIMIT 1");        if ($result['success']) {        if ($result['success']) {

            $query .= ", bio = ?";

            $params[] = $profileData['bio'];            $success = $result['message'];            $success = $result['message'];

        } catch (PDOException $e) {

            // bio column doesn't exist, skip it            // Refresh user data            // Refresh user data

        }

                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");

        // Check if profile_public column exists

        try {            $stmt->execute([$currentUser->id]);            $stmt->execute([$currentUser->id]);

            $db->query("SELECT profile_public FROM users LIMIT 1");

            $query .= ", profile_public = ?";            $currentUser = $stmt->fetch(PDO::FETCH_OBJ);            $currentUser = $stmt->fetch(PDO::FETCH_OBJ);

            $params[] = $profileData['profile_public'];

        } catch (PDOException $e) {        } else {        } else {

            // profile_public column doesn't exist, skip it

        }            $error = $result['message'];            $error = $result['message'];

        

        $query .= " WHERE id = ?";        }        }

        $params[] = $currentUser->id;

            }}

        $stmt = $db->prepare($query);

        $stmt->execute($params);}

        

        return ['success' => true, 'message' => 'Profile updated successfully!'];/**

    } catch (Exception $e) {

        error_log("Profile update error: " . $e->getMessage());/** * Process profile update

        return ['success' => false, 'message' => 'Database error occurred. Please try again.'];

    } * Process profile update */

}

 */function processProfileUpdate(): array {

/**

 * Handle profile image uploadfunction processProfileUpdate(): array {    global $db, $currentUser;

 */

function handleProfileImageUpload(): array {    global $db, $currentUser;    

    global $currentUser;

            // Sanitize input data

    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {

        return ['success' => true, 'filename' => null];    // Sanitize input data    $profileData = [

    }

    $profileData = [        'first_name' => trim($_POST['first_name'] ?? ''),

    $uploadDir = __DIR__ . '/../uploads/profiles/';

    if (!is_dir($uploadDir)) {        'first_name' => trim($_POST['first_name'] ?? ''),        'last_name' => trim($_POST['last_name'] ?? ''),

        mkdir($uploadDir, 0755, true);

    }        'last_name' => trim($_POST['last_name'] ?? ''),        'description' => trim($_POST['description'] ?? ''),



    $file = $_FILES['profile_image'];        'description' => trim($_POST['description'] ?? ''),        'bio' => trim($_POST['bio'] ?? ''),

    $fileInfo = pathinfo($file['name']);

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];        'bio' => trim($_POST['bio'] ?? ''),        'school' => trim($_POST['school'] ?? ''),

    $fileExt = strtolower($fileInfo['extension'] ?? '');

        'school' => trim($_POST['school'] ?? ''),        'phone' => trim($_POST['phone'] ?? ''),

    // Validate file type

    if (!in_array($fileExt, $allowedTypes)) {        'phone' => trim($_POST['phone'] ?? ''),        'profile_public' => isset($_POST['profile_public']) ? 1 : 0

        return ['success' => false, 'message' => 'Profile image must be a JPG, PNG, GIF, or WebP file'];

    }        'profile_public' => isset($_POST['profile_public']) ? 1 : 0    ];



    // Validate file size (5MB)    ];

    if ($file['size'] > 5 * 1024 * 1024) {

        return ['success' => false, 'message' => 'Profile image must be smaller than 5MB'];    // Validate required fields

    }

    // Validate required fields    $errors = [];

    // Generate unique filename

    $filename = $currentUser->id . '_' . time() . '.' . $fileExt;    $errors = [];    if (empty($profileData['first_name'])) $errors[] = "First name is required";

    $uploadPath = $uploadDir . $filename;

    if (empty($profileData['first_name'])) $errors[] = "First name is required";    if (empty($profileData['last_name'])) $errors[] = "Last name is required";

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

        // Clean up old profile image    if (empty($profileData['last_name'])) $errors[] = "Last name is required";

        if (!empty($currentUser->profile_image) && file_exists($uploadDir . $currentUser->profile_image)) {

            unlink($uploadDir . $currentUser->profile_image);    // Handle profile image upload

        }

        return ['success' => true, 'filename' => $filename];    // Handle profile image upload    $profileImageResult = handleProfileImageUpload();

    } else {

        return ['success' => false, 'message' => 'Failed to upload profile image'];    $profileImageResult = handleProfileImageUpload();    if (!$profileImageResult['success']) {

    }

}    if (!$profileImageResult['success']) {        $errors[] = $profileImageResult['message'];



// Set page title and include header        $errors[] = $profileImageResult['message'];    } else if ($profileImageResult['filename']) {

$pageTitle = 'Edit Profile';

include __DIR__ . '/components/dashboard-header.php';    } else if ($profileImageResult['filename']) {        $profileData['profile_image'] = $profileImageResult['filename'];

?>

        $profileData['profile_image'] = $profileImageResult['filename'];    }

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Page Header -->    }

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">

        <div class="flex items-center justify-between">    if (!empty($errors)) {

            <div>

                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>    if (!empty($errors)) {        return ['success' => false, 'message' => implode('<br>', $errors)];

                <p class="text-gray-600 dark:text-gray-300 mt-1">Update your personal information and account settings</p>

            </div>        return ['success' => false, 'message' => implode('<br>', $errors)];    }

            <div class="hidden sm:flex items-center space-x-2">

                <div class="w-10 h-10 bg-gradient-to-r from-primary to-red-600 rounded-full flex items-center justify-center">    }

                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>    // Update database

                    </svg>

                </div>    // Update database    try {

            </div>

        </div>    try {        $query = "UPDATE users SET first_name = ?, last_name = ?, description = ?, school = ?, phone = ?";

    </div>

        $query = "UPDATE users SET first_name = ?, last_name = ?, description = ?, school = ?, phone = ?";        $params = [$profileData['first_name'], $profileData['last_name'], $profileData['description'], $profileData['school'], $profileData['phone']];

    <!-- Notification Messages -->

    <?php if ($success): ?>        $params = [$profileData['first_name'], $profileData['last_name'], $profileData['description'], $profileData['school'], $profileData['phone']];        

        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg p-4">

            <div class="flex items-center">                // Add optional fields if they exist in the database

                <div class="flex-shrink-0">

                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">        // Add optional fields if they exist in the database        if (isset($profileData['profile_image'])) {

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>

                    </svg>        if (isset($profileData['profile_image'])) {            $query .= ", profile_image = ?";

                </div>

                <div class="ml-3">            $query .= ", profile_image = ?";            $params[] = $profileData['profile_image'];

                    <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200"><?= htmlspecialchars($success) ?></p>

                </div>            $params[] = $profileData['profile_image'];        }

            </div>

        </div>        }        

    <?php endif; ?>

                // Check if bio column exists

    <?php if ($error): ?>

        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">        // Check if bio column exists        try {

            <div class="flex items-center">

                <div class="flex-shrink-0">        try {            $db->query("SELECT bio FROM users LIMIT 1");

                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>            $db->query("SELECT bio FROM users LIMIT 1");            $query .= ", bio = ?";

                    </svg>

                </div>            $query .= ", bio = ?";            $params[] = $profileData['bio'];

                <div class="ml-3">

                    <p class="text-sm font-medium text-red-800 dark:text-red-200"><?= $error ?></p>            $params[] = $profileData['bio'];        } catch (PDOException $e) {

                </div>

            </div>        } catch (PDOException $e) {            // bio column doesn't exist, skip it

        </div>

    <?php endif; ?>            // bio column doesn't exist, skip it        }



    <!-- Profile Form -->        }        

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">

        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">                // Check if profile_public column exists

            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h2>

            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Update your profile details and photo</p>        // Check if profile_public column exists        try {

        </div>

        try {            $db->query("SELECT profile_public FROM users LIMIT 1");

        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6" id="profileForm">

            <!-- Profile Image Section -->            $db->query("SELECT profile_public FROM users LIMIT 1");            $query .= ", profile_public = ?";

            <div class="flex items-start space-x-6">

                <div class="flex-shrink-0">            $query .= ", profile_public = ?";            $params[] = $profileData['profile_public'];

                    <div class="relative">

                        <?php             $params[] = $profileData['profile_public'];        } catch (PDOException $e) {

                        $profileImageUrl = !empty($currentUser->profile_image) 

                            ? $settings['site_url'] . '/uploads/profiles/' . $currentUser->profile_image        } catch (PDOException $e) {            // profile_public column doesn't exist, skip it

                            : 'https://via.placeholder.com/96x96/374151/ffffff?text=' . substr($currentUser->first_name ?? 'U', 0, 1);

                        ?>            // profile_public column doesn't exist, skip it        }

                        <img id="profilePreview" 

                             src="<?= $profileImageUrl ?>"         }        

                             alt="Profile Picture" 

                             class="w-24 h-24 rounded-full object-cover border-4 border-gray-200 dark:border-gray-600">                $query .= " WHERE id = ?";

                        <div class="absolute inset-0 rounded-full bg-black bg-opacity-40 opacity-0 hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">

                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">        $query .= " WHERE id = ?";        $params[] = $currentUser->id;

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>        $params[] = $currentUser->id;        

                            </svg>

                        </div>                $stmt = $db->prepare($query);

                    </div>

                </div>        $stmt = $db->prepare($query);        $stmt->execute($params);

                <div class="flex-1">

                    <label for="profile_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Picture</label>        $stmt->execute($params);        

                    <input type="file" 

                           id="profile_image"                 return ['success' => true, 'message' => 'Profile updated successfully!'];

                           name="profile_image" 

                           accept="image/*"        return ['success' => true, 'message' => 'Profile updated successfully!'];    } catch (Exception $e) {

                           class="block w-full text-sm text-gray-500 dark:text-gray-400

                                  file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0    } catch (Exception $e) {        error_log("Profile update error: " . $e->getMessage());

                                  file:text-sm file:font-semibold file:bg-primary file:text-white

                                  hover:file:bg-primary/90 file:cursor-pointer cursor-pointer        error_log("Profile update error: " . $e->getMessage());        return ['success' => false, 'message' => 'Database error occurred. Please try again.'];

                                  border border-gray-300 dark:border-gray-600 rounded-lg

                                  focus:ring-2 focus:ring-primary focus:border-primary">        return ['success' => false, 'message' => 'Database error occurred. Please try again.'];    }

                    <p id="imageStatus" class="mt-2 text-xs text-gray-500 dark:text-gray-400">

                        Upload a JPG, PNG, GIF, or WebP image. Maximum file size: 5MB.    }}

                    </p>

                </div>}

            </div>

/**

            <!-- Basic Information -->

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">/** * Handle profile image upload

                <div>

                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> * Handle profile image upload */

                        First Name <span class="text-red-500">*</span>

                    </label> */function handleProfileImageUpload(): array {

                    <input type="text" 

                           id="first_name" function handleProfileImageUpload(): array {    global $currentUser;

                           name="first_name" 

                           value="<?= htmlspecialchars($currentUser->first_name ?? '') ?>"     global $currentUser;    

                           required

                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {

                                  focus:ring-2 focus:ring-primary focus:border-primary

                                  dark:bg-gray-700 dark:text-white">    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {        return ['success' => true, 'filename' => null];

                </div>

        return ['success' => true, 'filename' => null];    }

                <div>

                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">    }

                        Last Name <span class="text-red-500">*</span>

                    </label>    $uploadDir = __DIR__ . '/../uploads/profiles/';

                    <input type="text" 

                           id="last_name"     $uploadDir = __DIR__ . '/../uploads/profiles/';    if (!is_dir($uploadDir)) {

                           name="last_name" 

                           value="<?= htmlspecialchars($currentUser->last_name ?? '') ?>"     if (!is_dir($uploadDir)) {        mkdir($uploadDir, 0755, true);

                           required

                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg        mkdir($uploadDir, 0755, true);    }

                                  focus:ring-2 focus:ring-primary focus:border-primary

                                  dark:bg-gray-700 dark:text-white">    }

                </div>

            </div>    $file = $_FILES['profile_image'];



            <!-- Contact Information -->    $file = $_FILES['profile_image'];    $fileInfo = pathinfo($file['name']);

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>    $fileInfo = pathinfo($file['name']);    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>

                    <input type="tel"     $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];    $fileExt = strtolower($fileInfo['extension'] ?? '');

                           id="phone" 

                           name="phone"     $fileExt = strtolower($fileInfo['extension'] ?? '');

                           value="<?= htmlspecialchars($currentUser->phone ?? '') ?>"

                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg    // Validate file type

                                  focus:ring-2 focus:ring-primary focus:border-primary

                                  dark:bg-gray-700 dark:text-white">    // Validate file type    if (!in_array($fileExt, $allowedTypes)) {

                </div>

    if (!in_array($fileExt, $allowedTypes)) {        return ['success' => false, 'message' => 'Profile image must be a JPG, PNG, GIF, or WebP file'];

                <div>

                    <label for="school" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School</label>        return ['success' => false, 'message' => 'Profile image must be a JPG, PNG, GIF, or WebP file'];    }

                    <input type="text" 

                           id="school"     }

                           name="school" 

                           value="<?= htmlspecialchars($currentUser->school ?? '') ?>"    // Validate file size (5MB)

                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg

                                  focus:ring-2 focus:ring-primary focus:border-primary    // Validate file size (5MB)    if ($file['size'] > 5 * 1024 * 1024) {

                                  dark:bg-gray-700 dark:text-white">

                </div>    if ($file['size'] > 5 * 1024 * 1024) {        return ['success' => false, 'message' => 'Profile image must be smaller than 5MB'];

            </div>

        return ['success' => false, 'message' => 'Profile image must be smaller than 5MB'];    }

            <!-- Bio and Description -->

            <div class="space-y-6">    }

                <div>

                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">About Me</label>    // Generate unique filename

                    <textarea id="description" 

                              name="description"     // Generate unique filename    $filename = $currentUser->id . '_' . time() . '.' . $fileExt;

                              rows="4"

                              placeholder="Tell us about yourself, your interests, skills, and what you'd like to achieve..."    $filename = $currentUser->id . '_' . time() . '.' . $fileExt;    $uploadPath = $uploadDir . $filename;

                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg

                                     focus:ring-2 focus:ring-primary focus:border-primary    $uploadPath = $uploadDir . $filename;

                                     dark:bg-gray-700 dark:text-white resize-none"><?= htmlspecialchars($currentUser->description ?? '') ?></textarea>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This information will be visible to other members and can help with project matching.</p>    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

                </div>

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {        // Clean up old profile image

                <div>

                    <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Short Bio</label>        // Clean up old profile image        if (!empty($currentUser->profile_image) && file_exists($uploadDir . $currentUser->profile_image)) {

                    <textarea id="bio" 

                              name="bio"         if (!empty($currentUser->profile_image) && file_exists($uploadDir . $currentUser->profile_image)) {            unlink($uploadDir . $currentUser->profile_image);

                              rows="2"

                              placeholder="A brief description that will appear on your member card..."            unlink($uploadDir . $currentUser->profile_image);        }

                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg

                                     focus:ring-2 focus:ring-primary focus:border-primary        }        return ['success' => true, 'filename' => $filename];

                                     dark:bg-gray-700 dark:text-white resize-none"><?= htmlspecialchars($currentUser->bio ?? '') ?></textarea>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This short bio will be displayed on the members page. Keep it concise!</p>        return ['success' => true, 'filename' => $filename];    } else {

                </div>

            </div>    } else {        return ['success' => false, 'message' => 'Failed to upload profile image'];



            <!-- Privacy Settings -->        return ['success' => false, 'message' => 'Failed to upload profile image'];    }

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">

                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Privacy Settings</h3>    }}

                <div class="space-y-4">

                    <div class="flex items-start">}

                        <div class="flex items-center h-5">

                            <input type="checkbox" // Set page title and include header

                                   id="profile_public" 

                                   name="profile_public" // Set page title and include header$pageTitle = 'Edit Profile';

                                   value="1"

                                   <?= ($currentUser->profile_public ?? 0) ? 'checked' : '' ?>$pageTitle = 'Edit Profile';include __DIR__ . '/components/dashboard-header.php';

                                   <?= !$currentUser->active_member ? 'disabled' : '' ?>

                                   class="w-4 h-4 text-primary focus:ring-primary border-gray-300 roundedinclude __DIR__ . '/components/dashboard-header.php';<div class="max-w-4xl mx-auto space-y-6">

                                          <?= !$currentUser->active_member ? 'opacity-50 cursor-not-allowed' : '' ?>">

                        </div>?>    <!-- Page Header -->

                        <div class="ml-3">

                            <label for="profile_public" class="text-sm font-medium text-gray-700 dark:text-gray-300">    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">

                                Show my profile on the public members page

                            </label><div class="max-w-4xl mx-auto space-y-6">        <div class="flex items-center justify-between">

                            <?php if (!$currentUser->active_member): ?>

                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">    <!-- Page Header -->            <div>

                                    ⚠️ Only active members can set their profile to public

                                </p>    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>

                            <?php else: ?>

                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">        <div class="flex items-center justify-between">                <p class="text-gray-600 dark:text-gray-300 mt-1">Update your personal information and account settings</p>

                                    When enabled, your profile will be visible to visitors on the members page

                                </p>            <div>            </div>

                            <?php endif; ?>

                        </div>                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>            <div class="hidden sm:flex items-center space-x-2">

                    </div>

                </div>                <p class="text-gray-600 dark:text-gray-300 mt-1">Update your personal information and account settings</p>                <div class="w-10 h-10 bg-gradient-to-r from-primary to-red-600 rounded-full flex items-center justify-center">

            </div>

            </div>                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

            <!-- Action Buttons -->

            <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700 gap-4">            <div class="hidden sm:flex items-center space-x-2">                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>

                <a href="<?= $settings['site_url'] ?>/dashboard/change-password.php"

                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600                 <div class="w-10 h-10 bg-gradient-to-r from-primary to-red-600 rounded-full flex items-center justify-center">                    </svg>

                          rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 

                          bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600                     <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">                </div>

                          transition-colors duration-200">

                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>            </div>

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>

                    </svg>                    </svg>        </div>

                    Change Password

                </a>                </div>    </div>



                <button type="submit"             </div>

                        name="save_profile"

                        id="saveButton"        </div>    <!-- Notification Messages -->

                        class="inline-flex items-center px-6 py-2 border border-transparent 

                               rounded-lg shadow-sm text-sm font-medium text-white     </div>    <?php if ($success): ?>

                               bg-gradient-to-r from-primary to-red-600 

                               hover:from-red-600 hover:to-primary         <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg p-4">

                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary

                               transition-all duration-200 transform hover:scale-105">    <!-- Notification Messages -->            <div class="flex items-center">

                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>    <?php if ($success): ?>                <div class="flex-shrink-0">

                    </svg>

                    Save Changes        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg p-4">                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                </button>

            </div>            <div class="flex items-center">                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>

        </form>

    </div>                <div class="flex-shrink-0">                    </svg>



    <!-- Linked Accounts Section -->                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">                </div>

    <?php if ($discordConfigured || $githubConfigured || $googleConfigured || $slackConfigured): ?>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>                <div class="ml-3">

        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">

            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Linked Accounts</h2>                    </svg>                    <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200"><?= htmlspecialchars($success) ?></p>

            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Connect your social media and development accounts</p>

        </div>                </div>                </div>



        <div class="p-6">                <div class="ml-3">            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Discord Integration -->                    <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200"><?= htmlspecialchars($success) ?></p>        </div>

                <?php if ($discordConfigured): ?>

                <div class="relative p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">                </div>    <?php endif; ?>

                    <div class="flex items-center justify-between">

                        <div class="flex items-center space-x-3">            </div>

                            <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">

                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">        </div>    <?php if ($error): ?>

                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>

                                </svg>    <?php endif; ?>        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">

                            </div>

                            <div>            <div class="flex items-center">

                                <p class="text-sm font-medium text-gray-900 dark:text-white">Discord</p>

                                <p class="text-xs text-gray-500 dark:text-gray-400">    <?php if ($error): ?>                <div class="flex-shrink-0">

                                    <?= $discordLink ? 'Connected' : 'Not connected' ?>

                                </p>        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            </div>

                        </div>            <div class="flex items-center">                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>

                        <div class="flex space-x-1">

                            <?php if ($discordLink): ?>                <div class="flex-shrink-0">                    </svg>

                                <form method="POST" class="inline">

                                    <button type="submit" name="unlink_discord" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Discord">                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">                </div>

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>                <div class="ml-3">

                                        </svg>

                                    </button>                    </svg>                    <p class="text-sm font-medium text-red-800 dark:text-red-200"><?= $error ?></p>

                                </form>

                            <?php else: ?>                </div>                </div>

                                <a href="<?= $settings['site_url'] ?>/auth/discord/?action=link" class="p-1 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" title="Link Discord">

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                <div class="ml-3">            </div>

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>

                                    </svg>                    <p class="text-sm font-medium text-red-800 dark:text-red-200"><?= $error ?></p>        </div>

                                </a>

                            <?php endif; ?>                </div>    <?php endif; ?>

                        </div>

                    </div>            </div>

                </div>

                <?php endif; ?>        </div>    <!-- Profile Form -->



                <!-- GitHub Integration -->    <?php endif; ?>    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">

                <?php if ($githubConfigured): ?>

                <div class="relative p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">

                    <div class="flex items-center justify-between">

                        <div class="flex items-center space-x-3">    <!-- Profile Form -->            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h2>

                            <div class="w-10 h-10 bg-gray-900 dark:bg-white rounded-lg flex items-center justify-center">

                                <svg class="w-6 h-6 text-white dark:text-gray-900" fill="currentColor" viewBox="0 0 24 24">    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Update your profile details and photo</p>

                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>

                                </svg>        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">        </div>

                            </div>

                            <div>            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h2>

                                <p class="text-sm font-medium text-gray-900 dark:text-white">GitHub</p>

                                <p class="text-xs text-gray-500 dark:text-gray-400">            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Update your profile details and photo</p>        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6" id="profileForm">

                                    <?= $githubLink ? 'Connected' : 'Not connected' ?>

                                </p>        </div>        $discord->unlinkDiscordAccount($currentUser->id);

                            </div>

                        </div>        $success = "Discord account unlinked successfully!";

                        <div class="flex space-x-1">

                            <?php if ($githubLink): ?>        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6" id="profileForm">        $discordLink = null;

                                <form method="POST" class="inline">

                                    <button type="submit" name="unlink_github" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink GitHub">            <!-- Profile Image Section -->    } elseif (isset($_POST['unlink_github'])) {

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>            <div class="flex items-start space-x-6">        $github->unlinkGitHubAccount($currentUser->id);

                                        </svg>

                                    </button>                <div class="flex-shrink-0">        $success = "GitHub account unlinked successfully!";

                                </form>

                            <?php else: ?>                    <div class="relative">        $githubLink = null;

                                <a href="<?= $settings['site_url'] ?>/auth/github/?action=link" class="p-1 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300" title="Link GitHub">

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                        <?php     } elseif (isset($_POST['unlink_google'])) {

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>

                                    </svg>                        $profileImageUrl = !empty($currentUser->profile_image)         $google->unlinkGoogleAccount($currentUser->id);

                                </a>

                            <?php endif; ?>                            ? $settings['site_url'] . '/uploads/profiles/' . $currentUser->profile_image        $success = "Google account unlinked successfully!";

                        </div>

                    </div>                            : $settings['site_url'] . '/images/default-avatar.png';        $googleLink = null;

                </div>

                <?php endif; ?>                        ?>    } elseif (isset($_POST['unlink_slack'])) {



                <!-- Google Integration -->                        <img id="profilePreview"         $slack->unlinkSlackAccount($currentUser->id);

                <?php if ($googleConfigured): ?>

                <div class="relative p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gradient-to-br from-blue-50 to-red-50 dark:from-blue-900/20 dark:to-red-900/20">                             src="<?= $profileImageUrl ?>"         $success = "Slack account unlinked successfully!";

                    <div class="flex items-center justify-between">

                        <div class="flex items-center space-x-3">                             alt="Profile Picture"         $slackLink = null;

                            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">

                                <svg class="w-6 h-6" viewBox="0 0 24 24">                             class="w-24 h-24 rounded-full object-cover border-4 border-gray-200 dark:border-gray-600">    } elseif (isset($_POST['save_profile']) || (!isset($_POST['unlink_discord']) && !isset($_POST['unlink_github']) && !isset($_POST['unlink_google']) && !isset($_POST['unlink_slack']))) {

                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>

                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>                        <div class="absolute inset-0 rounded-full bg-black bg-opacity-40 opacity-0 hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">        error_log("=== PROFILE UPDATE SECTION TRIGGERED ===");

                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>

                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">        error_log("Processing profile update for user ID: " . $currentUser->id);

                                </svg>

                            </div>                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>

                            <div>

                                <p class="text-sm font-medium text-gray-900 dark:text-white">Google</p>                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>        $newFirst = trim($_POST['first_name'] ?? '');

                                <p class="text-xs text-gray-500 dark:text-gray-400">

                                    <?= $googleLink ? 'Connected' : 'Not connected' ?>                            </svg>        $newLast = trim($_POST['last_name'] ?? '');

                                </p>

                            </div>                        </div>        $newDesc = trim($_POST['description'] ?? '');

                        </div>

                        <div class="flex space-x-1">                    </div>        $newBio = trim($_POST['bio'] ?? '');

                            <?php if ($googleLink): ?>

                                <form method="POST" class="inline">                </div>        $newSchool = trim($_POST['school'] ?? '');

                                    <button type="submit" name="unlink_google" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Google">

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                <div class="flex-1">        $newPhone = trim($_POST['phone'] ?? '');

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>

                                        </svg>                    <label for="profile_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Picture</label>        $profilePublic = isset($_POST['profile_public']) ? 1 : 0;

                                    </button>

                                </form>                    <input type="file" 

                            <?php else: ?>

                                <a href="<?= $settings['site_url'] ?>/auth/google/?action=link" class="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Link Google">                           id="profile_image"         $updateErrors = [];

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>                           name="profile_image"         if ($newFirst === '') $updateErrors[] = "First name cannot be empty.";

                                    </svg>

                                </a>                           accept="image/*"        if ($newLast === '') $updateErrors[] = "Last name cannot be empty.";

                            <?php endif; ?>

                        </div>                           class="block w-full text-sm text-gray-500 dark:text-gray-400

                    </div>

                </div>                                  file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0        // Handle profile image upload

                <?php endif; ?>

                                  file:text-sm file:font-semibold file:bg-primary file:text-white        $profileImageName = $currentUser->profile_image ?? '';

                <!-- Slack Integration -->

                <?php if ($slackConfigured): ?>                                  hover:file:bg-primary/90 file:cursor-pointer cursor-pointer        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {

                <div class="relative p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gradient-to-br from-green-50 to-purple-50 dark:from-green-900/20 dark:to-purple-900/20">

                    <div class="flex items-center justify-between">                                  border border-gray-300 dark:border-gray-600 rounded-lg            $uploadDir = __DIR__ . '/../uploads/profiles/';

                        <div class="flex items-center space-x-3">

                            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">                                  focus:ring-2 focus:ring-primary focus:border-primary">            if (!is_dir($uploadDir)) {

                                <svg class="w-6 h-6" viewBox="0 0 24 24">

                                    <path fill="#36C5F0" d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52z"/>                    <p id="imageStatus" class="mt-2 text-xs text-gray-500 dark:text-gray-400">                mkdir($uploadDir, 0755, true);

                                    <path fill="#36C5F0" d="M6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313z"/>

                                    <path fill="#2EB67D" d="M8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834z"/>                        Upload a JPG, PNG, GIF, or WebP image. Maximum file size: 5MB.            }

                                    <path fill="#2EB67D" d="M8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312z"/>

                                    <path fill="#ECB22E" d="M18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834z"/>                    </p>

                                    <path fill="#ECB22E" d="M17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312z"/>

                                    <path fill="#E01E5A" d="M15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52z"/>                </div>            $fileInfo = pathinfo($_FILES['profile_image']['name']);

                                    <path fill="#E01E5A" d="M15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>

                                </svg>            </div>            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                            </div>

                            <div>            $fileExt = strtolower($fileInfo['extension'] ?? '');

                                <p class="text-sm font-medium text-gray-900 dark:text-white">Slack</p>

                                <p class="text-xs text-gray-500 dark:text-gray-400">            <!-- Basic Information -->

                                    <?= $slackLink ? 'Connected' : 'Not connected' ?>

                                </p>            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">            if (in_array($fileExt, $allowedTypes)) {

                            </div>

                        </div>                <div>                if ($_FILES['profile_image']['size'] <= 5 * 1024 * 1024) { // 5MB limit

                        <div class="flex space-x-1">

                            <?php if ($slackLink): ?>                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">                    $profileImageName = $currentUser->id . '_' . time() . '.' . $fileExt;

                                <form method="POST" class="inline">

                                    <button type="submit" name="unlink_slack" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Slack">                        First Name <span class="text-red-500">*</span>                    $uploadPath = $uploadDir . $profileImageName;

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>                    </label>

                                        </svg>

                                    </button>                    <input type="text"                     if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {

                                </form>

                            <?php else: ?>                           id="first_name"                         // Delete old profile image if it exists

                                <a href="<?= $settings['site_url'] ?>/auth/slack/?action=link" class="p-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300" title="Link Slack">

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                           name="first_name"                         if (!empty($currentUser->profile_image) && file_exists($uploadDir . $currentUser->profile_image)) {

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>

                                    </svg>                           value="<?= htmlspecialchars($currentUser->first_name ?? '') ?>"                             unlink($uploadDir . $currentUser->profile_image);

                                </a>

                            <?php endif; ?>                           required                        }

                        </div>

                    </div>                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg                    } else {

                </div>

                <?php endif; ?>                                  focus:ring-2 focus:ring-primary focus:border-primary                        $updateErrors[] = "Failed to upload profile image.";

            </div>

                                  dark:bg-gray-700 dark:text-white">                        $profileImageName = $currentUser->profile_image ?? '';

            <!-- Integration Management Link -->

            <div class="text-center mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">                </div>                    }

                <a href="<?= $settings['site_url'] ?>/dashboard/edit-integrations.php"

                   class="inline-flex items-center text-sm font-medium text-primary hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200">                } else {

                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>                <div>                    $updateErrors[] = "Profile image must be smaller than 5MB.";

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>

                    </svg>                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">                }

                    Manage all integrations →

                </a>                        Last Name <span class="text-red-500">*</span>            } else {

            </div>

        </div>                    </label>                $updateErrors[] = "Profile image must be a JPG, PNG, GIF, or WebP file.";

    </div>

    <?php endif; ?>                    <input type="text"             }

</div>

                           id="last_name"         }

<script>

// Profile Image Preview                           name="last_name" 

document.getElementById('profile_image').addEventListener('change', function(e) {

    const file = e.target.files[0];                           value="<?= htmlspecialchars($currentUser->last_name ?? '') ?>"         if (empty($updateErrors)) {

    const preview = document.getElementById('profilePreview');

    const status = document.getElementById('imageStatus');                           required            try {

    

    if (!file) {                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg                // Update with basic columns that exist

        status.textContent = 'Upload a JPG, PNG, GIF, or WebP image. Maximum file size: 5MB.';

        status.className = 'mt-2 text-xs text-gray-500 dark:text-gray-400';                                  focus:ring-2 focus:ring-primary focus:border-primary                $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, description = ?, school = ?, phone = ? WHERE id = ?");

        return;

    }                                  dark:bg-gray-700 dark:text-white">                $result = $stmt->execute([$newFirst, $newLast, $newDesc, $newSchool, $newPhone, $currentUser->id]);



    // Validate file type                </div>

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {            </div>                if ($result) {

        alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');

        e.target.value = '';                    // Refresh user data

        status.textContent = 'Invalid file type. Please select a JPG, PNG, GIF, or WebP image.';

        status.className = 'mt-2 text-xs text-red-500 dark:text-red-400';            <!-- Contact Information -->                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");

        return;

    }            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">                    $stmt->execute([$currentUser->id]);



    // Validate file size (5MB)                <div>                    $currentUser = $stmt->fetch(PDO::FETCH_OBJ);

    if (file.size > 5 * 1024 * 1024) {

        alert('File size must be less than 5MB');                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>

        e.target.value = '';

        status.textContent = 'File too large. Maximum size is 5MB.';                    <input type="tel"                     $success = "Profile updated successfully!";

        status.className = 'mt-2 text-xs text-red-500 dark:text-red-400';

        return;                           id="phone"                     error_log("Profile update successful for user ID: " . $currentUser->id);

    }

                           name="phone"                 } else {

    // Preview the image

    const reader = new FileReader();                           value="<?= htmlspecialchars($currentUser->phone ?? '') ?>"                    $error = "Failed to update profile. Please try again.";

    reader.onload = function(e) {

        preview.src = e.target.result;                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg                    error_log("Profile update failed for user ID: " . $currentUser->id);

    };

    reader.readAsDataURL(file);                                  focus:ring-2 focus:ring-primary focus:border-primary                }



    // Update status                                  dark:bg-gray-700 dark:text-white">            } catch (Exception $e) {

    const sizeMB = (file.size / 1024 / 1024).toFixed(2);

    status.innerHTML = `✅ Image selected: ${file.name} (${sizeMB} MB)`;                </div>                $error = "Database error: " . $e->getMessage();

    status.className = 'mt-2 text-xs text-emerald-600 dark:text-emerald-400';

});                error_log("Profile update error: " . $e->getMessage());



// Form Validation and Submission                <div>            }

document.getElementById('profileForm').addEventListener('submit', function(e) {

    const firstName = document.getElementById('first_name');                    <label for="school" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School</label>        } else {

    const lastName = document.getElementById('last_name');

    const saveButton = document.getElementById('saveButton');                    <input type="text"             $error = implode('<br>', $updateErrors);



    // Basic validation                           id="school"             error_log("Validation errors: " . implode(', ', $updateErrors));

    if (!firstName.value.trim()) {

        alert('First name is required');                           name="school"         }

        firstName.focus();

        e.preventDefault();                           value="<?= htmlspecialchars($currentUser->school ?? '') ?>"    }

        return false;

    }                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg}



    if (!lastName.value.trim()) {                                  focus:ring-2 focus:ring-primary focus:border-primary

        alert('Last name is required');

        lastName.focus();                                  dark:bg-gray-700 dark:text-white">$pageTitle = 'Edit Profile';

        e.preventDefault();

        return false;                </div>include __DIR__ . '/components/dashboard-header.php';

    }

            </div>?>

    // Update button state

    saveButton.innerHTML = `

        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">

            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>            <!-- Bio and Description --><div class="space-y-6">

            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>

        </svg>            <div class="space-y-6">    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">

        Saving...

    `;                <div>        <div class="flex items-center justify-between">

    saveButton.disabled = true;

                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">About Me</label>            <div>

    return true;

});                    <textarea id="description"                 <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Profile</h2>



// Character counter for bio fields                              name="description"                 <p class="text-gray-600 dark:text-gray-300 mt-1">Update your personal information and preferences</p>

function addCharacterCounter(textareaId, maxLength = 500) {

    const textarea = document.getElementById(textareaId);                              rows="4"            </div>

    if (!textarea) return;

                                  placeholder="Tell us about yourself, your interests, skills, and what you'd like to achieve..."        </div>

    const counter = document.createElement('div');

    counter.className = 'text-xs text-gray-500 dark:text-gray-400 mt-1';                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg    </div>

    textarea.parentNode.appendChild(counter);

                                         focus:ring-2 focus:ring-primary focus:border-primary    <?php if ($success): ?>

    function updateCounter() {

        const remaining = maxLength - textarea.value.length;                                     dark:bg-gray-700 dark:text-white resize-none"><?= htmlspecialchars($currentUser->description ?? '') ?></textarea>        <div class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-700 rounded-md p-4">

        counter.textContent = `${remaining} characters remaining`;

        counter.className = remaining < 50                     <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This information will be visible to other members and can help with project matching.</p>            <div class="flex">

            ? 'text-xs text-amber-500 dark:text-amber-400 mt-1'

            : 'text-xs text-gray-500 dark:text-gray-400 mt-1';                </div>                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

    }

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>

    textarea.addEventListener('input', updateCounter);

    updateCounter();                <div>                </svg>

}

                    <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Short Bio</label>                <div class="ml-3">

// Add character counters

addCharacterCounter('bio', 200);                    <textarea id="bio"                     <p class="text-sm text-green-700 dark:text-green-300"><?= htmlspecialchars($success) ?></p>

addCharacterCounter('description', 1000);

</script>                              name="bio"                 </div>



<?php include __DIR__ . '/components/dashboard-footer.php'; ?>                              rows="2"            </div>

                              placeholder="A brief description that will appear on your member card..."        </div>

                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg    <?php endif; ?>

                                     focus:ring-2 focus:ring-primary focus:border-primary

                                     dark:bg-gray-700 dark:text-white resize-none"><?= htmlspecialchars($currentUser->bio ?? '') ?></textarea>    <?php if ($error): ?>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This short bio will be displayed on the members page. Keep it concise!</p>        <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 rounded-md p-4">

                </div>            <div class="flex">

            </div>                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>

            <!-- Privacy Settings -->                </svg>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">                <div class="ml-3">

                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Privacy Settings</h3>                    <p class="text-sm text-red-700 dark:text-red-300"><?= $error ?></p>

                <div class="space-y-4">                </div>

                    <div class="flex items-start">            </div>

                        <div class="flex items-center h-5">        </div>

                            <input type="checkbox"     <?php endif; ?>

                                   id="profile_public"     <div class="bg-white dark:bg-gray-800 rounded-lg shadow">

                                   name="profile_public"         <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">

                                   value="1"            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Personal Information</h3>

                                   <?= ($currentUser->profile_public ?? 0) ? 'checked' : '' ?>        </div>

                                   <?= !$currentUser->active_member ? 'disabled' : '' ?>

                                   class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">

                                          <?= !$currentUser->active_member ? 'opacity-50 cursor-not-allowed' : '' ?>">            <!-- Profile Image Upload -->

                        </div>            <div>

                        <div class="ml-3">                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Picture</label>

                            <label for="profile_public" class="text-sm font-medium text-gray-700 dark:text-gray-300">                <div class="flex items-center space-x-6">

                                Show my profile on the public members page                    <div class="flex-shrink-0">

                            </label>                        <?php

                            <?php if (!$currentUser->active_member): ?>                        $currentImage = '';

                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">                        if (!empty($currentUser->profile_image)) {

                                    ⚠️ Only active members can set their profile to public                            $currentImage = '/uploads/profiles/' . $currentUser->profile_image;

                                </p>                        } elseif (!empty($currentUser->discord_id) && !empty($currentUser->discord_avatar)) {

                            <?php else: ?>                            $currentImage = "https://cdn.discordapp.com/avatars/{$currentUser->discord_id}/{$currentUser->discord_avatar}.png?size=128";

                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">                        } else {

                                    When enabled, your profile will be visible to visitors on the members page                            $currentImage = '/images/default-avatar.svg';

                                </p>                        }

                            <?php endif; ?>                        ?>

                        </div>                        <img class="h-20 w-20 rounded-full object-cover"

                    </div>                            src="<?= htmlspecialchars($currentImage) ?>"

                </div>                            alt="Current profile picture"

            </div>                            onerror="this.src='/images/default-avatar.svg'">

                    </div>

            <!-- Action Buttons -->                    <div class="flex-1">

            <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700 gap-4">                        <input type="file"

                <a href="<?= $settings['site_url'] ?>/dashboard/change-password.php"                            id="profile_image"

                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600                             name="profile_image"

                          rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300                             accept="image/*"

                          bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600                             class="block w-full text-sm text-gray-500 dark:text-gray-400

                          transition-colors duration-200">                                      file:mr-4 file:py-2 file:px-4

                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">                                      file:rounded-md file:border-0

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>                                      file:text-sm file:font-semibold

                    </svg>                                      file:bg-primary file:text-white

                    Change Password                                      hover:file:bg-primary/90

                </a>                                      file:cursor-pointer cursor-pointer">

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">

                <button type="submit"                             Upload a JPG, PNG, GIF, or WebP image. Maximum file size: 5MB.

                        name="save_profile"                        </p>

                        id="saveButton"                    </div>

                        class="inline-flex items-center px-6 py-2 border border-transparent                 </div>

                               rounded-lg shadow-sm text-sm font-medium text-white             </div>

                               bg-gradient-to-r from-primary to-red-600 

                               hover:from-red-600 hover:to-primary             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary                <div>

                               transition-all duration-200 transform hover:scale-105">                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name *</label>

                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">                    <input type="text"

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>                        id="first_name"

                    </svg>                        name="first_name"

                    Save Changes                        value="<?= htmlspecialchars($currentUser->first_name ?? '') ?>"

                </button>                        required

            </div>                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">

        </form>                </div>

    </div>

                <div>

    <!-- Linked Accounts Section -->                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name *</label>

    <?php if (array_filter($oauthConfig)): ?>                    <input type="text"

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">                        id="last_name"

        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">                        name="last_name"

            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Linked Accounts</h2>                        value="<?= htmlspecialchars($currentUser->last_name ?? '') ?>"

            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Connect your social media and development accounts</p>                        required

        </div>                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">

                </div>

        <div class="p-6">            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Discord Integration -->                <div>

                <?php if ($oauthConfig['discord']): ?>                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>

                <div class="relative p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">                    <input type="tel"

                    <div class="flex items-center justify-between">                        id="phone"

                        <div class="flex items-center space-x-3">                        name="phone"

                            <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">                        value="<?= htmlspecialchars($currentUser->phone ?? '') ?>"

                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">

                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>                </div>

                                </svg>

                            </div>                <div>

                            <div>                    <label for="school" class="block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>

                                <p class="text-sm font-medium text-gray-900 dark:text-white">Discord</p>                    <input type="text"

                                <p class="text-xs text-gray-500 dark:text-gray-400">                        id="school"

                                    <?= $oauthLinks['discord'] ? 'Connected' : 'Not connected' ?>                        name="school"

                                </p>                        value="<?= htmlspecialchars($currentUser->school ?? '') ?>"

                            </div>                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">

                        </div>                </div>

                        <div class="flex space-x-1">            </div>

                            <?php if ($oauthLinks['discord']): ?>            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <form method="POST" class="inline">            </div>

                                    <button type="submit" name="unlink_discord" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Discord">    </div>

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">    <div>

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">About Me</label>

                                        </svg>        <textarea id="description"

                                    </button>            name="description"

                                </form>            rows="4"

                            <?php else: ?>            placeholder="Tell us about yourself, your interests, skills, and what you'd like to achieve..."

                                <a href="<?= $settings['site_url'] ?>/auth/discord/?action=link" class="p-1 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" title="Link Discord">            class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($currentUser->description ?? '') ?></textarea>

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This information will be visible to other members and can help with project matching.</p>

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>    </div>

                                    </svg>

                                </a>    <div>

                            <?php endif; ?>        <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Short Bio</label>

                        </div>        <textarea id="bio"

                    </div>            name="bio"

                </div>            rows="2"

                <?php endif; ?>            placeholder="A brief description that will appear on your member card..."

            class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($currentUser->bio ?? '') ?></textarea>

                <!-- GitHub Integration -->        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This short bio will be displayed on the members page. Keep it concise!</p>

                <?php if ($oauthConfig['github']): ?>    </div>

                <div class="relative p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">

                    <div class="flex items-center justify-between">    <!-- Privacy Settings -->

                        <div class="flex items-center space-x-3">    <div class="border-t border-gray-200 dark:border-gray-600 pt-6">

                            <div class="w-10 h-10 bg-gray-900 dark:bg-white rounded-lg flex items-center justify-center">        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Privacy Settings</h4>

                                <svg class="w-6 h-6 text-white dark:text-gray-900" fill="currentColor" viewBox="0 0 24 24">        <div class="space-y-4">

                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>            <div class="flex items-center">

                                </svg>                <input type="checkbox"

                            </div>                    id="profile_public"

                            <div>                    name="profile_public"

                                <p class="text-sm font-medium text-gray-900 dark:text-white">GitHub</p>                    value="1"

                                <p class="text-xs text-gray-500 dark:text-gray-400">                    <?= ($currentUser->profile_public ?? 0) ? 'checked' : '' ?>

                                    <?= $oauthLinks['github'] ? 'Connected' : 'Not connected' ?>                    <?= !$currentUser->active_member ? 'disabled' : '' ?>

                                </p>                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded <?= !$currentUser->active_member ? 'opacity-50 cursor-not-allowed' : '' ?>">

                            </div>                <label for="profile_public" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">

                        </div>                    Show my profile on the public members page

                        <div class="flex space-x-1">                </label>

                            <?php if ($oauthLinks['github']): ?>            </div>

                                <form method="POST" class="inline">            <?php if (!$currentUser->active_member): ?>

                                    <button type="submit" name="unlink_github" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink GitHub">                <p class="text-xs text-amber-600 dark:text-amber-400">

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                    ⚠️ Only active members can set their profile to public

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>                </p>

                                        </svg>            <?php else: ?>

                                    </button>                <p class="text-xs text-gray-500 dark:text-gray-400">

                                </form>                    When enabled, your profile will be visible to visitors on the members page

                            <?php else: ?>                </p>

                                <a href="<?= $settings['site_url'] ?>/auth/github/?action=link" class="p-1 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300" title="Link GitHub">            <?php endif; ?>

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">        </div>

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>    </div>

                                    </svg>

                                </a>    <div class="border-t border-gray-200 dark:border-gray-600 pt-6">

                            <?php endif; ?>        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Linked Accounts</h4>

                        </div>        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                    </div>            <!-- Only show enabled integrations -->

                </div>

                <?php endif; ?>            <?php if ($discordConfigured): ?>

                <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">

                <!-- Google Integration -->                    <div class="flex items-center space-x-2">

                <?php if ($oauthConfig['google']): ?>                        <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 24 24">

                <div class="relative p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gradient-to-br from-blue-50 to-red-50 dark:from-blue-900/20 dark:to-red-900/20">                            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z" />

                    <div class="flex items-center justify-between">                        </svg>

                        <div class="flex items-center space-x-3">                        <div>

                            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">                            <p class="text-xs font-medium text-gray-900 dark:text-white">Discord</p>

                                <svg class="w-6 h-6" viewBox="0 0 24 24">                            <p class="text-xs text-gray-500 dark:text-gray-400">

                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>                                <?php if ($discordLink): ?>

                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>                                    <?= htmlspecialchars($discordLink['discord_username']) ?>

                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>                                <?php else: ?>

                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>                                    Not linked

                                </svg>                                <?php endif; ?>

                            </div>                            </p>

                            <div>                        </div>

                                <p class="text-sm font-medium text-gray-900 dark:text-white">Google</p>                    </div>

                                <p class="text-xs text-gray-500 dark:text-gray-400">                    <div class="flex space-x-1">

                                    <?= $oauthLinks['google'] ? 'Connected' : 'Not connected' ?>                        <?php if ($discordLink): ?>

                                </p>                            <form method="POST" class="inline">

                            </div>                                <input type="hidden" name="unlink_discord" value="1">

                        </div>                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Discord">

                        <div class="flex space-x-1">                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <?php if ($oauthLinks['google']): ?>                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />

                                <form method="POST" class="inline">                                    </svg>

                                    <button type="submit" name="unlink_google" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Google">                                </button>

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                            </form>

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>                        <?php else: ?>

                                        </svg>                            <a href="<?= $settings['site_url'] ?>/auth/discord/?action=link" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" title="Link Discord">

                                    </button>                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                </form>                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />

                            <?php else: ?>                                </svg>

                                <a href="<?= $settings['site_url'] ?>/auth/google/?action=link" class="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Link Google">                            </a>

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                        <?php endif; ?>

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>                    </div>

                                    </svg>                </div>

                                </a>            <?php endif; ?>

                            <?php endif; ?>

                        </div>            <?php if ($githubConfigured): ?>

                    </div>                <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">

                </div>                    <div class="flex items-center space-x-2">

                <?php endif; ?>                        <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">

                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />

                <!-- Slack Integration -->                        </svg>

                <?php if ($oauthConfig['slack']): ?>                        <div>

                <div class="relative p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gradient-to-br from-green-50 to-purple-50 dark:from-green-900/20 dark:to-purple-900/20">                            <p class="text-xs font-medium text-gray-900 dark:text-white">GitHub</p>

                    <div class="flex items-center justify-between">                            <p class="text-xs text-gray-500 dark:text-gray-400">

                        <div class="flex items-center space-x-3">                                <?php if ($githubLink): ?>

                            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">                                    <?= htmlspecialchars($githubLink['github_username']) ?>

                                <svg class="w-6 h-6" viewBox="0 0 24 24">                                <?php else: ?>

                                    <path fill="#36C5F0" d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52z"/>                                    Not linked

                                    <path fill="#36C5F0" d="M6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313z"/>                                <?php endif; ?>

                                    <path fill="#2EB67D" d="M8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834z"/>                            </p>

                                    <path fill="#2EB67D" d="M8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312z"/>                        </div>

                                    <path fill="#ECB22E" d="M18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834z"/>                    </div>

                                    <path fill="#ECB22E" d="M17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312z"/>                    <div class="flex space-x-1">

                                    <path fill="#E01E5A" d="M15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52z"/>                        <?php if ($githubLink): ?>

                                    <path fill="#E01E5A" d="M15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>                            <form method="POST" class="inline">

                                </svg>                                <input type="hidden" name="unlink_github" value="1">

                            </div>                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink GitHub">

                            <div>                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <p class="text-sm font-medium text-gray-900 dark:text-white">Slack</p>                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />

                                <p class="text-xs text-gray-500 dark:text-gray-400">                                    </svg>

                                    <?= $oauthLinks['slack'] ? 'Connected' : 'Not connected' ?>                                </button>

                                </p>                            </form>

                            </div>                        <?php else: ?>

                        </div>                            <a href="<?= $settings['site_url'] ?>/auth/github/?action=link" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300" title="Link GitHub">

                        <div class="flex space-x-1">                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <?php if ($oauthLinks['slack']): ?>                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />

                                <form method="POST" class="inline">                                </svg>

                                    <button type="submit" name="unlink_slack" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Slack">                            </a>

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                        <?php endif; ?>

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>                    </div>

                                        </svg>                </div>

                                    </button>            <?php endif; ?>

                                </form>

                            <?php else: ?>            <?php if ($googleConfigured): ?>

                                <a href="<?= $settings['site_url'] ?>/auth/slack/?action=link" class="p-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300" title="Link Slack">                <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                    <div class="flex items-center space-x-2">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>                        <svg class="w-5 h-5" viewBox="0 0 24 24">

                                    </svg>                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />

                                </a>                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />

                            <?php endif; ?>                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />

                        </div>                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />

                    </div>                        </svg>

                </div>                        <div>

                <?php endif; ?>                            <p class="text-xs font-medium text-gray-900 dark:text-white">Google</p>

            </div>                            <p class="text-xs text-gray-500 dark:text-gray-400">

                                <?php if ($googleLink): ?>

            <!-- Integration Management Link -->                                    <?= htmlspecialchars($googleLink['google_email']) ?>

            <div class="text-center mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">                                <?php else: ?>

                <a href="<?= $settings['site_url'] ?>/dashboard/edit-integrations.php"                                    Not linked

                   class="inline-flex items-center text-sm font-medium text-primary hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200">                                <?php endif; ?>

                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">                            </p>

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>                        </div>

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>                    </div>

                    </svg>                    <div class="flex space-x-1">

                    Manage all integrations →                        <?php if ($googleLink): ?>

                </a>                            <form method="POST" class="inline">

            </div>                                <input type="hidden" name="unlink_google" value="1">

        </div>                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Google">

    </div>                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

    <?php endif; ?>                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />

</div>                                    </svg>

                                </button>

<script>                            </form>

// Profile Image Preview                        <?php else: ?>

document.getElementById('profile_image').addEventListener('change', function(e) {                            <a href="<?= $settings['site_url'] ?>/auth/google/?action=link" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Link Google">

    const file = e.target.files[0];                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

    const preview = document.getElementById('profilePreview');                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />

    const status = document.getElementById('imageStatus');                                </svg>

                                </a>

    if (!file) {                        <?php endif; ?>

        status.textContent = 'Upload a JPG, PNG, GIF, or WebP image. Maximum file size: 5MB.';                    </div>

        status.className = 'mt-2 text-xs text-gray-500 dark:text-gray-400';                </div>

        return;            <?php endif; ?>

    }

            <?php if ($slackConfigured): ?>

    // Validate file type                <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];                    <div class="flex items-center space-x-2">

    if (!allowedTypes.includes(file.type)) {                        <svg class="w-5 h-5" viewBox="0 0 24 24">

        alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');                            <path fill="#E01E5A" d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52z" />

        e.target.value = '';                            <path fill="#E01E5A" d="M6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313z" />

        status.textContent = 'Invalid file type. Please select a JPG, PNG, GIF, or WebP image.';                            <path fill="#36C5F0" d="M8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834z" />

        status.className = 'mt-2 text-xs text-red-500 dark:text-red-400';                            <path fill="#36C5F0" d="M8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312z" />

        return;                            <path fill="#2EB67D" d="M18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834z" />

    }                            <path fill="#2EB67D" d="M17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312z" />

                            <path fill="#ECB22E" d="M15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52z" />

    // Validate file size (5MB)                            <path fill="#ECB22E" d="M15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z" />

    if (file.size > 5 * 1024 * 1024) {                        </svg>

        alert('File size must be less than 5MB');                        <div>

        e.target.value = '';                            <p class="text-xs font-medium text-gray-900 dark:text-white">Slack</p>

        status.textContent = 'File too large. Maximum size is 5MB.';                            <p class="text-xs text-gray-500 dark:text-gray-400">

        status.className = 'mt-2 text-xs text-red-500 dark:text-red-400';                                <?php if ($slackLink): ?>

        return;                                    <?= htmlspecialchars($slackLink['slack_username']) ?>

    }                                <?php else: ?>

                                    Not linked

    // Preview the image                                <?php endif; ?>

    const reader = new FileReader();                            </p>

    reader.onload = function(e) {                        </div>

        preview.src = e.target.result;                    </div>

    };                    <div class="flex space-x-1">

    reader.readAsDataURL(file);                        <?php if ($slackLink): ?>

                            <form method="POST" class="inline">

    // Update status                                <input type="hidden" name="unlink_slack" value="1">

    const sizeMB = (file.size / 1024 / 1024).toFixed(2);                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Slack">

    status.innerHTML = `✅ Image selected: ${file.name} (${sizeMB} MB)`;                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

    status.className = 'mt-2 text-xs text-emerald-600 dark:text-emerald-400';                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />

});                                    </svg>

                                </button>

// Form Validation and Submission                            </form>

document.getElementById('profileForm').addEventListener('submit', function(e) {                        <?php else: ?>

    const firstName = document.getElementById('first_name');                            <a href="<?= $settings['site_url'] ?>/auth/slack/?action=link" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300" title="Link Slack">

    const lastName = document.getElementById('last_name');                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

    const saveButton = document.getElementById('saveButton');                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />

                                </svg>

    // Basic validation                            </a>

    if (!firstName.value.trim()) {                        <?php endif; ?>

        alert('First name is required');                    </div>

        firstName.focus();                </div>

        e.preventDefault();            <?php endif; ?>

        return false;        </div>

    }        <div class="text-center mt-6">

            <a href="<?= $settings['site_url'] ?>/dashboard/edit-integrations.php"

    if (!lastName.value.trim()) {                class="inline-flex items-center text-sm text-primary hover:text-red-600 dark:text-red-400 dark:hover:text-red-300">

        alert('Last name is required');                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">

        lastName.focus();                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>

        e.preventDefault();                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>

        return false;                </svg>

    }                Manage all integrations →

            </a>

    // Update button state        </div>

    saveButton.innerHTML = `    </div>

        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">

            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>    <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">

            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>        <a href="<?= $settings['site_url'] ?>/dashboard/change-password.php"

        </svg>            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">

        Saving...            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">

    `;                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>

    saveButton.disabled = true;            </svg>

            Change Password

    // Re-enable button after 10 seconds as failsafe        </a>

    setTimeout(() => {

        if (saveButton.disabled) {        <div class="flex space-x-4">

            saveButton.innerHTML = `            <button type="submit"

                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">                name="save_profile"

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">

                </svg>                Save Changes

                Save Changes            </button>

            `;        </div>

            saveButton.disabled = false;    </div>

        }    </form>

    }, 10000);</div>

</div>

    return true;

});<script>

    // Profile image preview functionality

// Character counter for bio fields    const profileImageInput = document.getElementById('profile_image');

function addCharacterCounter(textareaId, maxLength = 500) {    if (profileImageInput) {

    const textarea = document.getElementById(textareaId);        profileImageInput.addEventListener('change', function(e) {

    if (!textarea) return;            const file = e.target.files[0];

                if (file) {

    const counter = document.createElement('div');                // Validate file type

    counter.className = 'text-xs text-gray-500 dark:text-gray-400 mt-1';                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    textarea.parentNode.appendChild(counter);                if (!allowedTypes.includes(file.type)) {

                        alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');

    function updateCounter() {                    e.target.value = '';

        const remaining = maxLength - textarea.value.length;                    return;

        counter.textContent = `${remaining} characters remaining`;                }

        counter.className = remaining < 50 

            ? 'text-xs text-amber-500 dark:text-amber-400 mt-1'                // Validate file size (5MB)

            : 'text-xs text-gray-500 dark:text-gray-400 mt-1';                if (file.size > 5 * 1024 * 1024) {

    }                    alert('File size must be less than 5MB');

                        e.target.value = '';

    textarea.addEventListener('input', updateCounter);                    return;

    updateCounter();                }

}

                // Preview the image

// Add character counters                const reader = new FileReader();

addCharacterCounter('bio', 200);                reader.onload = function(e) {

addCharacterCounter('description', 1000);                    const img = document.querySelector('.h-20.w-20.rounded-full');

                    if (img) {

// Auto-save form data to localStorage (optional enhancement)                        img.src = e.target.result;

function autoSaveForm() {                    }

    const form = document.getElementById('profileForm');                };

    const formData = new FormData(form);                reader.readAsDataURL(file);

    const data = {};

                    // Show success message

    for (let [key, value] of formData.entries()) {                const labelElement = document.querySelector('label[for="profile_image"]');

        if (key !== 'profile_image') { // Don't save file inputs                if (labelElement) {

            data[key] = value;                    const parentDiv = labelElement.closest('div');

        }                    if (parentDiv) {

    }                        const messageP = parentDiv.querySelector('p');

                            if (messageP) {

    localStorage.setItem('profileFormData', JSON.stringify(data));                            messageP.innerHTML = '✅ Image selected: ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';

}                            messageP.className = 'mt-2 text-xs text-green-600 dark:text-green-400';

                        }

// Auto-save every 30 seconds                    }

setInterval(autoSaveForm, 30000);                }

            }

// Restore form data on page load        });

window.addEventListener('load', function() {    }

    const savedData = localStorage.getItem('profileFormData');

    if (savedData) {    // Form submission with validation

        try {    document.addEventListener('DOMContentLoaded', function() {

            const data = JSON.parse(savedData);        const profileForm = document.querySelector('form[enctype="multipart/form-data"]');

            Object.keys(data).forEach(key => {        const saveBtn = document.querySelector('button[name="save_profile"]');

                const element = document.querySelector(`[name="${key}"]`);

                if (element && element.type !== 'file') {        if (profileForm && saveBtn) {

                    if (element.type === 'checkbox') {            console.log('Profile form and save button found');

                        element.checked = data[key] === '1';

                    } else {            // Handle form submission

                        element.value = data[key];            profileForm.addEventListener('submit', function(e) {

                    }                console.log('Form submit event triggered');

                }

            });                // Basic validation

        } catch (e) {                const firstName = document.querySelector('input[name="first_name"]');

            // Invalid saved data, ignore                const lastName = document.querySelector('input[name="last_name"]');

        }

    }                if (!firstName || !firstName.value.trim()) {

});                    alert('First name is required');

                    e.preventDefault();

// Clear saved data on successful submission                    return false;

if (window.location.search.includes('success') || document.querySelector('.bg-emerald-50')) {                }

    localStorage.removeItem('profileFormData');

}                if (!lastName || !lastName.value.trim()) {

</script>                    alert('Last name is required');

                    e.preventDefault();

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>                    return false;
                }

                console.log('Validation passed, submitting form');

                // Update button state
                saveBtn.innerHTML = '⏳ Saving...';
                saveBtn.disabled = true;

                // Re-enable button after 10 seconds as failsafe
                setTimeout(function() {
                    if (saveBtn.disabled) {
                        saveBtn.innerHTML = 'Save Changes';
                        saveBtn.disabled = false;
                        console.log('Button re-enabled after timeout');
                    }
                }, 10000);

                return true; // Allow form submission
            });
        } else {
            console.log('Profile form or save button not found');
        }
    });
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>