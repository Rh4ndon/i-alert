<?php
include '../models/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    if ($userType == 'customer') {
        $user = getRecord('user', 'userEmail = "' . $email . '"');
        if ($user && password_verify($password, $user['userPassword'])) {

            session_start();
            $_SESSION['userId'] = $user['userId'];
            $_SESSION['userFirstName'] = $user['userFirstName'];
            $_SESSION['userLastName'] = $user['userLastName'];
            $_SESSION['userEmail'] = $user['userEmail'];
            $_SESSION['userPhone'] = $user['userPhone'];
            $_SESSION['userAddress'] = $user['userAddress'];
            $_SESSION['userLat'] = $user['userLat'];
            $_SESSION['userLong'] = $user['userLong'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['login_time'] = time();
            // save data to localStorage

            // Json response
            $response = [
                'success' => true,
                'userId' => $user['userId'],
                'userFirstName' => $user['userFirstName'],
                'userLastName' => $user['userLastName'],
                'userEmail' => $user['userEmail'],
                'userPhone' => $user['userPhone'],
                'userAddress' => $user['userAddress'],
                'userLat' => $user['userLat'],
                'userLong' => $user['userLong'],
                'is_logged_in' => true,
                'login_time' => time()

            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (($user && !password_verify($password, $user['userPassword']))) {
            $response = [
                'error' => 'Invalid password.'
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (!$user) {
            $response = [
                'error' => 'Invalid email'
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    } else if ($userType == 'rider') {
        $rider = getRecord('rider', 'riderEmail = "' . $email . '"');
        if ($rider && password_verify($password, $rider['riderPassword'])) {

            session_start();
            $_SESSION['riderId'] = $rider['riderId'];
            $_SESSION['riderFirstName'] = $rider['riderFirstName'];
            $_SESSION['riderLastName'] = $rider['riderLastName'];
            $_SESSION['riderEmail'] = $rider['riderEmail'];
            $_SESSION['riderPhone'] = $rider['riderPhone'];
            $_SESSION['riderAddress'] = $rider['riderAddress'];
            $_SESSION['vehicleType'] = $rider['vehicleType'];
            $_SESSION['riderLat'] = $rider['riderCurrentLat'];
            $_SESSION['riderLong'] = $rider['riderCurrentLong'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['login_time'] = time();
            // save data to localStorage

            // Json response
            $response = [
                'success' => true,
                'riderId' => $rider['riderId'],
                'riderFirstName' => $rider['riderFirstName'],
                'riderLastName' => $rider['riderLastName'],
                'riderEmail' => $rider['riderEmail'],
                'riderPhone' => $rider['riderPhone'],
                'riderAddress' => $rider['riderAddress'],
                'vehicleType' => $rider['vehicleType'],
                'riderLat' => $rider['riderCurrentLat'],
                'riderLong' => $rider['riderCurrentLong'],
                'is_logged_in' => true,
                'login_time' => time()
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (($rider && !password_verify($password, $rider['password']))) {
            $response = [
                'error' => 'Invalid password.'
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (!$rider) {
            $response = [
                'error' => 'Invalid email'
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    } else if ($userType == 'admin') {
        $admin = getRecord('admin', 'adminEmail = "' . $email . '"');
        if ($admin && password_verify($password, $admin['adminPassword'])) {

            session_start();
            $_SESSION['adminId'] = $admin['adminId'];
            $_SESSION['adminFirstName'] = $admin['adminFirstName'];
            $_SESSION['adminLastName'] = $admin['adminLastName'];
            $_SESSION['adminEmail'] = $admin['adminEmail'];
            $_SESSION['adminPhone'] = $admin['adminPhone'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['login_time'] = time();

            // Json response
            $response = [
                'success' => true,
                'adminId' => $admin['adminId'],
                'adminFirstName' => $admin['adminFirstName'],
                'adminLastName' => $admin['adminLastName'],
                'adminEmail' => $admin['adminEmail'],
                'adminPhone' => $admin['adminPhone'],
                'is_logged_in' => true,
                'login_time' => time()
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (($admin && !password_verify($password, $admin['password']))) {
            $response = [
                'error' => 'Invalid password.'
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (!$admin) {
            $response = [
                'error' => 'Invalid email'
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    }
}
