    <?php
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Start output buffering to prevent any accidental output
    ob_start();

    require_once '../models/functions.php';

    // Insert a new user
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Initialize response array
        $response = ['success' => false, 'error' => ''];

        try {


            // Validate required fields 
            $required_fields = ['firstName', 'lastName', 'email', 'phone', 'address', 'password'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required fields.");
                }
            }

            // Sanitize input
            $userFirstName = filter_var(trim($_POST['firstName']), FILTER_SANITIZE_STRING);
            $userLastName = filter_var(trim($_POST['lastName']), FILTER_SANITIZE_STRING);
            $userEmail = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $userPhone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
            $userAddress = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);
            $userPassword = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);

            // Validate email format
            if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Please enter a valid email address.");
            }

            // Check if email already exists
            // You'll need to implement this check based on your database structure
            $existing_email = getAllRecords('users', "WHERE email = '$userEmail'");
            if ($existing_email) {
                throw new Exception("Email already exists. Please use a different email.");
            }
            $existing_name = getAllRecords('users', "WHERE first_name = '$userFirstName' AND last_name = '$userLastName'");
            if ($existing_name) {
                throw new Exception("Customer with this name already exists. Please use a different name.");
            }

            // Generate default password
            $password = password_hash($userPassword, PASSWORD_BCRYPT);

            $data = [
                'first_name' => $userFirstName,
                'last_name' => $userLastName,
                'email' => $userEmail,
                'phone' => $userPhone,
                'address' => $userAddress,
                'password_hash' => $password,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at  ' => date('Y-m-d H:i:s')
            ];

            // Insert the customer into the database
            $result = insertRecord('users', $data);

            if ($result) {
                $response['success'] = true;
            } else {
                $response['error'] = 'Failed to add user to database.';
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
