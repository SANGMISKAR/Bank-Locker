<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture form details
    $_SESSION['Name'] = $_POST['Name'];
    $_SESSION['Email_id'] = $_POST['Email_id'];
    $_SESSION['MobileNo'] = $_POST['MobileNo'];
    $_SESSION['Address'] = $_POST['Address'];
    $_SESSION['Occupation'] = $_POST['Occupation'];
    $_SESSION['lockerType'] = $_POST['lockerType'];
    $_SESSION['nominee_name'] = $_POST['nominee_name'];
    $_SESSION['relation_with_nominee'] = $_POST['relation_with_nominee'];
    $_SESSION['id_proof'] = $_POST['id_proof'];
    $_SESSION['id_proof_file'] = $_FILES['id_proof_file']['name'];

    // Move uploaded file to the desired location
    move_uploaded_file($_FILES['id_proof_file']['tmp_name'], 'uploads/' . $_FILES['id_proof_file']['name']);

    // Redirect to the payment page
    header("Location: payment.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locker Booking Form</title>
    <style>
        /* Existing styles */
        body {
            font-family: "Cambria", serif;
            background-color: antiquewhite;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: linear-gradient(to left, #ffffff, #eeeded);
            border-radius: 20px;
            padding:  10px 50px;
            box-sizing: border-box;
            width: 100%;
            max-width: 80%;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .container h1 {
            font-size: 35px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
            font-weight: bold;
            border-bottom: 2px solid #ff6700;
        }

        .form-group {
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .form-group label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: bolder;
            color: #252525;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #828282;
            border-radius: 8px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group textarea {
            resize: vertical;
        }

        .form-group input[type="file"] {
            border: none;
            padding: 0;
        }

        .btn {
            background-color: #ff6700;
            color: white;
            padding: 15px 25px;
            font-size: 18px;
            border: 2px solid black;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn:hover {
            background-color: #0c0e90;
            transform: scale(1.05);
        }

        /* Modal styles */
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
    <?php include 'navbar.html'; ?>
    <div class="container">
        <h1>Locker Booking Form</h1>
        <form id="lockerForm" method="post" enctype="multipart/form-data">
            <!-- Form fields -->
            <div class="form-group">
                <label for="Name">Name:</label>
                <input type="text" id="Name" name="Name" required>
            </div>
            <div class="form-group">
                <label for="Email_id">Email ID:</label>
                <input type="email" id="Email_id" name="Email_id" required>
            </div>
            <div class="form-group">
    <label for="MobileNo">Mobile No:</label>
    <input type="text" id="MobileNo" name="MobileNo" required oninput="validateMobileNo(this)">
    <span id="error-msg" style="color:red; display:none;">Please enter a valid mobile number.</span>
</div>
            <div class="form-group">
                <label for="Address">Complete Address:</label>
                <textarea id="Address" name="Address" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="Occupation">Occupation:</label>
                <input type="text" id="Occupation" name="Occupation" required>
            </div>
            <div class="form-group">
                <label for="lockerType">Type of Locker:</label>
                <select id="lockerType" name="lockerType" required>
                    <option value="">Select Locker Type</option>
                    <option value="Small">Small</option>
                    <option value="Medium">Medium</option>
                    <option value="Large">Large</option>
                    <option value="Extra-Large">Extra-Large</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nominee_name">Name of Nominee:</label>
                <input type="text" id="nominee_name" name="nominee_name" required>
            </div>
            <div class="form-group">
                <label for="relation_with_nominee">Relation with Nominee:</label>
                <input type="text" id="relation_with_nominee" name="relation_with_nominee" required>
            </div>
            <div class="form-group">
                <label for="id_proof">Select ID Proof:</label>
                <select id="id_proof" name="id_proof" required>
                    <option value="">Select ID Proof</option>
                    <option value="Pancard">Pan Card</option>
                    <option value="aadhar">Aadhar Card</option>
                </select>
            </div>
            <div class="form-group">
                <label for="id_proof_file">Upload ID Proof:</label>
                <input type="file" id="id_proof_file" name="id_proof_file" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>
            <div class="form-group">
                <button type="button" class="btn" onclick="validateAndShowModal()">Submit</button>
            </div>

            <!-- Confirmation Modal -->
            <div id="confirmationModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">Confirm Information</div>
                    <div class="modal-body">
                        <p><strong>Name:</strong> <span id="confirmName"></span></p>
                        <p><strong>Email ID:</strong> <span id="confirmEmail_id"></span></p>
                        <p><strong>Mobile No:</strong> <span id="confirmMobileNo"></span></p>
                        <p><strong>Address:</strong> <span id="confirmAddress"></span></p>
                        <p><strong>Occupation:</strong> <span id="confirmOccupation"></span></p>
                        <p><strong>Locker Type:</strong> <span id="confirmLockerType"></span></p>
                        <p><strong>Nominee Name:</strong> <span id="confirmNominee_name"></span></p>
                        <p><strong>Relation with Nominee:</strong> <span id="confirmRelation_with_nominee"></span></p>
                        <p><strong>ID Proof:</strong> <span id="confirmId_proof"></span></p>
                        <p><strong>ID Proof File:</strong> <span id="confirmId_proof_file"></span></p>
                    </div>
                    <div class="modal-footer">
                        <form id="confirmForm" method="post">
                            <button type="submit" class="close-btn" name="confirm">Confirm & Proceed</button>
                        </form>
                        <button class="close-btn" id="cancelBtn">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function validateMobileNo(input) {
        // Remove any non-numeric characters
        input.value = input.value.replace(/[^0-9]/g, '');

        // Optionally, you can check for specific mobile number length (e.g., 10 digits)
        const errorMsg = document.getElementById('error-msg');
        if (input.value.length !== 10) {
            errorMsg.style.display = 'block';
        } else {
            errorMsg.style.display = 'none';
        }
    }
        function validateAndShowModal() {
            const form = document.getElementById('lockerForm');
            if (form.checkValidity()) {
                // Fill the modal with form data and show it
                document.getElementById('confirmationModal').style.display = 'flex';
                populateModal();
            } else {
                // Trigger HTML5 validation
                form.reportValidity();
            }
        }

        function populateModal() {
            // Populate modal content with form data
            document.getElementById('confirmName').innerText = document.getElementById('Name').value;
            document.getElementById('confirmEmail_id').innerText = document.getElementById('Email_id').value;
            document.getElementById('confirmMobileNo').innerText = document.getElementById('MobileNo').value;
            document.getElementById('confirmAddress').innerText = document.getElementById('Address').value;
            document.getElementById('confirmOccupation').innerText = document.getElementById('Occupation').value;
            document.getElementById('confirmLockerType').innerText = document.getElementById('lockerType').value;
            document.getElementById('confirmNominee_name').innerText = document.getElementById('nominee_name').value;
            document.getElementById('confirmRelation_with_nominee').innerText = document.getElementById('relation_with_nominee').value;
            document.getElementById('confirmId_proof').innerText = document.getElementById('id_proof').value;
            document.getElementById('confirmId_proof_file').innerText = document.getElementById('id_proof_file').value;
        }

        document.getElementById('cancelBtn').onclick = function() {
            document.getElementById('confirmationModal').style.display = 'none';
        };
    </script>
</body>
</html>
