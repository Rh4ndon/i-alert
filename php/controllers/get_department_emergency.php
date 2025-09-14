    <?php
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Start output buffering to prevent any accidental output
    ob_start();

    require_once '../models/functions.php';

    // Insert a new user
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Initialize response array
        $response = ['success' => false, 'error' => ''];

        try {

            $result = getAllRecords('emergency_reports', 'WHERE status != "rejected"');
            foreach ($result as $key => $value) {
                $user = getRecord('users', 'id = ' . $value['user_id']);

                $result[$key]['title'] = $value['title'];
                $result[$key]['description'] = $value['description'];
                $result[$key]['status'] = $value['status'];
                $result[$key]['user'] = $user['first_name'] . ' ' . $user['last_name'];
                $result[$key]['user_phone'] = $user['phone'];
                //convert created_at into time month day year
                $result[$key]['time'] = date("F j, Y, g:i a", strtotime($value['created_at']));
            }



            if ($result) {
                $response['success'] = true;
                $response['data'] = $result;
            } else {
                $response['error'] = 'Failed to get report to database.';
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
