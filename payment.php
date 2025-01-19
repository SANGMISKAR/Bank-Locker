<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store payment mode and calculated amounts in session
    $_SESSION['paymentMode'] = $_POST['paymentMode'];
    $_SESSION['lockerCharges'] = $_POST['lockerCharges'];
    $_SESSION['processingFees'] = $_POST['processingFees'];
    $_SESSION['gstAmount'] = $_POST['gstAmount'];
    $_SESSION['totalAmount'] = $_POST['totalAmount'];

    // Redirect to storedata.php to insert everything into the database
    header("Location: storedata.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Mode and Details</title>
    <style>
        /* Your CSS styles */
        body {
            font-family: "Cambria", serif;
            background-color: antiquewhite;
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: flex-start;
            min-height: 100vh;
        }

        .container {
            background: linear-gradient(to left, #ffffff, #eeeded);
            border-radius: 20px;
            padding: 10px 50px;
            box-sizing: border-box;
            width: 100%;
            max-width: 80%;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .container h1 {
            font-size: 32px;
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
        .form-group select {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #828282;
            border-radius: 8px;
        }

        .payment-details {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }

        .payment-details h2 {
            font-size: 24px;
            color: #ff6700;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #ff6700;
            color: white;
            padding: 15px 25px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn:hover {
            background-color: #0c0e90;
        }

        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            padding-top: 100px; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4); 
        }

        .modal-content {
            background-color: white;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 10px;
            text-align: center;
        }
    </style>
    <script>
        function updatePayment() {
            let lockerType = document.getElementById("lockerType").value;
            let paymentMode = document.getElementById("paymentMode").value;
            let lockerCharges = 0;

            switch (lockerType) {
                case 'Small':
                    lockerCharges = 1500;
                    break;
                case 'Medium':
                    lockerCharges = 2800;
                    break;
                case 'Large':
                    lockerCharges = 3500;
                    break;
                case 'Extra-Large':
                    lockerCharges = 4800;
                    break;
            }

            switch (paymentMode) {
                case 'Monthly':
                    lockerCharges /= 12;
                    break;
                case 'ecs':
                    lockerCharges *= 0.9;
                    break;
            }

            let processingFees = 300;
            let gstAmount = lockerCharges * 0.18;
            let totalAmount = lockerCharges + processingFees + gstAmount;

            document.getElementById("lockerCharges").value = lockerCharges.toFixed(2);
            document.getElementById("processingFees").value = processingFees.toFixed(2);
            document.getElementById("gstAmount").value = gstAmount.toFixed(2);
            document.getElementById("totalAmount").value = totalAmount.toFixed(2);
        }

        function confirmSubmission() {
            document.getElementById('paymentForm').submit();
        }

        window.onload = function() {
            updatePayment();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Payment Details</h1>
        <form id="paymentForm" method="post">
            <input type="hidden" id="lockerType" name="lockerType" value="<?php echo htmlspecialchars($_SESSION['lockerType']); ?>">

            <div class="form-group">
                <label for="paymentMode">Payment Mode:</label>
                <select id="paymentMode" name="paymentMode" required onchange="updatePayment()">
                    <option value="Annually">Annually</option>
                    <option value="Monthly">Monthly</option>
                    
                </select>
            </div>

            <div class="payment-details">
                <h2>Payment Breakdown</h2>
                <div class="form-group">
                    <label for="lockerCharges">Locker Charges:</label>
                    <input type="text" id="lockerCharges" name="lockerCharges" readonly>
                </div>
                <div class="form-group">
                    <label for="processingFees">Processing Fees:</label>
                    <input type="text" id="processingFees" name="processingFees" readonly>
                </div>
                <div class="form-group">
                    <label for="gstAmount">GST (18%):</label>
                    <input type="text" id="gstAmount" name="gstAmount" readonly>
                </div>
                <div class="form-group">
                    <label for="totalAmount">Total Amount:</label>
                    <input type="text" id="totalAmount" name="totalAmount" readonly>
                </div>
            </div>

            <button type="button" class="btn" onclick="confirmSubmission()">Proceed to Payment</button>
        </form>
    </div>
</body>
</html>
