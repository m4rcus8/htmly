<?php
require 'system/includes/dispatch.php';
require 'system/includes/session.php';

// Load the configuration file
config('source', 'config/config.ini');

// Set the timezone
date_default_timezone_set(config('timezone', 'Asia/Jakarta'));

$whitelist = array('jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'gif');
$dir       = 'content/images/';
$error     = null;
$timestamp = date('YmdHis');
$path      = null;

if (login()) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (isset($_FILES['file'])) {
        $tmp_name = $_FILES['file']['tmp_name'];
        $name     = basename($_FILES['file']['name']);
        $error    = $_FILES['file']['error'];
        $path     = $dir . $timestamp . '-' . $name;
	
        $check = getimagesize($tmp_name);
	
        if ($check !== false) {
            if ($error === UPLOAD_ERR_OK) {
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                if (!in_array(strtolower($extension), $whitelist)) {
                    $error = 'Invalid file type uploaded.';
                } else {
                    if (move_uploaded_file($tmp_name, $path)) {
                        $path = $timestamp . '-' . $name; // Update path after successful move
                    } else {
                        $error = 'Failed to move uploaded file.';
                    }
                }
            } else {
                $error = 'Upload error occurred.';
            }
        } else {
            $error = "File is not an image.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode(array(
        'path'  => $path,
        'name'  => $name,
        'error' => $error,
    ));
	
    die();

} else {
    $login = site_url() . 'login';
    header("Location: $login");
}
