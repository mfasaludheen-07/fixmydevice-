<?php
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    echo "CONNECTED_SUCCESS";
} catch (Exception $e) {
    echo "CONNECTION_ERROR: " . $e->getMessage();
}
