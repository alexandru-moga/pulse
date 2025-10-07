<?php
require_once __DIR__ . '/../core/init.php';
checkActiveOrLimitedAccess();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Profile Update Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-xl font-bold mb-4">Profile Update Test</h1>

        <form method="POST" action="test-profile-update.php" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">First Name:</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($currentUser->first_name ?? '') ?>"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Last Name:</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($currentUser->last_name ?? '') ?>"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description:</label>
                <textarea name="description" rows="3"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"><?= htmlspecialchars($currentUser->description ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">School:</label>
                <input type="text" name="school" value="<?= htmlspecialchars($currentUser->school ?? '') ?>"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Phone:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($currentUser->phone ?? '') ?>"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                Test Profile Update
            </button>
        </form>

        <div class="mt-4 p-4 bg-gray-50 rounded">
            <h2 class="font-semibold">Current User Data:</h2>
            <pre class="text-xs"><?= json_encode($currentUser, JSON_PRETTY_PRINT) ?></pre>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form submitted');

            // Show the form data being sent
            const formData = new FormData(this);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            console.log('Form data:', data);
        });
    </script>
</body>

</html>