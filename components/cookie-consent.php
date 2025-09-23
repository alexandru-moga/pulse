<?php

/**
 * Cookie Consent Component
 * GDPR-compliant cookie consent management
 */
?>

<!-- Cookie Consent Banner -->
<div id="cookieConsent" class="cookie-consent" style="display: none;">
    <div class="cookie-consent-content">
        <div class="cookie-consent-text">
            <h3>🍪 We use cookies</h3>
            <p>We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.</p>
        </div>
        <div class="cookie-consent-buttons">
            <button id="cookieAcceptAll" class="cookie-btn cookie-btn-accept">Accept All</button>
            <button id="cookieCustomize" class="cookie-btn cookie-btn-secondary">Customize</button>
            <button id="cookieReject" class="cookie-btn cookie-btn-reject">Reject All</button>
        </div>
    </div>
</div>

<!-- Cookie Preferences Modal -->
<div id="cookieModal" class="cookie-modal" style="display: none;">
    <div class="cookie-modal-overlay"></div>
    <div class="cookie-modal-content">
        <div class="cookie-modal-header">
            <h2>Cookie Preferences</h2>
            <button id="cookieModalClose" class="cookie-modal-close">&times;</button>
        </div>
        <div class="cookie-modal-body">
            <p>We use cookies to improve your experience on our website. You can choose which categories of cookies to allow:</p>

            <div class="cookie-category">
                <div class="cookie-category-header">
                    <h4>Essential Cookies</h4>
                    <label class="cookie-toggle">
                        <input type="checkbox" id="essential-cookies" checked disabled>
                        <span class="cookie-slider"></span>
                    </label>
                </div>
                <p>These cookies are necessary for the website to function and cannot be switched off. They are usually set in response to actions you take, such as logging in or filling out forms.</p>
            </div>

            <div class="cookie-category">
                <div class="cookie-category-header">
                    <h4>Analytics Cookies</h4>
                    <label class="cookie-toggle">
                        <input type="checkbox" id="analytics-cookies">
                        <span class="cookie-slider"></span>
                    </label>
                </div>
                <p>These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously.</p>
            </div>

            <div class="cookie-category">
                <div class="cookie-category-header">
                    <h4>Functional Cookies</h4>
                    <label class="cookie-toggle">
                        <input type="checkbox" id="functional-cookies">
                        <span class="cookie-slider"></span>
                    </label>
                </div>
                <p>These cookies enable enhanced functionality and personalization, such as remembering your preferences and settings.</p>
            </div>

            <div class="cookie-category">
                <div class="cookie-category-header">
                    <h4>Marketing Cookies</h4>
                    <label class="cookie-toggle">
                        <input type="checkbox" id="marketing-cookies">
                        <span class="cookie-slider"></span>
                    </label>
                </div>
                <p>These cookies are used to make advertising messages more relevant to you and track the effectiveness of our marketing campaigns.</p>
            </div>
        </div>
        <div class="cookie-modal-footer">
            <button id="cookieSavePreferences" class="cookie-btn cookie-btn-accept">Save Preferences</button>
            <button id="cookieAcceptAllModal" class="cookie-btn cookie-btn-secondary">Accept All</button>
        </div>
    </div>
</div>

<!-- Cookie Settings Link (for footer) -->
<a href="#" id="cookieSettingsLink" class="cookie-settings-link">Cookie Settings</a>

<style>
    /* Cookie Consent Styles */
    .cookie-consent {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #1a1a1a;
        color: #ffffff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 400px;
        width: calc(100vw - 40px);
    }

    .cookie-consent-content {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .cookie-consent-text h3 {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
    }

    .cookie-consent-text p {
        margin: 0;
        font-size: 13px;
        line-height: 1.4;
        opacity: 0.9;
    }

    .cookie-consent-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cookie-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex: 1;
        min-width: 80px;
    }

    .cookie-btn-accept {
        background: #ef4444;
        color: white;
    }

    .cookie-btn-accept:hover {
        background: #dc2626;
    }

    .cookie-btn-secondary {
        background: transparent;
        color: #ffffff;
        border: 1px solid #ffffff;
    }

    .cookie-btn-secondary:hover {
        background: #ffffff;
        color: #1a1a1a;
    }

    .cookie-btn-reject {
        background: #6b7280;
        color: white;
    }

    .cookie-btn-reject:hover {
        background: #4b5563;
    }

    /* Cookie Modal Styles */
    .cookie-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .cookie-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
    }

    .cookie-modal-content {
        position: relative;
        background: white;
        border-radius: 12px;
        max-width: 600px;
        width: 100%;
        max-height: 80vh;
        overflow-y: auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .cookie-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 24px 0 24px;
    }

    .cookie-modal-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .cookie-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cookie-modal-close:hover {
        color: #1a1a1a;
    }

    .cookie-modal-body {
        padding: 24px;
    }

    .cookie-modal-body p {
        margin: 0 0 24px 0;
        color: #4b5563;
        line-height: 1.6;
    }

    .cookie-category {
        margin-bottom: 24px;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .cookie-category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .cookie-category h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .cookie-category p {
        margin: 0;
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
    }

    /* Toggle Switch */
    .cookie-toggle {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .cookie-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .cookie-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .cookie-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.cookie-slider {
        background-color: #ef4444;
    }

    input:checked+.cookie-slider:before {
        transform: translateX(26px);
    }

    input:disabled+.cookie-slider {
        background-color: #ef4444;
        opacity: 0.6;
        cursor: not-allowed;
    }

    .cookie-modal-footer {
        padding: 0 24px 24px 24px;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    /* Cookie Settings Link */
    .cookie-settings-link {
        color: inherit;
        text-decoration: none;
        font-size: 14px;
        opacity: 0.7;
        transition: opacity 0.2s ease;
    }

    .cookie-settings-link:hover {
        opacity: 1;
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .cookie-consent {
            bottom: 10px;
            right: 10px;
            left: 10px;
            max-width: none;
            width: auto;
        }

        .cookie-consent-content {
            gap: 12px;
        }

        .cookie-consent-text h3 {
            font-size: 14px;
        }

        .cookie-consent-text p {
            font-size: 12px;
        }

        .cookie-consent-buttons {
            gap: 6px;
        }

        .cookie-btn {
            padding: 6px 12px;
            font-size: 12px;
            min-width: 70px;
        }

        .cookie-modal-content {
            margin: 10px;
            max-height: 90vh;
        }

        .cookie-modal-footer {
            flex-direction: column;
        }

        .cookie-modal-footer .cookie-btn {
            width: 100%;
        }
    }
</style>

<script>
    // Cookie Consent Management
    (function() {
        'use strict';

        // Configuration
        const COOKIE_NAME = 'cookie_consent';
        const COOKIE_DURATION = 365; // days

        // Cookie utilities
        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
        }

        function getCookie(name) {
            const nameEQ = name + '=';
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function deleteCookie(name) {
            document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        }

        // Get DOM elements
        const consentBanner = document.getElementById('cookieConsent');
        const consentModal = document.getElementById('cookieModal');
        const acceptAllBtn = document.getElementById('cookieAcceptAll');
        const customizeBtn = document.getElementById('cookieCustomize');
        const rejectBtn = document.getElementById('cookieReject');
        const modalClose = document.getElementById('cookieModalClose');
        const savePrefsBtn = document.getElementById('cookieSavePreferences');
        const acceptAllModalBtn = document.getElementById('cookieAcceptAllModal');
        const settingsLink = document.getElementById('cookieSettingsLink');
        const modalOverlay = consentModal.querySelector('.cookie-modal-overlay');

        // Check if consent has been given
        function checkCookieConsent() {
            const consent = getCookie(COOKIE_NAME);
            if (!consent) {
                showConsentBanner();
            } else {
                applyCookieSettings(JSON.parse(consent));
            }
        }

        // Show consent banner
        function showConsentBanner() {
            consentBanner.style.display = 'block';
        }

        // Hide consent banner
        function hideConsentBanner() {
            consentBanner.style.display = 'none';
        }

        // Show preferences modal
        function showPreferencesModal() {
            consentModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Load current preferences
            const consent = getCookie(COOKIE_NAME);
            if (consent) {
                const preferences = JSON.parse(consent);
                document.getElementById('essential-cookies').checked = true; // Always true
                document.getElementById('analytics-cookies').checked = preferences.analytics || false;
                document.getElementById('functional-cookies').checked = preferences.functional || false;
                document.getElementById('marketing-cookies').checked = preferences.marketing || false;
            }
        }

        // Hide preferences modal
        function hidePreferencesModal() {
            consentModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Accept all cookies
        function acceptAllCookies() {
            const preferences = {
                essential: true,
                analytics: true,
                functional: true,
                marketing: true,
                timestamp: new Date().toISOString()
            };

            setCookie(COOKIE_NAME, JSON.stringify(preferences), COOKIE_DURATION);
            applyCookieSettings(preferences);
            hideConsentBanner();
            hidePreferencesModal();
        }

        // Reject all non-essential cookies
        function rejectAllCookies() {
            const preferences = {
                essential: true,
                analytics: false,
                functional: false,
                marketing: false,
                timestamp: new Date().toISOString()
            };

            setCookie(COOKIE_NAME, JSON.stringify(preferences), COOKIE_DURATION);
            applyCookieSettings(preferences);
            hideConsentBanner();
            hidePreferencesModal();
        }

        // Save custom preferences
        function saveCustomPreferences() {
            const preferences = {
                essential: true, // Always true
                analytics: document.getElementById('analytics-cookies').checked,
                functional: document.getElementById('functional-cookies').checked,
                marketing: document.getElementById('marketing-cookies').checked,
                timestamp: new Date().toISOString()
            };

            setCookie(COOKIE_NAME, JSON.stringify(preferences), COOKIE_DURATION);
            applyCookieSettings(preferences);
            hideConsentBanner();
            hidePreferencesModal();
        }

        // Apply cookie settings
        function applyCookieSettings(preferences) {
            // Remove non-essential cookies if not consented
            if (!preferences.analytics) {
                // Remove analytics cookies (Google Analytics, etc.)
                deleteCookie('_ga');
                deleteCookie('_ga_*');
                deleteCookie('_gid');
                deleteCookie('_gat');
            }

            if (!preferences.functional) {
                // Remove functional cookies
                deleteCookie('user_preferences');
                deleteCookie('theme');
            }

            if (!preferences.marketing) {
                // Remove marketing cookies
                deleteCookie('_fbp');
                deleteCookie('_fbc');
                deleteCookie('marketing_consent');
            }

            // Initialize allowed services
            if (preferences.analytics && typeof gtag !== 'undefined') {
                // Initialize Google Analytics if consented
                gtag('consent', 'update', {
                    'analytics_storage': 'granted'
                });
            }

            // Dispatch custom event for other scripts
            window.dispatchEvent(new CustomEvent('cookieConsentUpdated', {
                detail: preferences
            }));
        }

        // Event listeners
        if (acceptAllBtn) {
            acceptAllBtn.addEventListener('click', acceptAllCookies);
        }

        if (customizeBtn) {
            customizeBtn.addEventListener('click', showPreferencesModal);
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', rejectAllCookies);
        }

        if (modalClose) {
            modalClose.addEventListener('click', hidePreferencesModal);
        }

        if (savePrefsBtn) {
            savePrefsBtn.addEventListener('click', saveCustomPreferences);
        }

        if (acceptAllModalBtn) {
            acceptAllModalBtn.addEventListener('click', acceptAllCookies);
        }

        if (settingsLink) {
            settingsLink.addEventListener('click', function(e) {
                e.preventDefault();
                showPreferencesModal();
            });
        }

        if (modalOverlay) {
            modalOverlay.addEventListener('click', hidePreferencesModal);
        }

        // Escape key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && consentModal.style.display === 'flex') {
                hidePreferencesModal();
            }
        });

        // Initialize on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkCookieConsent);
        } else {
            checkCookieConsent();
        }
    })();
</script>