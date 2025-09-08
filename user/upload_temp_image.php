<?php
session_start();

if (isset($_FILES['garbagephoto'])) {
    $file = $_FILES['garbagephoto'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($extension, $allowed)) {
        $newName = md5($file['name']) . time() . '.' . $extension;
        $destination = "images/temp/" . $newName;

        if (!file_exists("images/temp")) {
            mkdir("images/temp", 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode(['success' => true, 'filename' => $newName]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Upload failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid file type.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file received.']);
}
