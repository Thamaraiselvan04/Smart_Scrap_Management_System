<?php
// includes/config.php

// Define Admin Godown Coordinates
define('ADMIN_LAT_SERVER', 12.935160);
define('ADMIN_LON_SERVER', 80.096417);
define('ADMIN_PIN', '600045');

// Maximum distance allowed for service (in kilometers)
define('MAX_DISTANCE_KM', 55);

// OpenCage API Key
define('OPENCAGE_API_KEY', '65c22dc1e71847bab95da587d2060c24');

// You can add other global configurations here if needed
// For example, SMTP details if not using PHPMailer directly:
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'removed');
define('SMTP_PASSWORD', 'removed');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
?>