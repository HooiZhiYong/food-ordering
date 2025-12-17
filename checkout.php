<?php
// checkout.php
session_start();
include('config/db_connect.php');

// 1. Redirect if not logged in or Cart is empty
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit();
}

$user_id = $_SESSION['user_id'];
// Added 'paypal_transaction_id' to capture the real ID from PayPal
$errors = array('address'=>'', 'payment'=>'', 'card'=>'', 'paypal'=>'');

// Calculate Total (Needed early for PayPal JS)
$total = 0;
foreach($_SESSION['cart'] as $item) {
    $total += ($item['price'] * $item['quantity']);
}

// Determine which payment method was selected (Default to COD)
$selected_payment = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Cash On Delivery';
$entered_card_num = isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : '';
$entered_expiry   = isset($_POST['card_expiry']) ? htmlspecialchars($_POST['card_expiry']) : '';
$entered_cvc      = isset($_POST['card_cvc']) ? htmlspecialchars($_POST['card_cvc']) : '';


// 2. Handle Order Submission
if (isset($_POST['place_order'])) {
    
    // Get Input
    $address = htmlspecialchars($_POST['address']);
    $payment_method = $_POST['payment_method'];
    // For security, strictly use the server-calculated total
    $total_amount = $total; 

    // Basic Validation
    if(empty($address)){
        $errors['address'] = "Delivery address is required.";
    }

    // --- PAYMENT SPECIFIC VALIDATION ---
    
    // A. Credit Card Validation
    if($payment_method == 'Credit Card'){
        $card_num = $_POST['card_number'];
        $card_expiry = $_POST['card_expiry']; // Format: MM/YY
        $card_cvc = $_POST['card_cvc'];

        // Keep inputs sticky
        $entered_card_num = htmlspecialchars($card_num); 
        $entered_expiry   = htmlspecialchars($card_expiry); 
        $entered_cvc      = htmlspecialchars($card_cvc); 

        //Validate Number (16 digits)
        if(!preg_match('/^[0-9]{16}$/', $card_num)){
            $errors['card'] = "Card number must be 16 digits.";
        }
        
        //Validate Expiry (MM/YY)
        elseif(!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $card_expiry)){
            $errors['card'] = "Expiry must be in MM/YY format (e.g. 12/25).";
        }

        //Validate CVC (3 digits)
        elseif(!preg_match('/^[0-9]{3}$/', $card_cvc)){
            $errors['card'] = "CVC must be 3 digits.";
        }
        else {
            // Logic to check if card is expired
            $parts = explode('/', $card_expiry);
            $month = (int)$parts[0];
            $year  = (int)("20" . $parts[1]); // Convert "25" to "2025"
            
            $current_year = (int)date('Y');
            $current_month = (int)date('m');

            if ($year < $current_year || ($year == $current_year && $month < $current_month)) {
                $errors['card'] = "Card has expired.";
            }
        }
    }

    // B. PayPal Validation
    if($payment_method == 'PayPal'){
        if(empty($_POST['paypal_transaction_id'])){
            $errors['paypal'] = "PayPal payment failed or was cancelled.";
        }
    }

    // If no errors, SAVE TO DATABASE
    if(!array_filter($errors)){
        
        // A. Insert into ORDERS table
        $sql_order = "INSERT INTO orders (user_id, total_amount, order_status, delivery_address) VALUES (?, ?, 'pending', ?)";
        $stmt = $conn->prepare($sql_order);
        $stmt->bind_param("ids", $user_id, $total_amount, $address);
        
        if($stmt->execute()){
            
            // B. Get the ID of the order we just created
            $new_order_id = $conn->insert_id;

            // C. Insert into ORDER_DETAILS table (Loop through cart)
            $sql_details = "INSERT INTO order_details (order_id, food_id, quantity, price_each) VALUES (?, ?, ?, ?)";
            $stmt_detail = $conn->prepare($sql_details);

            foreach($_SESSION['cart'] as $food_id => $item){
                $stmt_detail->bind_param("iiid", $new_order_id, $food_id, $item['quantity'], $item['price']);
                $stmt_detail->execute();
            }

            // D. CLEAR THE CART
            unset($_SESSION['cart']);

            // E. Redirect to Order History
            $tx_id = isset($_POST['paypal_transaction_id']) ? " (TxID: " . $_POST['paypal_transaction_id'] . ")" : "";
            echo "<script>alert('Payment Successful via $payment_method!$tx_id Thank you for your order.'); window.location='my_orders.php';</script>";
            exit();

        } else {
            echo "SQL Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Food Ordering</title>
    <link rel="stylesheet" href="assets/member_style.css">
    
    <!-- PAYPAL SDK: Replace 'sb' with your Sandbox Client ID -->
    <script src="https://www.paypal.com/sdk/js?client-id=sb&currency=MYR"></script>

    <style>
        .checkout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .summary-box { background: #fff8e1; padding: 20px; border-radius: 5px; border: 1px solid #ffeaa7; }
        .payment-box { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .total-row { font-size: 1.5em; font-weight: bold; border-top: 2px solid #e67e22; margin-top: 10px; padding-top: 10px; color: #d35400; }
        
        .payment-details { display: none; margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px; border: 1px solid #eee;}
        
        #paypal-button-container { margin-top: 15px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Max Star - Check Out</h2></div>
        <div class="nav-links">
            <a href="cart.php">Back to Cart</a>
        </div>
    </nav>

    <div class="container">
        <h1>Checkout & Payment</h1>

        <form id="checkout-form" action="checkout.php" method="POST" class="checkout-grid">
            
            <input type="hidden" name="paypal_transaction_id" id="paypal_transaction_id">
            <input type="hidden" name="place_order" value="1">

            <!-- LEFT COLUMN: Address & Payment -->
            <div class="payment-box">
                <h3>1. Delivery Information</h3>
                <div class="form-group">
                    <label>Delivery Address:</label>
                    <textarea name="address" id="address-input" rows="3" required placeholder="Enter full address..."><?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?></textarea>
                    <div class="error"><?php echo $errors['address']; ?></div>
                </div>

                <h3>2. Payment Method</h3>
                <div class="form-group">
                    <label>
                        <input type="radio" name="payment_method" value="Cash On Delivery" onclick="togglePayment(this.value)" <?php echo ($selected_payment == 'Cash On Delivery') ? 'checked' : ''; ?>> 
                        Cash On Delivery (COD)
                    </label>
                    
                    <label>
                        <input type="radio" name="payment_method" value="Credit Card" onclick="togglePayment(this.value)" <?php echo ($selected_payment == 'Credit Card') ? 'checked' : ''; ?>> 
                        Credit / Debit Card
                    </label>

                    <label>
                        <input type="radio" name="payment_method" value="PayPal" onclick="togglePayment(this.value)" <?php echo ($selected_payment == 'PayPal') ? 'checked' : ''; ?>> 
                        PayPal
                    </label>
                </div>

                <!-- A. Fake Credit Card Section -->
                <div id="card-section" class="payment-details">
                    <h4>Enter Card Details</h4>
                    <div class="form-group">
                        <label>Card Number (16 digits):</label>
                        <input type="text" name="card_number" id="card_input" placeholder="1234 5678 1234 5678" maxlength="16" value="<?php echo $entered_card_num; ?>">
                    </div>
                    
                    <div class="form-group" style="display:flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Expiry (MM/YY):</label>
                            <!-- Added name='card_expiry' -->
                            <input type="text" name="card_expiry" id="expiry_input" placeholder="12/25" maxlength="5" value="<?php echo $entered_expiry; ?>">
                        </div>
                        <div style="flex: 1;">
                            <label>CVC (3 digits):</label>
                            <!-- Added name='card_cvc' -->
                            <input type="text" name="card_cvc" id="cvc_input" placeholder="123" maxlength="3" value="<?php echo $entered_cvc; ?>">
                        </div>
                    </div>
                    <div class="error"><?php echo $errors['card']; ?></div>
                </div>

                <!-- B. Real PayPal Section -->
                <div id="paypal-section" class="payment-details">
                    <p style="margin-bottom: 10px; color: #555;">Complete your payment securely with PayPal.</p>
                    <div id="paypal-button-container"></div>
                    <div class="error"><?php echo $errors['paypal']; ?></div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Order Summary -->
            <div class="summary-box">
                <h3>Order Summary</h3>
                <br>
                <?php foreach($_SESSION['cart'] as $item): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom: 10px;">
                        <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                        <span>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                
                <div class="total-row">
                    Total: RM <?php echo number_format($total, 2); ?>
                </div>
                
                <br><br>
                
                <button type="submit" id="main-submit-btn" class="btn" style="width: 100%; background-color: #ff9f43; font-size: 1.2em;">
                    Confirm & Pay
                </button>
            </div>

        </form>
    </div>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{ amount: { value: '<?php echo $total; ?>' } }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    document.getElementById('paypal_transaction_id').value = details.id;
                    document.getElementById('checkout-form').submit();
                });
            }
        }).render('#paypal-button-container');

        function togglePayment(method) {
            var cardSection = document.getElementById('card-section');
            var paypalSection = document.getElementById('paypal-section');
            var mainBtn = document.getElementById('main-submit-btn');

            // Reset requirements
            document.getElementById('card_input').required = false;
            document.getElementById('expiry_input').required = false;
            document.getElementById('cvc_input').required = false;

            if(method === 'Credit Card') {
                cardSection.style.display = 'block';
                paypalSection.style.display = 'none';
                mainBtn.style.display = 'block'; 
                
                document.getElementById('card_input').required = true;
                document.getElementById('expiry_input').required = true;
                document.getElementById('cvc_input').required = true;
            } 
            else if (method === 'PayPal') {
                cardSection.style.display = 'none';
                paypalSection.style.display = 'block';
                mainBtn.style.display = 'none'; 
            } 
            else {
                cardSection.style.display = 'none';
                paypalSection.style.display = 'none';
                mainBtn.style.display = 'block'; 
            }
        }

        window.onload = function() {
            togglePayment('<?php echo $selected_payment; ?>');
        };
    </script>
</body>
</html>