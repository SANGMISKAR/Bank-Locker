<?php
session_start();
include 'connection.php';

// Include the PHPMailer library
require 'C:/xampp/htdocs/admin/PHPMailer/src/Exception.php';
require 'C:/xampp/htdocs/admin/PHPMailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/admin/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

// Fetch data from the database where status is NULL
$sql = "SELECT id, accountno, district, branch, Name, Email_id, MobileNo, Address, Occupation, lockerType, nominee_name, relation_with_nominee, id_proof, id_proof_file, paymentMode, lockerCharges, processingFees, gstAmount, totalAmount, booking_date, status, locker_no, locker_key_no FROM locker_bookings WHERE status IS NULL";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Error fetching records: " . $conn->error);
}

// Handle form submission for verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify']) && is_array($_POST['verify'])) {
        // Begin Transaction
        $conn->begin_transaction();

        try {
            foreach ($_POST['verify'] as $index => $accountno) {
                // Ensure locker_no and locker_key_no are set
                $locker_no = isset($_POST['locker_no'][$index]) ? $_POST['locker_no'][$index] : '';
                $locker_key_no = isset($_POST['locker_key_no'][$index]) ? $_POST['locker_key_no'][$index] : '';
                $status = isset($_POST['status'][$index]) ? 'verified' : 'unverified'; 

                // Sanitize input data
                $accountno = $conn->real_escape_string($accountno);

                // Fetch branch and lockerType for the current accountno
                $fetchSql = "SELECT branch, lockerType FROM locker_bookings WHERE accountno = ?";
                if ($fetchStmt = $conn->prepare($fetchSql)) {
                    $fetchStmt->bind_param("s", $accountno);
                    $fetchStmt->execute();
                    $fetchStmt->bind_result($branch, $lockerType);
                    $fetchStmt->fetch();
                    $fetchStmt->close();
                } else {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // Validate lockerType
                $validLockerTypes = ['Small', 'Medium', 'Large', 'ExtraLarge'];
                if (!in_array($lockerType, $validLockerTypes)) {
                    throw new Exception("Invalid locker type: " . htmlspecialchars($lockerType));
                }

                // Prepare and execute the update statement for locker_bookings
                if ($stmt = $conn->prepare("UPDATE locker_bookings SET status = ?, locker_no = ?, locker_key_no = ? WHERE accountno = ?")) {
                    $stmt->bind_param("ssss", $status, $locker_no, $locker_key_no, $accountno);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // Update the lockers table
                $lockerTypeColumn = "";
                switch ($lockerType) {
                    case 'Small':
                        $lockerTypeColumn = 'Small';
                        break;
                    case 'Medium':
                        $lockerTypeColumn = 'Medium';
                        break;
                    case 'Large':
                        $lockerTypeColumn = 'Large';
                        break;
                    case 'ExtraLarge':
                        $lockerTypeColumn = 'ExtraLarge';
                        break;
                }

                // Ensure lockerTypeColumn is valid to prevent SQL injection
                if (!in_array($lockerTypeColumn, $validLockerTypes)) {
                    throw new Exception("Invalid locker type column: " . htmlspecialchars($lockerTypeColumn));
                }

                // Prepare the UPDATE statement dynamically
                $updateLockersSql = "UPDATE lockers SET $lockerTypeColumn = $lockerTypeColumn - 1 WHERE Branch = ?";
                if ($updateLockersStmt = $conn->prepare($updateLockersSql)) {
                    $updateLockersStmt->bind_param("s", $branch);
                    
                    if (!$updateLockersStmt->execute()) {
                        throw new Exception("Execute failed: " . $updateLockersStmt->error);
                    }
                    $updateLockersStmt->close();
                } else {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // Fetch the user's email and payment details for sending the email
                $userSql = "SELECT Email_id, lockerCharges, processingFees, gstAmount, totalAmount, lockerType FROM locker_bookings WHERE accountno = ?";
                if ($userStmt = $conn->prepare($userSql)) {
                    $userStmt->bind_param("s", $accountno);
                    $userStmt->execute();
                    $userStmt->bind_result($email, $lockerCharges, $processingFees, $gstAmount, $totalAmount, $lockerType);
                    $userStmt->fetch();

                    // Fetch locker_no and locker_key_no for email
                    // Assuming locker_no and locker_key_no are already set above

                    // Send email using PHPMailer
                    sendVerificationEmail($email, $locker_no, $locker_key_no, $lockerType, $lockerCharges, $processingFees, $gstAmount, $totalAmount);

                    $userStmt->close();
                } else {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
            }

            // Commit Transaction
            $conn->commit();

            echo "<script>alert('Selected records have been verified and locker counts updated.'); window.location.href='admin.php';</script>";
            exit();
        } catch (Exception $e) {
            // Rollback Transaction on Error
            $conn->rollback();
            die("Error processing verification: " . $e->getMessage());
        }
    }
}

// Function to send email using PHPMailer
function sendVerificationEmail($email, $locker_no, $locker_key_no, $lockerType, $lockerCharges, $processingFees, $gstAmount, $totalAmount) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0; // Enable for debug output
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kushalgupta8424@gmail.com'; 
        $mail->Password = 'wcfbhcfsxavwzzrv'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your_email@example.com', 'District Co-operative Bank');
        $mail->addAddress($email); 

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Locker Booking Verified';
        $mail->Body = "
            <h1>Locker Booking Verified</h1>
            <p>Your locker booking request has been successfully verified.</p>
            <p><strong>Locker No:</strong> $locker_no</p>
            <p><strong>Locker Key No:</strong> $locker_key_no</p>
            <p><strong>Locker Type:</strong> $lockerType</p>
            <p><strong>Locker Charges:</strong> $lockerCharges</p>
            <p><strong>Processing Fees:</strong> $processingFees</p>
            <p><strong>GST Amount:</strong> $gstAmount</p>
            <p><strong>Total Amount:</strong> $totalAmount</p>
            <p>Thank you for booking a locker with us.</p>";

        $mail->send();
    } catch (Exception $e) {
        // Log the error instead of displaying it
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

// Close connection
$conn->close();
?>

<!-- Your HTML form goes here -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Verification</title>
    <style>
        /* Include the CSS from the admin page */
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f4f7fc;
            color: #666;
            font-family: "Montserrat", sans-serif;
        }

        .header {
            background-color: #0adcd1;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header h2 {
            margin: 0;
        }

        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #e53935;
        }
        .home-btn {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
        }

        .home-btn:hover {
            background-color: #45a049;
        }


        .main-content {
            padding: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        fieldset {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
        }

        legend {
            font-weight: bold;
            margin-bottom: 10px;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
        }

        input[type="text"], input[type="checkbox"] {
            margin-bottom: 10px;
        }

        input[type="text"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
        }

        input[type="checkbox"] {
            margin-right: 10px;
        }

        .submit-btn {
            background-color: #0adcd1;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            align-self: flex-start;
        }

        .submit-btn:hover {
            background-color: #0aabaf;
        }

        .image-cell img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <h2>Admin Dashboard</h2>
        <div>
            <a href="admin.php" class="home-btn">Back to Home</a>
            <a href="admin.php?action=logout" class="logout-btn">Logout</a>
        </div>
    </header>

    <!-- End Header -->

    <!-- Main Content -->
    <div class="main-content">
        <h3>Verify Submissions</h3>
        <form method="POST">
            <?php if ($result->num_rows > 0): ?>
                <?php $index = 0; // Initialize index for form data ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <fieldset>
                        <legend>Record <?php echo htmlspecialchars($row['id']); ?></legend>
                        <input type="hidden" name="verify[]" value="<?php echo htmlspecialchars($row['accountno']); ?>">

                        <label>Account No:</label>
                        <input type="text" name="accountno[]" value="<?php echo htmlspecialchars($row['accountno']); ?>" readonly>

                        <label>District:</label>
                        <input type="text" name="district[]" value="<?php echo htmlspecialchars($row['district']); ?>" readonly>

                        <label>Branch:</label>
                        <input type="text" name="branch[]" value="<?php echo htmlspecialchars($row['branch']); ?>" readonly>

                        <label>Name:</label>
                        <input type="text" name="Name[]" value="<?php echo htmlspecialchars($row['Name']); ?>" readonly>

                        <label>Email:</label>
                        <input type="text" name="Email_id[]" value="<?php echo htmlspecialchars($row['Email_id']); ?>" readonly>

                        <label>Mobile No:</label>
                        <input type="text" name="MobileNo[]" value="<?php echo htmlspecialchars($row['MobileNo']); ?>" readonly>

                        <label>Address:</label>
                        <input type="text" name="Address[]" value="<?php echo htmlspecialchars($row['Address']); ?>" readonly>

                        <label>Occupation:</label>
                        <input type="text" name="Occupation[]" value="<?php echo htmlspecialchars($row['Occupation']); ?>" readonly>

                        <label>Locker Type:</label>
                        <input type="text" name="lockerType[]" value="<?php echo htmlspecialchars($row['lockerType']); ?>" readonly>

                        <label>Nominee Name:</label>
                        <input type="text" name="nominee_name[]" value="<?php echo htmlspecialchars($row['nominee_name']); ?>" readonly>

                        <label>Relation with Nominee:</label>
                        <input type="text" name="relation_with_nominee[]" value="<?php echo htmlspecialchars($row['relation_with_nominee']); ?>" readonly>

                        <label>ID Proof:</label>
                        <input type="text" name="id_proof[]" value="<?php echo htmlspecialchars($row['id_proof']); ?>" readonly>

                        <label>ID Proof File:</label>
                        <div class="image-cell">
                            <img src="uploads/<?php echo htmlspecialchars($row['id_proof_file']); ?>" alt="ID Proof">
                        </div>

                        <label>Payment Mode:</label>
                        <input type="text" name="paymentMode[]" value="<?php echo htmlspecialchars($row['paymentMode']); ?>" readonly>

                        <label>Locker Charges:</label>
                        <input type="text" name="lockerCharges[]" value="<?php echo htmlspecialchars($row['lockerCharges']); ?>" readonly>

                        <label>Processing Fees:</label>
                        <input type="text" name="processingFees[]" value="<?php echo htmlspecialchars($row['processingFees']); ?>" readonly>

                        <label>GST Amount:</label>
                        <input type="text" name="gstAmount[]" value="<?php echo htmlspecialchars($row['gstAmount']); ?>" readonly>

                        <label>Total Amount:</label>
                        <input type="text" name="totalAmount[]" value="<?php echo htmlspecialchars($row['totalAmount']); ?>" readonly>

                        <label>Booking Date:</label>
                        <input type="text" name="booking_date[]" value="<?php echo htmlspecialchars($row['booking_date']); ?>" readonly>

                        <label>Locker No:</label>
                        <input type="text" name="locker_no[]" value="<?php echo htmlspecialchars($row['locker_no']); ?>">

                        <label>Locker Key No:</label>
                        <input type="text" name="locker_key_no[]" value="<?php echo htmlspecialchars($row['locker_key_no']); ?>">

                        <label>Status:</label>
                        <input type="checkbox" name="status[]" value="<?php echo htmlspecialchars($index); ?>" <?php echo $row['status'] === 'verified' ? 'checked' : ''; ?>>
                        <span>Verified</span>
                    </fieldset>
                    <?php $index++; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No records found</p>
            <?php endif; ?>
            <button type="submit" class="submit-btn">Verify</button>
        </form>
    </div>
    <!-- End Main Content -->
</body>
</html>
