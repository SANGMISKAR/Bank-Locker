<?php
session_start();
include 'connection.php';

// Check if all necessary session variables are set
$required_session_vars = [
    'accountno', 'district', 'branch', 'Name', 'Email_id', 'MobileNo', 'Address', 
    'Occupation', 'lockerType', 'nominee_name', 'relation_with_nominee', 
    'id_proof', 'id_proof_file', 'paymentMode', 'lockerCharges', 
    'processingFees', 'gstAmount', 'totalAmount'
];

foreach ($required_session_vars as $var) {
    if (!isset($_SESSION[$var])) {
        echo "$var is not set.<br>";
        exit();
    }
}

// Prepare SQL insert query
$stmt = $conn->prepare("INSERT INTO locker_bookings (accountno, district, branch, Name, Email_id, MobileNo, Address, Occupation, lockerType, nominee_name, relation_with_nominee, id_proof, id_proof_file, paymentMode, lockerCharges, processingFees, gstAmount, totalAmount, booking_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$stmt->bind_param(
    "ssssssssssssssssss",
    $_SESSION['accountno'],
    $_SESSION['district'],
    $_SESSION['branch'],
    $_SESSION['Name'],
    $_SESSION['Email_id'],
    $_SESSION['MobileNo'],
    $_SESSION['Address'],
    $_SESSION['Occupation'],
    $_SESSION['lockerType'],
    $_SESSION['nominee_name'],
    $_SESSION['relation_with_nominee'],
    $_SESSION['id_proof'],
    $_SESSION['id_proof_file'],
    $_SESSION['paymentMode'],
    $_SESSION['lockerCharges'],
    $_SESSION['processingFees'],
    $_SESSION['gstAmount'],
    $_SESSION['totalAmount']
);

// Execute the query and handle success or error
if ($stmt->execute()) {
    echo "<script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function() {
                // Show modal after successful insertion
                document.getElementById('successModal').style.display = 'block';
            });
          </script>";
} else {
    echo "Error inserting data: " . $stmt->error;
}

// Close the statement and the connection
$stmt->close();
$conn->close();

// Clear the session after successful insertion
session_destroy();
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Submission Success</title>
    <style>
        /* Modal styles */
            body {
                font-family: "Cambria", serif;
                background-color: antiquewhite;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            
            
            }  

            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0, 0, 0, 0.5);
                justify-content: center;
                align-items: center;
            }

            .modal-content {
                background-color: white;
                margin: auto;
                padding: 20px;
                border: 1px solid #888;
                width: 40%;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                text-align: center;
            }

            .modal-header {
                font-size: 20px;
                margin-bottom: 10px;
                font-weight: bold;
            }

            .modal-body {
                margin-bottom: 20px;
                font-family: 'Lucida Bright';
            }

            .modal-footer {
                text-align: right;
            }

            .close-btn {
                background-color: #ff6700;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }

            .close-btn:hover {
                background-color: #e65c00;
            }
    </style>
</head>
<body>
    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                Form Submitted Successfully
            </div>
            <div class="modal-body">
                Your locker booking form has been successfully submitted and is now in verification mode.
            </div>
            <div class="modal-footer">
                <button class="close-btn" onclick="redirectToHome()">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Function to redirect the user to home.html after closing the modal
        function redirectToHome() {
            window.location.href = 'home.html';
        }
    </script>
</body>
</html>
 