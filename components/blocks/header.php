<?php
// Default values
$logo_text = $logo_text ?? htmlspecialchars($settings['site_title'] ?? 'Site');
$show_navigation = $show_navigation ?? true;

// Include the existing header component
include __DIR__ . '/../layout/header.php';
