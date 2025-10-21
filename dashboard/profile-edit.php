<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/DiscordOAuth.php';
require_once __DIR__ . '/../core/classes/GitHubOAuth.php';
require_once __DIR__ . '/../core/classes/GoogleOAuth.php';
require_once __DIR__ . '/../core/classes/SlackOAuth.php';
checkActiveOrLimitedAccess();

global $db, $currentUser, $settings;

// Additional safety check for $currentUser
if (!$currentUser) {
    header('Location: /dashboard/login.php');
    exit;
}

$success = $error = null;

if (isset($_SESSION['account_link_success'])) {
    $success = $_SESSION['account_link_success'];
    unset($_SESSION['account_link_success']);
}
if (isset($_SESSION['account_error'])) {
    $error = $_SESSION['account_error'];
    unset($_SESSION['account_error']);
}

$discord = new DiscordOAuth($db);
$github = new GitHubOAuth($db);
$google = new GoogleOAuth($db);
$slack = new SlackOAuth($db);

// Check if integrations are configured
$discordConfigured = $discord->isConfigured();
$githubConfigured = $github->isConfigured();
$googleConfigured = $google->isConfigured();
$slackConfigured = $slack->isConfigured();

// Count enabled integrations for responsive grid
$enabledIntegrations = 0;
if ($discordConfigured) $enabledIntegrations++;
if ($githubConfigured) $enabledIntegrations++;
if ($googleConfigured) $enabledIntegrations++;
if ($slackConfigured) $enabledIntegrations++;

// Set grid classes based on number of enabled integrations
$gridClasses = match ($enabledIntegrations) {
    1 => 'grid grid-cols-1',
    2 => 'grid grid-cols-1 sm:grid-cols-2',
    3 => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4 => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    default => 'grid grid-cols-1'
};

$discordLink = $discord->getUserDiscordLink($currentUser->id);
$githubLink = $github->getUserGitHubLink($currentUser->id);
$googleLink = $google->getUserGoogleLink($currentUser->id);
$slackLink = $slack->getUserSlackLink($currentUser->id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unlink_discord'])) {
        $discord->unlinkDiscordAccount($currentUser->id);
        $success = "Discord account unlinked successfully!";
        $discordLink = null;
    } elseif (isset($_POST['unlink_github'])) {
        $github->unlinkGitHubAccount($currentUser->id);
        $success = "GitHub account unlinked successfully!";
        $githubLink = null;
    } elseif (isset($_POST['unlink_google'])) {
        $google->unlinkGoogleAccount($currentUser->id);
        $success = "Google account unlinked successfully!";
        $googleLink = null;
    } elseif (isset($_POST['unlink_slack'])) {
        $slack->unlinkSlackAccount($currentUser->id);
        $success = "Slack account unlinked successfully!";
        $slackLink = null;
    } else {
        $newFirst = trim($_POST['first_name'] ?? '');
        $newLast = trim($_POST['last_name'] ?? '');
        $newDesc = trim($_POST['description'] ?? '');
        $newSchool = trim($_POST['school'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');
        $newBio = trim($_POST['bio'] ?? '');
        $profilePublic = isset($_POST['profile_public']) ? 1 : 0;

        $updateErrors = [];
        if ($newFirst === '') $updateErrors[] = "First name cannot be empty.";
        if ($newLast === '') $updateErrors[] = "Last name cannot be empty.";

        // Handle profile picture upload
        $profileImageFilename = $currentUser->profile_image ?? null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../images/members/';

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0775, true)) {
                    $updateErrors[] = "Failed to create upload directory.";
                }
            }

            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                $updateErrors[] = "Upload directory is not writable. Please contact an administrator.";
            }

            if (empty($updateErrors)) {
                $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($fileExtension, $allowedExtensions)) {
                    $updateErrors[] = "Invalid file type. Allowed: " . implode(', ', $allowedExtensions);
                } elseif ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                    $updateErrors[] = "File size must be less than 5MB.";
                } else {
                    // Delete old profile image if exists
                    if ($profileImageFilename && file_exists($uploadDir . $profileImageFilename)) {
                        @unlink($uploadDir . $profileImageFilename);
                    }

                    // Generate unique filename
                    $profileImageFilename = 'profile_' . $currentUser->id . '_' . time() . '.' . $fileExtension;

                    $targetPath = $uploadDir . $profileImageFilename;

                    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                        $updateErrors[] = "Failed to upload profile picture. Please contact an administrator.";
                        $profileImageFilename = $currentUser->profile_image ?? null;
                    }
                }
            }
        }

        if (empty($updateErrors)) {
            $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, description = ?, school = ?, phone = ?, bio = ?, profile_image = ?, profile_public = ? WHERE id = ?");
            $result = $stmt->execute([$newFirst, $newLast, $newDesc, $newSchool, $newPhone, $newBio, $profileImageFilename, $profilePublic, $currentUser->id]);

            if ($result) {
                // Reload user data from database
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$currentUser->id]);
                $updatedUser = $stmt->fetch(PDO::FETCH_OBJ);

                // Update the global $currentUser variable
                if ($updatedUser) {
                    $currentUser = $updatedUser;
                }

                $successMsg = "Profile updated successfully!";
                if (!empty($profileImageFilename)) {
                    $successMsg .= " Profile picture uploaded.";
                }
                $success = $successMsg;
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        } else {
            $error = implode('<br>', $updateErrors);
        }
    }
}

$pageTitle = 'Edit Profile';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Profile</h2>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Update your personal information and preferences</p>
            </div>
        </div>
    </div>
    <?php if ($success): ?>
        <div class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-700 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700 dark:text-green-300"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-300"><?= $error ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Personal Information</h3>
        </div>

        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">

            <!-- Profile Picture Section -->
            <div class="border-b border-gray-200 dark:border-gray-600 pb-6">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Profile Picture</h4>
                <div class="flex items-center space-x-6">
                    <div class="shrink-0">
                        <?php if (!empty($currentUser->profile_image)): ?>
                            <img src="<?= $settings['site_url'] ?>/images/members/<?= htmlspecialchars($currentUser->profile_image) ?>"
                                alt="Profile"
                                class="h-24 w-24 object-cover rounded-full border-2 border-gray-300 dark:border-gray-600">
                        <?php else: ?>
                            <div class="h-24 w-24 rounded-full bg-gradient-to-br from-primary to-red-600 flex items-center justify-center text-white text-3xl font-bold border-2 border-gray-300 dark:border-gray-600">
                                <?= strtoupper(substr($currentUser->first_name, 0, 1) . substr($currentUser->last_name, 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <label class="block">
                            <span class="sr-only">Choose profile photo</span>
                            <input type="file"
                                name="profile_image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-primary file:text-white
                                          hover:file:bg-red-600
                                          file:cursor-pointer cursor-pointer">
                        </label>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, GIF or WEBP. Max 5MB.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name *</label>
                    <input type="text"
                        id="first_name"
                        name="first_name"
                        value="<?= htmlspecialchars($currentUser->first_name ?? '') ?>"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name *</label>
                    <input type="text"
                        id="last_name"
                        name="last_name"
                        value="<?= htmlspecialchars($currentUser->last_name ?? '') ?>"
                        required
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <select name="country_code" id="country_code" class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm focus:ring-primary focus:border-primary">
                            <option value="+1">🇺🇸 +1</option>
                            <option value="+44">🇬🇧 +44</option>
                            <option value="+91">🇮🇳 +91</option>
                            <option value="+86">🇨🇳 +86</option>
                            <option value="+81">🇯🇵 +81</option>
                            <option value="+49">🇩🇪 +49</option>
                            <option value="+33">🇫🇷 +33</option>
                            <option value="+39">🇮🇹 +39</option>
                            <option value="+34">🇪🇸 +34</option>
                            <option value="+7">🇷🇺 +7</option>
                            <option value="+55">🇧🇷 +55</option>
                            <option value="+61">🇦🇺 +61</option>
                            <option value="+27">🇿🇦 +27</option>
                            <option value="+82">🇰🇷 +82</option>
                            <option value="+52">🇲🇽 +52</option>
                            <option value="+31">🇳🇱 +31</option>
                            <option value="+46">🇸🇪 +46</option>
                            <option value="+41">🇨🇭 +41</option>
                            <option value="+65">🇸🇬 +65</option>
                            <option value="+971">🇦🇪 +971</option>
                        </select>
                        <input type="tel"
                            id="phone"
                            name="phone"
                            value="<?= htmlspecialchars($currentUser->phone ?? '') ?>"
                            placeholder="123-456-7890"
                            class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 dark:border-gray-600 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div>
                    <label for="school" class="block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                    <input type="text"
                        id="school"
                        name="school"
                        value="<?= htmlspecialchars($currentUser->school ?? '') ?>"
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            </div>
    </div>
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">About Me</label>
        <textarea id="description"
            name="description"
            rows="4"
            placeholder="Tell us about yourself, your interests, skills, and what you'd like to achieve..."
            class="mt-1 block w-full border-2 border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary focus:border-2 dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($currentUser->description ?? '') ?></textarea>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This information will be visible to other members and can help with project matching.</p>
    </div>

    <!-- Public Profile Section -->
    <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Public Profile Settings</h4>

        <div class="mb-4">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox"
                    name="profile_public"
                    value="1"
                    <?= ($currentUser->profile_public ?? 1) ? 'checked' : '' ?>
                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show my profile on the public members page</span>
            </label>
            <p class="ml-6 mt-1 text-xs text-gray-500 dark:text-gray-400">When enabled, your profile will be visible on the team members page</p>
        </div>

        <div>
            <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Public Bio / Member Card Message</label>
            <textarea id="bio"
                name="bio"
                rows="3"
                maxlength="200"
                placeholder="A short message that will appear on your member card (max 200 characters)..."
                class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($currentUser->bio ?? '') ?></textarea>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span id="bioCharCount"><?= strlen($currentUser->bio ?? '') ?></span>/200 characters
            </p>
        </div>
    </div>

    <!-- Save Button for Profile Form -->
    <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-600">
        <button type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            Save Changes
        </button>
    </div>
    </form>
</div>

<!-- Account Linking Section (Separate from Profile Form) -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow mt-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Linked Accounts</h3>
    </div>
    <div class="p-6">
        <?php if ($enabledIntegrations > 0): ?>
            <div class="<?= $gridClasses ?> gap-4">
                <!-- Only show enabled integrations -->

                <?php if ($discordConfigured): ?>
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z" />
                            </svg>
                            <div>
                                <p class="text-xs font-medium text-gray-900 dark:text-white">Discord</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?php if ($discordLink): ?>
                                        <?= htmlspecialchars($discordLink['discord_username']) ?>
                                    <?php else: ?>
                                        Not linked
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-1">
                            <?php if ($discordLink): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="unlink_discord" value="1">
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Discord">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?= $settings['site_url'] ?>/auth/discord/?action=link" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" title="Link Discord">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($githubConfigured): ?>
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                            <div>
                                <p class="text-xs font-medium text-gray-900 dark:text-white">GitHub</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?php if ($githubLink): ?>
                                        <?= htmlspecialchars($githubLink['github_username']) ?>
                                    <?php else: ?>
                                        Not linked
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-1">
                            <?php if ($githubLink): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="unlink_github" value="1">
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink GitHub">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?= $settings['site_url'] ?>/auth/github/?action=link" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300" title="Link GitHub">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($googleConfigured): ?>
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                            </svg>
                            <div>
                                <p class="text-xs font-medium text-gray-900 dark:text-white">Google</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?php if ($googleLink): ?>
                                        <?= htmlspecialchars($googleLink['google_email']) ?>
                                    <?php else: ?>
                                        Not linked
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-1">
                            <?php if ($googleLink): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="unlink_google" value="1">
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Google">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?= $settings['site_url'] ?>/auth/google/?action=link" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Link Google">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($slackConfigured): ?>
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#E01E5A" d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52z" />
                                <path fill="#E01E5A" d="M6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313z" />
                                <path fill="#36C5F0" d="M8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834z" />
                                <path fill="#36C5F0" d="M8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312z" />
                                <path fill="#2EB67D" d="M18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834z" />
                                <path fill="#2EB67D" d="M17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312z" />
                                <path fill="#ECB22E" d="M15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52z" />
                                <path fill="#ECB22E" d="M15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z" />
                            </svg>
                            <div>
                                <p class="text-xs font-medium text-gray-900 dark:text-white">Slack</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?php if ($slackLink): ?>
                                        <?= htmlspecialchars($slackLink['slack_username']) ?>
                                    <?php else: ?>
                                        Not linked
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-1">
                            <?php if ($slackLink): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="unlink_slack" value="1">
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Unlink Slack">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?= $settings['site_url'] ?>/auth/slack/?action=link" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300" title="Link Slack">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">No integrations are currently configured.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mainForm = document.querySelector('form.p-6.space-y-6');
        const saveButton = mainForm ? mainForm.querySelector('button[type="submit"]') : null;

        // Form submission feedback
        if (mainForm && saveButton) {
            mainForm.addEventListener('submit', function(e) {
                saveButton.textContent = 'Saving...';
                saveButton.disabled = true;
            });
        }

        // Bio character counter
        const bioTextarea = document.getElementById('bio');
        const charCount = document.getElementById('bioCharCount');

        if (bioTextarea && charCount) {
            bioTextarea.addEventListener('input', function() {
                const length = this.value.length;
                charCount.textContent = length;

                // Visual feedback for character limit
                if (length > 200) {
                    charCount.classList.add('text-red-600', 'font-bold');
                    this.value = this.value.substring(0, 200);
                } else if (length > 180) {
                    charCount.classList.add('text-yellow-600');
                    charCount.classList.remove('text-red-600', 'font-bold');
                } else {
                    charCount.classList.remove('text-red-600', 'text-yellow-600', 'font-bold');
                }
            });
        }

        // Profile image preview
        const profileImageInput = document.querySelector('input[name="profile_image"]');
        if (profileImageInput) {
            profileImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        this.value = '';
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.querySelector('img[alt="Profile"], .h-24.w-24.rounded-full');
                        if (img) {
                            if (img.tagName === 'IMG') {
                                img.src = e.target.result;
                            } else {
                                // Replace div with img
                                const newImg = document.createElement('img');
                                newImg.src = e.target.result;
                                newImg.alt = 'Profile';
                                newImg.className = 'h-24 w-24 object-cover rounded-full border-2 border-gray-300 dark:border-gray-600';
                                img.replaceWith(newImg);
                            }
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>