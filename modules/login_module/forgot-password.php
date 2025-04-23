<?php
// Include config file
<?php
// Include config file
$pdo = require_once "../../config/db_connection.php";

// Include Composer's autoloader if it exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment variables
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    if ($env_vars === false) {
        error_log('Failed to parse .env file');
        die('Configuration error. Please contact support.');
    }
    foreach ($env_vars as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
} else {
    error_log('.env file not found at: ' . $env_file);
    die('Configuration file not found. Please contact support.');
}

// Get SendGrid API key from environment variable
$sendgrid_api_key = $_ENV['SENDGRID_API_KEY'] ?? null;
if (!$sendgrid_api_key) {
    error_log('SendGrid API key not found in environment variables');
    die('Email service configuration error. Please contact support.');
}

// Debug: Log API key length (not the actual key)
error_log('SendGrid API Key length: ' . strlen($sendgrid_api_key));

// Define variables and initialize with empty values
$email = "";
$email_err = "";
$success_msg = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } else{
        $email = trim($_POST["email"]);
        
        // Check if email exists in database
        $sql = "SELECT id, email, first_name, last_name FROM users WHERE email = :email";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        // Generate a long, unique token
                        $token = bin2hex(random_bytes(32));
                        
                        // Set expiration time to 1 hour from now
                        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        
                        // Store token in database with expiration
                        $sql = "UPDATE users SET 
                                reset_token = :token, 
                                reset_expires = :expires,
                                reset_used = 0 
                                WHERE email = :email";
                                
                        if($update_stmt = $pdo->prepare($sql)){
                            $update_stmt->bindParam(":token", $token, PDO::PARAM_STR);
                            $update_stmt->bindParam(":expires", $expires, PDO::PARAM_STR);
                            $update_stmt->bindParam(":email", $email, PDO::PARAM_STR);
                            
                            if($update_stmt->execute()){
                                try {
                                    // Initialize SendGrid with API key
                                    $sendgrid = new \SendGrid($sendgrid_api_key);
                                    
                                    // Test API key first
                                    $test_response = $sendgrid->client->api_keys()->get();
                                    error_log('API Key Test Response: ' . $test_response->statusCode());
                                    error_log('API Key Test Body: ' . $test_response->body());
                                    
                                    if ($test_response->statusCode() !== 200) {
                                        throw new Exception('SendGrid API key validation failed: ' . $test_response->body());
                                    }
                                    
                                    // Create email content
                                    $email_content = "Hello " . $row["first_name"] . ",<br><br>" .
                                        "You have requested to reset your password. Click the link below to reset your password:<br><br>" .
                                        "<a href='http://" . $_SERVER['HTTP_HOST'] . "/myDomain/PothenEsxes/LoginWITHfunctionality/reset-password.php?token=" . $token . "'>Reset Password</a><br><br>" .
                                        "<strong>Important Security Information:</strong><br>" .
                                        "- This link will expire in 1 hour<br>" .
                                        "- This link can only be used once<br>" .
                                        "- If you did not request this password reset, please ignore this email and ensure your account is secure<br><br>" .
                                        "Best regards,<br>" .
                                        "ΠΟΘΕΝ ΕΣΧΕΣ Team";
                                    
                                    // Prepare email data
                                    $data = [
                                        "personalizations" => [
                                            [
                                                "to" => [
                                                    [
                                                        "email" => $row["email"],
                                                        "name" => $row["first_name"] . " " . $row["last_name"]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        "from" => [
                                            "email" => "noreply@yourdomain.com",
                                            "name" => "ΠΟΘΕΝ ΕΣΧΕΣ"
                                        ],
                                        "subject" => "Password Reset Request",
                                        "content" => [
                                            [
                                                "type" => "text/html",
                                                "value" => $email_content
                                            ]
                                        ]
                                    ];
                                    
                                    // Debug: Log request data (without sensitive info)
                                    $log_data = $data;
                                    $log_data['personalizations'][0]['to'][0]['email'] = '[REDACTED]';
                                    error_log('SendGrid Request Data: ' . json_encode($log_data));
                                    
                                    // Send the email
                                    $response = $sendgrid->client->mail()->send()->post($data);
                                    
                                    // Debug: Log response
                                    error_log('SendGrid Response Status: ' . $response->statusCode());
                                    error_log('SendGrid Response Headers: ' . json_encode($response->headers()));
                                    error_log('SendGrid Response Body: ' . $response->body());
                                    
                                    if($response->statusCode() == 202) {
                                        $success_msg = "If an account exists with this email, you will receive password reset instructions.";
                                    } else {
                                        $error_message = 'Failed to send email. Status code: ' . $response->statusCode() . ', Body: ' . $response->body();
                                        error_log($error_message);
                                        throw new Exception($error_message);
                                    }
                                } catch (Exception $e) {
                                    error_log('SendGrid Error: ' . $e->getMessage());
                                    error_log('SendGrid Error Trace: ' . $e->getTraceAsString());
                                    error_log('SendGrid Error File: ' . $e->getFile() . ' Line: ' . $e->getLine());
                                    echo "There was an error sending the email. Please try again later.";
                                }
                            }
                        }
                    }
                } else {
                    // Don't reveal if email exists or not
                    $success_msg = "If an account exists with this email, you will receive password reset instructions.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Main Content -->
    <main class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card feature-card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="60" class="mb-3">
                            <h2 class="fw-bold">Forgot Password</h2>
                            <p class="text-muted">Enter your email to reset your password</p>
                        </div>

                        <?php if(!empty($success_msg)): ?>
                            <div class="alert alert-success"><?php echo $success_msg; ?></div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" required>
                                    <div class="invalid-feedback"><?php echo $email_err; ?></div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning text-dark w-100 mb-3">
                                <i class="bi bi-send"></i> Send Reset Link
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Remember your password? <a href="login.php" class="text-decoration-none">Sign in</a></p>
                            </div>
                            <div class="text-center">
                                <p class="mb-0"><a href="../index.php" class="text-decoration-none">Back to Home</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 

// Include Composer's autoloader if it exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment variables
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    if ($env_vars === false) {
        error_log('Failed to parse .env file');
        die('Configuration error. Please contact support.');
    }
    foreach ($env_vars as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
} else {
    error_log('.env file not found at: ' . $env_file);
    die('Configuration file not found. Please contact support.');
}

// Get SendGrid API key from environment variable
$sendgrid_api_key = $_ENV['SENDGRID_API_KEY'] ?? null;
if (!$sendgrid_api_key) {
    error_log('SendGrid API key not found in environment variables');
    die('Email service configuration error. Please contact support.');
}

// Debug: Log API key length (not the actual key)
error_log('SendGrid API Key length: ' . strlen($sendgrid_api_key));

// Define variables and initialize with empty values
$email = "";
$email_err = "";
$success_msg = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } else{
        $email = trim($_POST["email"]);
        
        // Check if email exists in database
        $sql = "SELECT id, email, first_name, last_name FROM users WHERE email = :email";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        // Generate a long, unique token
                        $token = bin2hex(random_bytes(32));
                        
                        // Set expiration time to 1 hour from now
                        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        
                        // Store token in database with expiration
                        $sql = "UPDATE users SET 
                                reset_token = :token, 
                                reset_expires = :expires,
                                reset_used = 0 
                                WHERE email = :email";
                                
                        if($update_stmt = $pdo->prepare($sql)){
                            $update_stmt->bindParam(":token", $token, PDO::PARAM_STR);
                            $update_stmt->bindParam(":expires", $expires, PDO::PARAM_STR);
                            $update_stmt->bindParam(":email", $email, PDO::PARAM_STR);
                            
                            if($update_stmt->execute()){
                                try {
                                    // Initialize SendGrid with API key
                                    $sendgrid = new \SendGrid($sendgrid_api_key);
                                    
                                    // Test API key first
                                    $test_response = $sendgrid->client->api_keys()->get();
                                    error_log('API Key Test Response: ' . $test_response->statusCode());
                                    error_log('API Key Test Body: ' . $test_response->body());
                                    
                                    if ($test_response->statusCode() !== 200) {
                                        throw new Exception('SendGrid API key validation failed: ' . $test_response->body());
                                    }
                                    
                                    // Create email content
                                    $email_content = "Hello " . $row["first_name"] . ",<br><br>" .
                                        "You have requested to reset your password. Click the link below to reset your password:<br><br>" .
                                        "<a href='http://" . $_SERVER['HTTP_HOST'] . "/myDomain/PothenEsxes/LoginWITHfunctionality/reset-password.php?token=" . $token . "'>Reset Password</a><br><br>" .
                                        "<strong>Important Security Information:</strong><br>" .
                                        "- This link will expire in 1 hour<br>" .
                                        "- This link can only be used once<br>" .
                                        "- If you did not request this password reset, please ignore this email and ensure your account is secure<br><br>" .
                                        "Best regards,<br>" .
                                        "ΠΟΘΕΝ ΕΣΧΕΣ Team";
                                    
                                    // Prepare email data
                                    $data = [
                                        "personalizations" => [
                                            [
                                                "to" => [
                                                    [
                                                        "email" => $row["email"],
                                                        "name" => $row["first_name"] . " " . $row["last_name"]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        "from" => [
                                            "email" => "noreply@yourdomain.com",
                                            "name" => "ΠΟΘΕΝ ΕΣΧΕΣ"
                                        ],
                                        "subject" => "Password Reset Request",
                                        "content" => [
                                            [
                                                "type" => "text/html",
                                                "value" => $email_content
                                            ]
                                        ]
                                    ];
                                    
                                    // Debug: Log request data (without sensitive info)
                                    $log_data = $data;
                                    $log_data['personalizations'][0]['to'][0]['email'] = '[REDACTED]';
                                    error_log('SendGrid Request Data: ' . json_encode($log_data));
                                    
                                    // Send the email
                                    $response = $sendgrid->client->mail()->send()->post($data);
                                    
                                    // Debug: Log response
                                    error_log('SendGrid Response Status: ' . $response->statusCode());
                                    error_log('SendGrid Response Headers: ' . json_encode($response->headers()));
                                    error_log('SendGrid Response Body: ' . $response->body());
                                    
                                    if($response->statusCode() == 202) {
                                        $success_msg = "If an account exists with this email, you will receive password reset instructions.";
                                    } else {
                                        $error_message = 'Failed to send email. Status code: ' . $response->statusCode() . ', Body: ' . $response->body();
                                        error_log($error_message);
                                        throw new Exception($error_message);
                                    }
                                } catch (Exception $e) {
                                    error_log('SendGrid Error: ' . $e->getMessage());
                                    error_log('SendGrid Error Trace: ' . $e->getTraceAsString());
                                    error_log('SendGrid Error File: ' . $e->getFile() . ' Line: ' . $e->getLine());
                                    echo "There was an error sending the email. Please try again later.";
                                }
                            }
                        }
                    }
                } else {
                    // Don't reveal if email exists or not
                    $success_msg = "If an account exists with this email, you will receive password reset instructions.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Main Content -->
    <main class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card feature-card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="60" class="mb-3">
                            <h2 class="fw-bold">Forgot Password</h2>
                            <p class="text-muted">Enter your email to reset your password</p>
                        </div>

                        <?php if(!empty($success_msg)): ?>
                            <div class="alert alert-success"><?php echo $success_msg; ?></div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" required>
                                    <div class="invalid-feedback"><?php echo $email_err; ?></div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning text-dark w-100 mb-3">
                                <i class="bi bi-send"></i> Send Reset Link
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Remember your password? <a href="login.php" class="text-decoration-none">Sign in</a></p>
                            </div>
                            <div class="text-center">
                                <p class="mb-0"><a href="../index.php" class="text-decoration-none">Back to Home</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 
