<?php
session_start();
include '../models/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'];
    $userType = $_POST['userType'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];


    if ($userType == 'user') {
        $user = getRecord('users', 'id = "' . $userId . '"');
        if ($user) {

            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;

            $password = password_hash($password, PASSWORD_BCRYPT);

            $data = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password_hash' => $password,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = updateRecord('users', $data, 'id = ' . $userId);
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email
                ];
                // Set proper header and return JSON response
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            } else {
                $response = [
                    'error' => 'Failed to update profile'
                ];
                // Set proper header and return JSON response
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }
        } else if (!$user) {
            $response = [
                'error' => 'Invalid ID'
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    } else {
        $user = getRecord('department_users', 'id = "' . $userId . '"');
        if ($user) {

            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;

            $password = password_hash($password, PASSWORD_BCRYPT);

            $data = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password_hash' => $password,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = updateRecord('department_users', $data, 'id = ' . $userId);
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email
                ];
                // Set proper header and return JSON response
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            } else {
                $response = [
                    'error' => 'Failed to update profile'
                ];
                // Set proper header and return JSON response
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }
        } else if (!$user) {
            $response = [
                'error' => 'Invalid ID'
            ];
            // Set proper header and return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    }
}
