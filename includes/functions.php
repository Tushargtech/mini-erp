<?php

date_default_timezone_set('Asia/Kolkata');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('',1);
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>