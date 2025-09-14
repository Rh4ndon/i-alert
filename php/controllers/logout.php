<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

echo "<script>
        localStorage.clear();

        if (typeof AndroidBridge !== 'undefined') {
                AndroidBridge.clearUserType();
                }

        window.location.href = '../index.html?msg=You have been logged out successfully.';
        </script>";
