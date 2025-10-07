<?php
require_once __DIR__ . '/../core/init.php';
checkActiveOrLimitedAccess();
?>
<!DOCTYPE html>
<html>
<head>
    <title>POST Test Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-xl font-bold mb-4">POST Test Form</h1>
        
        <form method="POST" action="test-profile-save.php" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Test Field:</label>
                <input type="text" name="test_field" value="test_value" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>
            
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                Test POST Request
            </button>
        </form>
        
        <div class="mt-4 p-4 bg-gray-50 rounded">
            <h2 class="font-semibold">Instructions:</h2>
            <p class="text-sm text-gray-600">Click the button above to test if POST requests work. You should see POST data in the response.</p>
        </div>
    </div>

    <script>
        // Log any JavaScript errors
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            console.log('JavaScript Error: ', msg, 'at', url, ':', lineNo);
            return false;
        };
        
        // Log form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form submitted');
        });
    </script>
</body>
</html>