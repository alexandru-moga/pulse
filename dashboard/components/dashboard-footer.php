</div>
</main>
</div>
</div>

<script>
    // Dark mode functionality
    const darkModeToggle = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;

    // Check for saved dark mode preference or default to light mode
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        htmlElement.classList.add('dark');
    }

    // Apply dark mode classes immediately
    function applyDarkModeClasses() {
        // Apply to all content elements
        const elements = document.querySelectorAll(`
                .bg-white, .bg-gray-50, .bg-gray-100, .bg-green-50, .bg-red-50, 
                .bg-yellow-50, .bg-blue-50, .text-gray-900, .text-gray-600, 
                .text-gray-500, .border-gray-200, .border-green-200, .border-red-200,
                .border-yellow-200, .border-blue-200
            `);

        elements.forEach(el => {
            // Background colors
            if (el.classList.contains('bg-white')) {
                el.classList.add('dark:bg-gray-800');
            }
            if (el.classList.contains('bg-gray-50')) {
                el.classList.add('dark:bg-gray-900');
            }
            if (el.classList.contains('bg-gray-100')) {
                el.classList.add('dark:bg-gray-800');
            }
            if (el.classList.contains('bg-green-50')) {
                el.classList.add('dark:bg-green-900');
            }
            if (el.classList.contains('bg-red-50')) {
                el.classList.add('dark:bg-red-900');
            }
            if (el.classList.contains('bg-yellow-50')) {
                el.classList.add('dark:bg-yellow-900');
            }
            if (el.classList.contains('bg-blue-50')) {
                el.classList.add('dark:bg-blue-900');
            }

            // Text colors
            if (el.classList.contains('text-gray-900')) {
                el.classList.add('dark:text-white');
            }
            if (el.classList.contains('text-gray-600')) {
                el.classList.add('dark:text-gray-300');
            }
            if (el.classList.contains('text-gray-500')) {
                el.classList.add('dark:text-gray-400');
            }

            // Border colors
            if (el.classList.contains('border-gray-200')) {
                el.classList.add('dark:border-gray-700');
            }
            if (el.classList.contains('border-green-200')) {
                el.classList.add('dark:border-green-700');
            }
            if (el.classList.contains('border-red-200')) {
                el.classList.add('dark:border-red-700');
            }
            if (el.classList.contains('border-yellow-200')) {
                el.classList.add('dark:border-yellow-700');
            }
            if (el.classList.contains('border-blue-200')) {
                el.classList.add('dark:border-blue-700');
            }
        });
    }

    // Apply classes on page load
    document.addEventListener('DOMContentLoaded', applyDarkModeClasses);

    darkModeToggle.addEventListener('click', () => {
        if (htmlElement.classList.contains('dark')) {
            htmlElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            htmlElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    });

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            if (e.matches) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
        }
    });

    // Existing code for notices and animations
    document.addEventListener('DOMContentLoaded', function() {
        const notices = document.querySelectorAll('.ysws-notice, .success-notice, .error-notice');
        notices.forEach(notice => {
            setTimeout(() => {
                notice.style.opacity = '0';
                setTimeout(() => notice.remove(), 300);
            }, 5000);
        });
    });

    const sidebarLinks = document.querySelectorAll('nav a');
    sidebarLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            if (!this.classList.contains('bg-primary')) {
                this.style.transform = 'translateX(4px)';
            }
        });
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
</script>

<!-- Cookie Consent -->
<?php include __DIR__ . '/../../components/cookie-consent.php'; ?>
</body>

</html>