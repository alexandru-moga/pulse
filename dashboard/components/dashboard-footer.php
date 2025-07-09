                </div>
            </main>
        </div>
    </div>

    <script>        
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
</body>
</html>
