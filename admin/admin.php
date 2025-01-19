<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset(); // Clear all session variables
    session_destroy(); // Destroy the session
    header('Location: login.php'); // Redirect to login page
    exit();
}

// Include database connection
include 'connection.php';

// Handle form submission
if (isset($_POST['add_sub_admin'])) {
    $user = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile_no = $_POST['mobile_no'];
    $bank_emp_no = $_POST['bank_emp_no'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO admin_users (username, name, email, mobile_no, bank_emp_no, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $user, $name, $email, $mobile_no, $bank_emp_no, $password);

    // Execute and check if successful
    if ($stmt->execute()) {
        $message = "Sub-Admin added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Set message in session for popup
    $_SESSION['message'] = $message;
    // Redirect to avoid form re-submission
    header("Location: admin.php");
    exit();
}

// Get message from session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Bank Locker Management - Admin Panel</title>
  <style>
    /* Your existing CSS styles */
    body {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      background-color: #f4f7fc;
      color: #666;
      font-family: "Montserrat", sans-serif;
      display: grid;
      grid-template-columns: 1fr;
      grid-template-rows: auto 1fr;
      grid-template-areas:
        "header"
        "main";
      height: 100vh;
    }

    .header {
      grid-area: header;
      background-color: #0adcd1;
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 30px 10px;
    }

    .header h2 {
      margin: 0;
      padding-left: 80px;
      font-size: 30px;
      font-weight: bolder;
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

    .main-content {
      grid-area: main;
      display: grid;
      grid-template-columns: 300px 1fr; /* Sidebar 300px wide */
      grid-template-areas: "sidebar main";
      height: 100%;
      overflow: auto;
    }

    #sidebar {
      grid-area: sidebar;
      background-color: #434447;
      color: #9799ab;
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: flex-start;
    }

    .sidebar-list {
      list-style: none;
      padding: 2px;
    }

    .sidebar-list-item {
      padding: 15px 0;
    }

    .sidebar-list-item a {
      text-decoration: none;
      color: #f0f0f0;
    }

    .sidebar-list-item a:hover {
      color: #d7780c;
    }

    .form-container {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
      display: none; /* Initially hidden */
    }

    .form-container h3 {
      margin-top: 0;
    }

    .form-container label {
      display: block;
      margin-bottom: 5px;
      color: #333;
    }

    .form-container input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-bottom: 15px;
    }

    .form-container button {
      background-color: #0adcd1;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    .form-container button:hover {
      background-color: #0a9b97;
    }

    .card {
      background-color: #fff;
      padding: 50px;
      border-radius: 10px;
      margin: 22px 10px;
      width: 350px;
      text-align: center;
    }

    .card:hover {
      background-color: rgb(221, 246, 248);
    }

    .card p {
      font-size: 18px;
      color: #333;
    }

    .card span {
      font-size: 30px;
      font-weight: bold;
      color: #067a91;
    }

    /* Popup styles */
    .popup {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      text-align: center;
    }

    .popup button {
      background-color: #0adcd1;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    .popup button:hover {
      background-color: #0a9b97;
    }

    .overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="header-left">
      <h2>District Co-operative Bank</h2>
    </div>
    <a href="admin.php?action=logout" class="logout-btn">Logout</a>
  </header>
  <!-- End Header -->

  <!-- Main content grid (Sidebar + Main area) -->
  <div class="main-content">
    <!-- Sidebar -->
    <aside id="sidebar">
      <ul class="sidebar-list">
        <li class="sidebar-list-item">
          <a href="lockerdetails.php">View Lockers Allotments</a>
        </li>
        <li class="sidebar-list-item">
          <a href="formverification.php">Booking Form Notifications </a>
        </li>
        <li class="sidebar-list-item">
          <a href="#" onclick="toggleForm(event)">Add Sub-Admin</a>
        </li>
      </ul>
    </aside>
    <!-- End Sidebar -->


      <!-- Form Container -->
      <div class="form-container" id="subadmin-form">
        <h3>Add Sub-Admin</h3>
        <form action="admin.php" method="POST">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required>
          <label for="name">Name</label>
          <input type="text" id="name" name="name" required>
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
          <label for="mobile_no">Mobile No</label>
          <input type="text" id="mobile_no" name="mobile_no" required>
          <label for="bank_emp_no">Bank Emp No</label>
          <input type="text" id="bank_emp_no" name="bank_emp_no" required>
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
          <button type="submit" name="add_sub_admin">Add Sub-Admin</button>
        </form>
      </div>

      <!-- Popup -->
      <div class="overlay" id="overlay"></div>
      <div class="popup" id="popup">
        <p><?php echo htmlspecialchars($message); ?></p>
        <button onclick="closePopup()">OK</button>
      </div>
    </main>
    <!-- End Main Content -->
  </div>

  <script>
    function toggleForm(event) {
      event.preventDefault(); // Prevent default link behavior
      var form = document.getElementById('subadmin-form');
      var mainContent = document.getElementById('main-content');
      if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        // Optionally clear other main content
        mainContent.querySelectorAll('.card').forEach(function(card) {
          card.style.display = 'none'; // Hide cards
        });
      } else {
        form.style.display = 'none';
        // Optionally re-show other main content
        mainContent.querySelectorAll('.card').forEach(function(card) {
          card.style.display = 'block'; // Show cards
        });
      }
    }

    function showPopup() {
      var overlay = document.getElementById('overlay');
      var popup = document.getElementById('popup');
      overlay.style.display = 'block';
      popup.style.display = 'block';
    }

    function closePopup() {
      var overlay = document.getElementById('overlay');
      var popup = document.getElementById('popup');
      overlay.style.display = 'none';
      popup.style.display = 'none';
    }

    // Show the popup if there is a message
    <?php if ($message): ?>
      showPopup();
    <?php endif; ?>
  </script>
</body>
</html>
