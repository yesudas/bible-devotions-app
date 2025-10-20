<?php
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (stripos($userAgent, 'Android') !== false) {
    $device = 'Android';
    $installAsAppButton = false;
} elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
    $device = 'iOS';
    $installAsAppButton = true;
} else {
    $device = 'Unknown';
    $installAsAppButton = true;
}

?>
