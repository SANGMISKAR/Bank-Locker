<?php
// Start session and connect to the database
include 'connection.php';

$rows = [];

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['accountno'])) {
    $accountNo = $_POST['accountno'];

    // Query to fetch locker details based on account number and verified status
    $sql = "SELECT `accountno`, `district`, `branch`, `Name`, `Email_id`, `MobileNo`, `Occupation`, `lockerType`, 
            `nominee_name`, `relation_with_nominee`, `paymentMode`, `lockerCharges`, `totalAmount`, `booking_date`, `status`, 
            `locker_no`, `locker_key_no`
            FROM `locker_bookings`
            WHERE `accountno` = ? AND `status` = 'verified'";  // Only fetch verified records

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $accountNo);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    // // Redirect to avoid form resubmission on refresh
    // header("Location: " . $_SERVER['PHP_SELF']);
    // exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Bank Locker Management - Search Page</title>
  <link rel="stylesheet" type="text/css" href="index.css" media="screen" /> 
</head>
<body>
  <!-- Header / Navbar -->
  <header class="header">
    <h2>District Co-operative Bank</h2>
    <nav class="navbar">
      <a href="#">Home</a>
      <a href="#search-container">Assign Locker</a>
      <a href="login.php">Banker</a>
    </nav>
  </header>
  
  <!-- Main Content -->
  <main class="main-container">
    <!-- Image Section -->
    <div class="image-container">
      <img src="22.jpg" alt="Bank Image">
    </div>

    <!-- Search Assigned Lockers Section -->
    <div class="search-container" id="search-container">
      <form method="post" action="">
        <input type="text" name="accountno" id="accountno" placeholder="Enter locker number or account number" required>
        <button type="submit">Search Locker Details</button>
      </form>
    </div>

    <!-- Result Section -->
    <div class="result-container">
      <?php if (!empty($rows)): ?>
        <table>
          <thead>
            <tr>
              <th>Account No</th>
              <th>District</th>
              <th>Branch</th>
              <th>Name</th>
              <th>Email ID</th>
              <th>Mobile No</th>
              <th>Occupation</th>
              <th>Locker Type</th>
              <th>Nominee Name</th>
              <th>Relation with Nominee</th>
              <th>Payment Mode</th>
              <th>Locker Charges</th>
              <th>Total Amount</th>
              <th>Booking Date</th>
              <th>Status</th>
              <th>Locker No</th>
              
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['accountno']) ?></td>
                <td><?= htmlspecialchars($row['district']) ?></td>
                <td><?= htmlspecialchars($row['branch']) ?></td>
                <td><?= htmlspecialchars($row['Name']) ?></td>
                <td><?= htmlspecialchars($row['Email_id']) ?></td>
                <td><?= htmlspecialchars($row['MobileNo']) ?></td>
                <td><?= htmlspecialchars($row['Occupation']) ?></td>
                <td><?= htmlspecialchars($row['lockerType']) ?></td>
                <td><?= htmlspecialchars($row['nominee_name']) ?></td>
                <td><?= htmlspecialchars($row['relation_with_nominee']) ?></td>
                <td><?= htmlspecialchars($row['paymentMode']) ?></td>
                <td><?= htmlspecialchars($row['lockerCharges']) ?></td>
                <td><?= htmlspecialchars($row['totalAmount']) ?></td>
                <td><?= htmlspecialchars($row['booking_date']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['locker_no']) ?></td>
                
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
        <h1>No data found for the provided account number or account is not verified.</h1>
      <?php endif; ?>
    </div>
  </main>

  <!-- Clear form on page reload -->
  <script>
    window.onload = function() {
      document.getElementById("accountno").value = ''; // Clear input field on page reload
    };
  </script>

  <!-- Footer -->
  <footer class="footer">
    <p>&copy; 2024 Bank Name. All Rights Reserved.</p>
  </footer>
</body>
</html>
