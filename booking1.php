<?php
session_start();
include 'connection.php';

$message = '';
$success = false;
$accountNotFound = false;
$lockerAlreadyAllotted = false;

// Fetch districts for dropdown
$sql = "SELECT DISTINCT District_id, District FROM accountverification";
$result = mysqli_query($conn, $sql);
$districts = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle AJAX request for fetching branches based on selected district
if (isset($_GET['District_id'])) {
    $District_id = mysqli_real_escape_string($conn, $_GET['District_id']);
    $sql = "SELECT DISTINCT Branch FROM accountverification WHERE District_id = '$District_id'";
    $result = mysqli_query($conn, $sql);
    $branches = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($branches);
    mysqli_close($conn);
    exit;
}

// Handle AJAX request for fetching locker types based on selected branch
if (isset($_GET['Branch'])) {
    $branch = mysqli_real_escape_string($conn, $_GET['Branch']);
    $lockerSql = "SELECT `Small`, `Medium`, `Large`, `ExtraLarge` FROM `lockers` WHERE Branch = '$branch'";
    $lockerResult = mysqli_query($conn, $lockerSql);
    $lockers = mysqli_fetch_assoc($lockerResult);
    echo json_encode($lockers);
    mysqli_close($conn);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $accountno = mysqli_real_escape_string($conn, $_POST['accountno']);

    if (!empty($district) && !empty($branch) && !empty($accountno)) {
        $lockerCheckSql = "SELECT * FROM locker_bookings WHERE accountno = '$accountno' AND branch = '$branch' AND district = '$district'";
        $lockerCheckResult = mysqli_query($conn, $lockerCheckSql);

        if (mysqli_num_rows($lockerCheckResult) > 0) {
            $lockerAlreadyAllotted = true; // Account already has a locker allotted
        } else {
            $sql = "SELECT * FROM accountverification WHERE accountno = '$accountno' AND branch = '$branch' AND District_id = '$district'";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                // Save user input in session variables
                $_SESSION['district'] = $district;
                $_SESSION['branch'] = $branch;
                $_SESSION['accountno'] = $accountno;
                
                $success = true; // Account verified successfully
                header("Location: form.php"); // Redirect to the next page
                exit();
            } else {
                $accountNotFound = true; // Account not found in the database
            }
        }
    } else {
        $message = "All fields are required.";
    }
}

mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locker Allotment e-Portal</title>
    <style>
        /* Basic styles for body and container */
        body {
            font-family: "Cambria", serif;
            background-color: antiquewhite;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            background-color: white;
            border-radius: 20px;
            padding: 50px;
            box-sizing: border-box;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        /* Form styles */
        .container h2 {
            font-size: 30px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: bolder;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .links {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .links a {
            color: #d55611;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
        }

        .btn {
            background-color: #ff6700;
            color: white;
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
        }

        .btn:hover {
            background-color: #0c0e90;
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
            background-color: #0c0e90;
        }

    </style>
</head>
<body>
    <?php include 'navbar.html'; ?>
    <div class="container">
        <h2>Select Branch</h2>
        <form id="lockerForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="district">DISTRICT</label>
                <select id="district" name="district" required onchange="getBranches(this.value)">
                    <option value="">Select</option>
                    <?php foreach ($districts as $district) { ?>
                        <option value="<?php echo $district['District_id']; ?>"><?php echo $district['District']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="branch">BRANCH</label>
                <select id="branch" name="branch" required onchange="getLockers(this.value)">
                    <option value="">Select Branch</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="accountno">Account Number</label>
                <input type="text" id="accountno" name="accountno" required>
            </div>

            <div id="lockerCount" style="display:none;">
                <h3>Available Lockers</h3>
                <p>Small: <span id="smallCount"></span></p>
                <p>Medium: <span id="mediumCount"></span></p>
                <p>Large: <span id="largeCount"></span></p>
                <p>Extra Large: <span id="extraLargeCount"></span></p>
            </div>
            
            <div class="links">
                <a href="Dimensions Of Lockers.pdf">View Locker Size and Rent Chart</a>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="agree" name="agree" required>
                <label for="agree">I have read and agree to the <span id="eligibilityCriteria" style="text-decoration: underline;color: blue;cursor: pointer;">Eligibility Criteria</span> for locker allocation.</label>
            </div>
            <button type="submit" class="btn">Submit</button>
        </form>
    </div>

    <!-- Eligibility Modal -->
    <div class="modal" id="eligibilityModal">
        <div class="modal-content">
            <div class="modal-header">Eligibility Criteria</div>
            <div class="modal-body">
                <p>To be eligible for a locker, you must:</p>
                <ul>
                    <li>Online Locker Allotment e-Portal is available only for the customers having SB/CA accounts with District Co-Operative Bank.</li>
                    <li>New to bank Customers can avail the Online Locker facility after opening of SB/CA accounts by visiting the nearest Branch</li>
                    <li>Applicant should have registered Mobile number & e-mail ID to avail Online Locker Allotment facility.</li>
                    <li>Only Active accounts are eligible for digital allotment of lockers through this portal</li>
                    <li>Applicant has to provide Fixed Deposit as Security Deposit at branch for 1 years of Locker rent </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="close-btn" onclick="closeModal('eligibilityModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Result Modals -->
    <div class="modal" id="resultModal">
        <div class="modal-content">
            <div class="modal-header">Result</div>
            <div class="modal-body" id="resultMessage"></div>
            <div class="modal-footer">
                <button class="close-btn" onclick="closeModal('resultModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        function getBranches(districtId) {
            fetch(`<?php echo $_SERVER['PHP_SELF']; ?>?District_id=${districtId}`)
                .then(response => response.json())
                .then(data => {
                    const branchSelect = document.getElementById('branch');
                    branchSelect.innerHTML = '<option value="">Select Branch</option>'; // Clear previous options
                    data.forEach(branch => {
                        branchSelect.innerHTML += `<option value="${branch.Branch}">${branch.Branch}</option>`;
                    });
                });
        }

        function getLockers(branch) {
            fetch(`<?php echo $_SERVER['PHP_SELF']; ?>?Branch=${branch}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('smallCount').innerText = data.Small || 0;
                    document.getElementById('mediumCount').innerText = data.Medium || 0;
                    document.getElementById('largeCount').innerText = data.Large || 0;
                    document.getElementById('extraLargeCount').innerText = data.ExtraLarge || 0;
                    document.getElementById('lockerCount').style.display = 'block';
                });
        }

        document.getElementById('eligibilityCriteria').addEventListener('click', function() {
            openModal('eligibilityModal');
        });

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Display result messages in a modal
        <?php if ($accountNotFound): ?>
            document.getElementById('resultMessage').innerText = 'Account not found.';
            openModal('resultModal');
        <?php elseif ($lockerAlreadyAllotted): ?>
            document.getElementById('resultMessage').innerText = 'Locker already allotted for this account.';
            openModal('resultModal');
        <?php elseif ($success): ?>
            document.getElementById('resultMessage').innerText = 'Account verified successfully.';
            openModal('resultModal');
        <?php endif; ?>
    </script>
</body>
</html>
