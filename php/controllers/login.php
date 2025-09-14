<?php
session_start();
include '../models/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    if ($userType == 'user') {
        $user = getRecord('users', 'email = "' . $email . '"');
        if ($user && password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['is_active'] = 1;

            // Json response
            $response = [
                'success' => true,
                'user_id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'is_active' => 1,
                'userType' => 'user',
                'message' => 'Login successful'

            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (($user && !password_verify($password, $user['password_hash']))) {
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
    } else if ($userType == 'ndrrmo') {
        $user = getRecord('department_users', 'email = "' . $email . '"');
        if ($user && password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            $_SESSION['is_active'] = 1;

            // Json response
            $response = [
                'success' => true,
                'user_id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'userType' => 'ndrrmo',
                'is_active' => 1,
                'message' => 'Login successful'

            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (($user && !password_verify($password, $user['password_hash']))) {
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
    } else if ($userType == 'pnp') {
        $user = getRecord('department_users', 'email = "' . $email . '"');
        if ($user && password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            $_SESSION['is_active'] = 1;

            // Json response
            $response = [
                'success' => true,
                'user_id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'is_active' => 1,
                'userType' => 'pnp',
                'message' => 'Login successful'

            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (($user && !password_verify($password, $user['password_hash']))) {
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
    } else if ($userType == 'fire') {
        $user = getRecord('department_users', 'email = "' . $email . '"');
        if ($user && password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            $_SESSION['is_active'] = 1;

            // Json response
            $response = [
                'success' => true,
                'user_id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'is_active' => 1,
                'userType' => 'fire',
                'message' => 'Login successful'

            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (($user && !password_verify($password, $user['password_hash']))) {
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
    } else if ($userType == 'hospital') {
        $user = getRecord('department_users', 'email = "' . $email . '"');
        if ($user && password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            $_SESSION['is_active'] = 1;

            // Json response
            $response = [
                'success' => true,
                'user_id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'is_active' => 1,
                'userType' => 'hospital',
                'message' => 'Login successful'

            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else if (($user && !password_verify($password, $user['password_hash']))) {
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
    }
}
