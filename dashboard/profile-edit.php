<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();

global $db, $currentUser, $settings;

$success = $error = null;

// Check for update success parameter
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $success = "Profile updated successfully!";
}

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
        // Handle profile update - simple approach like site-settings.php
        $newFirst = trim($_POST['first_name'] ?? '');
        $newLast = trim($_POST['last_name'] ?? '');
        $newDesc = trim($_POST['description'] ?? '');
        $newBio = trim($_POST['bio'] ?? '');
        $newSchool = trim($_POST['school'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');
        $profilePublic = isset($_POST['profile_public']) ? 1 : 0;

        // Simple validation
        if ($newFirst === '' || $newLast === '') {
            $error = "First name and last name are required.";
        } else {
            // Handle profile image upload
            $profileImageName = $currentUser->profile_image ?? '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileInfo = pathinfo($_FILES['profile_image']['name']);
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $fileExt = strtolower($fileInfo['extension'] ?? '');

                if (in_array($fileExt, $allowedTypes) && $_FILES['profile_image']['size'] <= 5 * 1024 * 1024) {
                    $profileImageName = $currentUser->id . '_' . time() . '.' . $fileExt;
                    $uploadPath = $uploadDir . $profileImageName;

                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                        // Delete old profile image if it exists
                        if (!empty($currentUser->profile_image) && file_exists($uploadDir . $currentUser->profile_image)) {
                            unlink($uploadDir . $currentUser->profile_image);
                        }
                    }
                }
            }

            // Simple database update - exactly like site-settings.php pattern
            $stmt = $db->prepare("UPDATE users SET first_name=?, last_name=?, description=?, bio=?, school=?, phone=?, profile_image=?, profile_public=? WHERE id=?");
            $stmt->execute([$newFirst, $newLast, $newDesc, $newBio, $newSchool, $newPhone, $profileImageName, $profilePublic, $currentUser->id]);
            
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$currentUser->id]);
            $currentUser = $stmt->fetch(PDO::FETCH_OBJ);
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
                <?php if (!empty($currentUser->last_profile_update)): ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Last updated: <?= date('M j, Y \a\t g:i A', strtotime($currentUser->last_profile_update)) ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                    <div id="profile-status" class="flex items-center">
                        <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                        <span>Ready to edit</span>
                    </div>
                </div>
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
            <!-- Profile Image Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Picture</label>
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <?php
                        $currentImage = '';
                        if (!empty($currentUser->profile_image)) {
                            $currentImage = $settings['site_url'] . '/uploads/profiles/' . $currentUser->profile_image;
                        } elseif (!empty($currentUser->discord_avatar)) {
                            $currentImage = "https://cdn.discordapp.com/avatars/{$currentUser->discord_id}/{$currentUser->discord_avatar}.png?size=128";
                        } else {
                            $currentImage = $settings['site_url'] . '/images/default-avatar.png';
                        }
                        ?>
                        <img class="h-20 w-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600" 
                             src="<?= htmlspecialchars($currentImage) ?>" 
                             alt="Profile picture">
                    </div>
                    <div class="flex-1">
                        <label for="profile_image" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-primary hover:text-red-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary border border-gray-300 dark:border-gray-600 px-3 py-2 inline-block">
                            <span>Change picture</span>
                            <input id="profile_image" name="profile_image" type="file" class="sr-only" accept="image/*">
                        </label>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF, or WebP up to 5MB</p>
                    </div>
                </div>
            </div>

            <!-- Personal Information Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                    <input type="text" name="first_name" id="first_name" required
                        value="<?= htmlspecialchars($currentUser->first_name ?? '') ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required
                        value="<?= htmlspecialchars($currentUser->last_name ?? '') ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"
                    placeholder="Tell us about yourself..."><?= htmlspecialchars($currentUser->description ?? '') ?></textarea>
            </div>

            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Short Bio</label>
                <textarea name="bio" id="bio" rows="2"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white"
                    placeholder="A short bio for your member card..."><?= htmlspecialchars($currentUser->bio ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="school" class="block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                    <input type="text" name="school" id="school"
                        value="<?= htmlspecialchars($currentUser->school ?? '') ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                    <input type="tel" name="phone" id="phone"
                        value="<?= htmlspecialchars($currentUser->phone ?? '') ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="profile_public" id="profile_public" value="1"
                    <?= (!empty($currentUser->profile_public)) ? 'checked' : '' ?>
                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                <label for="profile_public" class="ml-2 block text-sm text-gray-900 dark:text-white">
                    Make my profile visible on the public members page
                </label>
            </div>

            <!-- Account Integrations -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Account Integrations</h4>
                <div class="space-y-4">
                    <?php if ($discordConfigured): ?>
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419-.0002 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9554 2.4189-2.1568 2.4189Z"/>
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
                </div>
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="<?= $settings['site_url'] ?>/dashboard/change-password.php"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Change Password
                </a>

                <div class="flex space-x-4">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Simple profile image preview functionality only
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
            e.target.value = '';
            return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            e.target.value = '';
            return;
        }

        // Preview the image
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.querySelector('.h-20.w-20.rounded-full');
            if (img) {
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>