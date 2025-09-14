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

            $id = $_POST['id'];

            // Sanitize input
            $id = filter_var(trim($_POST['id']), FILTER_SANITIZE_NUMBER_INT);

            $get_emergency = getRecord('emergency_reports', 'id = ' . $id);

            $get_user = getRecord('users', 'id = ' . $get_emergency['user_id']);

            $deactivated_user = editRecord('users', ['is_active' => 0], 'id = ' . $get_emergency['user_id']);

            $data = [
                'status' => 'rejected',
                'updated_at  ' => date('Y-m-d H:i:s')
            ];



            // Insert the report into the database
            $result = editRecord('emergency_reports', $data, 'id = ' . $id);

            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Report submitted successfully.';
            } else {
                $response['error'] = 'Failed to insert report to database.';
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
