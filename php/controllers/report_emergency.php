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
            $required_fields = ['user_id', 'emergencyType', 'location', 'description'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required fields.");
                }
            }

            // Sanitize input
            $user_id = filter_var(trim($_POST['user_id']), FILTER_SANITIZE_NUMBER_INT);
            $emergencyType = filter_var(trim($_POST['emergencyType']), FILTER_SANITIZE_NUMBER_INT);
            $location = filter_var(trim($_POST['location']), FILTER_SANITIZE_STRING);
            $description = filter_var(trim($_POST['description']), FILTER_SANITIZE_STRING);

            $get_emergency_type = getRecord('emergency_types', 'id = ' . $emergencyType);

            $data = [
                'user_id' => $user_id,
                'emergency_type_id' => $emergencyType,
                'title' => $get_emergency_type['name'] . '-' . $location,
                'description' => $description,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at  ' => date('Y-m-d H:i:s')
            ];

            // file handling if theres an image
            if (isset($_FILES['imageUpload']) && $_FILES['imageUpload']['error'] === UPLOAD_ERR_OK) {
                // Handle file upload
                $image = $_FILES['imageUpload'];
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

                if (!in_array($image['type'], $allowed_types)) {
                    throw new Exception("Invalid image type. Only JPG, PNG, and GIF are allowed.");
                }

                $upload_dir = '../uploads/';

                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        throw new Exception("Failed to create upload directory.");
                    }
                }

                // Generate unique filename
                $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $image['name']);
                $upload_file = $upload_dir . $image_name;

                if (!move_uploaded_file($image['tmp_name'], $upload_file)) {
                    throw new Exception("Failed to upload image. Check directory permissions.");
                }

                $data['image_path'] = $image_name;
            }

            // handle the $location is like Lat: 15.604200, Lon: 120.601600 parse the lat and lon 
            if (strpos($location, 'Lat:') !== false && strpos($location, 'Lon:') !== false) {
                $parts = explode(',', $location);
                $data['latitude'] = (float)trim(str_replace('Lat:', '', $parts[0]));
                $data['longitude'] = (float)trim(str_replace('Lon:', '', $parts[1]));
            } else {
                $data['address'] = $location;
            }

            // Insert the report into the database
            $result = insertRecord('emergency_reports', $data);

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
