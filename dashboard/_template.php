<?php
/**
 * Dashboard Page Template
 * 
 * Use this template to convert any dashboard page to the new layout.
 * Replace the content between the header and footer includes with your page content.
 */

require_once __DIR__ . '/../core/init.php';

$pageTitle = 'Your Page Title Here';

include __DIR__ . '/components/dashboard-header.php';
?>

<!-- 
  Your page content goes here using Tailwind CSS classes
  
  Common patterns:
  
  1. Page with stats and cards:
-->
<div class="space-y-6">
    <!-- Page Header Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Page Section Title</h2>
                <p class="text-gray-600 mt-1">Description of this section</p>
            </div>
            <div>
                <!-- Action buttons -->
                <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Action Button
                </a>
            </div>
        </div>
    </div>

    <!-- Content Cards -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Content Section</h3>
        </div>
        <div class="p-6">
            <!-- Your content here -->
            <p class="text-gray-600">Replace this with your actual content...</p>
        </div>
    </div>

    <!-- Grid Layout Example -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-medium text-gray-900">Card 1</h4>
            <p class="text-gray-600 mt-2">Card content...</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-medium text-gray-900">Card 2</h4>
            <p class="text-gray-600 mt-2">Card content...</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-medium text-gray-900">Card 3</h4>
            <p class="text-gray-600 mt-2">Card content...</p>
        </div>
    </div>
</div>

<!-- 
  2. Form Page Example:
  
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Form Title</h3>
        </div>
        <div class="p-6">
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="field" class="block text-sm font-medium text-gray-700">Field Label</label>
                    <input type="text" id="field" name="field" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

  3. Table Page Example:
  
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Data Table</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Column 1</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Column 2</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Data 1</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Data 2</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <button class="text-primary hover:text-red-600">Edit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

  Common Tailwind CSS Classes for Dashboard:
  
  - Containers: bg-white rounded-lg shadow p-6
  - Buttons: bg-primary hover:bg-red-600 text-white px-4 py-2 rounded-md
  - Form inputs: border border-gray-300 rounded-md shadow-sm py-2 px-3
  - Grid layouts: grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6
  - Text colors: text-gray-900 (dark), text-gray-600 (medium), text-gray-500 (light)
  - Spacing: space-y-6 (vertical), space-x-4 (horizontal)
-->

<?php
include __DIR__ . '/components/dashboard-footer.php';
?>
