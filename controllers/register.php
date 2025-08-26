    <?php
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Start output buffering to prevent any accidental output
    ob_start();

    require_once '../models/functions.php';

    // Insert a new customer
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Initialize response array
        $response = ['success' => false, 'error' => ''];

        try {
            if (isset($_POST['userType']) && $_POST['userType'] === 'customer') {

                // Validate required fields 
                $required_fields = ['userFirstName', 'userLastName', 'userEmail', 'userPhone', 'userAddress', 'userPassword', 'userLat', 'userLong'];
                foreach ($required_fields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Please fill in all required fields.");
                    }
                }

                // Sanitize input
                $userFirstName = filter_var(trim($_POST['userFirstName']), FILTER_SANITIZE_STRING);
                $userLastName = filter_var(trim($_POST['userLastName']), FILTER_SANITIZE_STRING);
                $userEmail = filter_var(trim($_POST['userEmail']), FILTER_SANITIZE_EMAIL);
                $userPhone = filter_var(trim($_POST['userPhone']), FILTER_SANITIZE_STRING);
                $userAddress = filter_var(trim($_POST['userAddress']), FILTER_SANITIZE_STRING);
                $userPassword = filter_var(trim($_POST['userPassword']), FILTER_SANITIZE_STRING);
                $userLat = filter_var(trim($_POST['userLat']), FILTER_SANITIZE_STRING);
                $userLong = filter_var(trim($_POST['userLong']), FILTER_SANITIZE_STRING);

                // Validate email format
                if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Please enter a valid email address.");
                }

                // Check if email already exists
                // You'll need to implement this check based on your database structure
                $existing_email = getAllRecords('user', "WHERE userEmail = '$userEmail'");
                if ($existing_email) {
                    throw new Exception("Email already exists. Please use a different email.");
                }
                $existing_name = getAllRecords('user', "WHERE userFirstName = '$userFirstName' AND userLastName = '$userLastName'");
                if ($existing_name) {
                    throw new Exception("Customer with this name already exists. Please use a different name.");
                }

                // Generate default password
                $password = password_hash($userPassword, PASSWORD_BCRYPT);

                $data = [
                    'userFirstName' => $userFirstName,
                    'userLastName' => $userLastName,
                    'userEmail' => $userEmail,
                    'userPhone' => $userPhone,
                    'userAddress' => $userAddress,
                    'userPassword' => $password,
                    'userLat' => $userLat,
                    'userLong' => $userLong,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'updatedAt  ' => date('Y-m-d H:i:s')
                ];

                // Insert the customer into the database
                $result = insertRecord('user', $data);

                if ($result) {
                    $response['success'] = true;
                } else {
                    $response['error'] = 'Failed to add customer to database.';
                }
            } else {

                // Validate required fields 
                $required_fields = ['riderFirstName', 'riderLastName', 'riderEmail', 'riderPhone', 'riderAddress', 'riderVehicle', 'riderPassword', 'userLat', 'userLong'];
                foreach ($required_fields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Please fill in all required fields.");
                    }
                }

                // Sanitize input
                $riderFirstName = filter_var(trim($_POST['riderFirstName']), FILTER_SANITIZE_STRING);
                $riderLastName = filter_var(trim($_POST['riderLastName']), FILTER_SANITIZE_STRING);
                $riderEmail = filter_var(trim($_POST['riderEmail']), FILTER_SANITIZE_EMAIL);
                $riderPhone = filter_var(trim($_POST['riderPhone']), FILTER_SANITIZE_STRING);
                $riderAddress = filter_var(trim($_POST['riderAddress']), FILTER_SANITIZE_STRING);
                $riderVehicle = filter_var(trim($_POST['riderVehicle']), FILTER_SANITIZE_STRING);
                $riderPassword = filter_var(trim($_POST['riderPassword']), FILTER_SANITIZE_STRING);
                $userLat = filter_var(trim($_POST['userLat']), FILTER_SANITIZE_STRING);
                $userLong = filter_var(trim($_POST['userLong']), FILTER_SANITIZE_STRING);

                // Validate email format
                if (!filter_var($riderEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Please enter a valid email address.");
                }

                // Check if email already exists
                // You'll need to implement this check based on your database structure
                $existing_email = getAllRecords('rider', "WHERE riderEmail = '$riderEmail'");
                if ($existing_email) {
                    throw new Exception("Email already exists. Please use a different email.");
                }
                $existing_name = getAllRecords('rider', "WHERE riderFirstName = '$riderFirstName' AND riderLastName = '$riderLastName'");
                if ($existing_name) {
                    throw new Exception("Rider with this name already exists. Please use a different name.");
                }

                // Generate default password
                $password = password_hash($riderPassword, PASSWORD_BCRYPT);

                $data = [
                    'riderFirstName' => $riderFirstName,
                    'riderLastName' => $riderLastName,
                    'riderEmail' => $riderEmail,
                    'riderPhone' => $riderPhone,
                    'riderAddress' => $riderAddress,
                    'vehicleType' => $riderVehicle,
                    'riderPassword' => $password,
                    'riderCurrentLat' => $userLat,
                    'riderCurrentLong' => $userLong,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'updatedAt  ' => date('Y-m-d H:i:s')
                ];

                // Insert the rider into the database
                $result = insertRecord('rider', $data);

                if ($result) {
                    $response['success'] = true;
                } else {
                    $response['error'] = 'Failed to add rider to database.';
                }
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        // Clear any previous output
        ob_end_clean();

        // Set proper header and return JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // If not a POST request
    ob_end_clean();
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
