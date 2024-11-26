<?php
// Include database connection
include('db_connect.php');

// Define CP options for Call of Duty Mobile
$cpOptions = [
    "Call of Duty: Mobile" => [
        ["amount" => 31, "price" => 1.50],
        ["amount" => 63, "price" => 3.00],
        ["amount" => 128, "price" => 6.00],
        ["amount" => 321, "price" => 15.00],
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if required POST variables are set
    if (isset($_POST['open-id'], $_POST['package-name'], $_POST['amount'], $_POST['price'], $_POST['payment-method'], $_POST['phone'], $_POST['email'])) {
        // Capture form data
        $open_id = $_POST['open-id'];
        $package_name = $_POST['package-name'];
        $amount = $_POST['amount'];
        $price = $_POST['price'];
        $payment_method = $_POST['payment-method'];
        $phone_number = $_POST['phone'];
        $email = $_POST['email'];
        $promo_code = $_POST['promo-code']; // Promo code field

        // Insert purchase data into cpcod table
        $query = "INSERT INTO cpcod (open_id, package_name, amount, price, payment_method, email, phone_number, status, created_at) 
                  VALUES ('$open_id', '$package_name', '$amount', '$price', '$payment_method', '$email', '$phone_number', 'pending', NOW())";

        if (mysqli_query($conn, $query)) {
            // After successful insertion, redirect to receipt page
            $orderId = mysqli_insert_id($conn); // Get the last inserted order ID
            header("Location: cod_receipt.php?orderId=$orderId&game=" . urlencode($package_name) . "&amount=$amount&price=$price&promoCode=" . urlencode($promo_code) . "&openId=$open_id");
            exit(); // Ensure no further code is executed after the redirection
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Required data not provided!";
    }
}
?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gems Store</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <img src="image/gems_store_4-removebg-preview.png" alt="GEMS STORE Logo" style="height: 60px;">
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Call of Duty Tools</a></li>
                <li><a href="register.php" class="register-btn">Register</a></li>
            </ul>
        </nav>
    </header>

    <section class="confirmation">
        <div class="step">
            <h2>1. Fill Data</h2>
            <form method="POST" action="">
                <input type="text" name="open-id" placeholder="Enter Open ID" required>
        </div>

        <!-- Step 2: Choose Packages -->
        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <button type="button" onclick="selectPackage('Call of Duty: Mobile')">Call of Duty: Mobile</button>
            </div>
            <p>This service is available to Indonesia account region only.</p>
        </div>

        <!-- Step 3: Choose CP -->
        <div class="step">
            <h2>3. Choose CP</h2>
            <div class="cp-options" id="cp-options">
                <!-- CP buttons will be dynamically added here -->
            </div>
        </div>

        <!-- Step 4: Choose Payment Methods -->
        <div class="step">
            <h2>4. Choose Payment Methods</h2>
            <p>Selected Price: RM <span id="price-display">0.00</span></p>
            <div class="payment-options">
                <button type="button" class="payment-button" onclick="selectPaymentMethod('e-wallet')">E-Wallet</button>
                <button type="button" class="payment-button" onclick="selectPaymentMethod('bank')">Bank Transfer</button>
                <button type="button" class="payment-button" onclick="selectPaymentMethod('whatsapp')">WhatsApp</button>
            </div>
            <input type="hidden" name="payment-method" id="payment-method">
            <input type="hidden" name="package-name" id="package-name">
            <input type="hidden" name="amount" id="amount">
            <input type="hidden" name="price" id="price">
        </div>

        <!-- Step 5: Make a Purchase -->
        <div class="step">
            <h2>5. Make a Purchase</h2>
            <input type="email" name="email" placeholder="your@email.com (Optional)">
            <input type="text" name="phone" placeholder="+60123456789 (Required)" required>
            <input type="text" id="promo-code" name="promo-code" placeholder="Enter Promo Code">
            <button class="apply-code" onclick="applyPromoCode()">Apply Promo Code</button>
            <p>Select product: <strong id="selected-product">RM 0.00</strong></p>
            <button class="buy-now" type="submit">Buy Now</button>
        </div>
        </form>
    </section>

    <script>
        let selectedPackage = '';
        let selectedAmount = 0;
        let selectedPrice = 0;

        function selectPackage(packageName) {
            selectedPackage = packageName;
            const cpContainer = document.getElementById('cp-options');
            cpContainer.innerHTML = '';

            const cpOptions = <?php echo json_encode($cpOptions); ?>;
            cpOptions[packageName].forEach(cp => {
                const button = document.createElement('button');
                button.textContent = `${cp.amount} CP - RM ${cp.price}`;
                button.onclick = () => selectCP(cp.price, cp.amount);
                cpContainer.appendChild(button);
            });

            // Set the hidden field for package name
            document.getElementById('package-name').value = packageName;
        }

        function selectCP(price, amount) {
            selectedPrice = price;
            selectedAmount = amount;

            // Update the hidden fields
            document.getElementById('price').value = price;
            document.getElementById('amount').value = amount;

            document.getElementById('price-display').textContent = price.toFixed(2);
            document.getElementById('selected-product').textContent = `RM ${price.toFixed(2)}`;
        }

        function selectPaymentMethod(method) {
            // Set the hidden field for payment method
            document.getElementById('payment-method').value = method;
        }

        function applyPromoCode() {
            const promoCode = document.getElementById('promo-code').value;
            let discount = 0;

            if (promoCode === "DISCOUNT10") {
                discount = selectedPrice * 0.10;
                alert("Promo code applied! You get a 10% discount.");
            } else {
                alert("Invalid promo code.");
            }

            const finalPrice = selectedPrice - discount;
            document.getElementById('selected-product').textContent = `RM ${finalPrice.toFixed(2)}`;
            document.getElementById('price-display').textContent = `RM ${finalPrice.toFixed(2)}`;
        }
    </script>

    <!-- Footer -->
    <footer class="footer">
        <div class="help">
            <h3>NEED HELP?</h3>
            <a href="https://www.whatsapp.com/" class="whatsapp-btn" target="_blank">WhatsApp</a>
        </div>
        <div class="social-media">
            <a href="https://www.facebook.com/">Facebook</a>
            <a href="https://www.instagram.com/">Instagram</a>
        </div>
        <p>© Copyright Gems Store 2024, All Rights Reserved.</p>
        <p>
            <a href="terms.html" class="terms">Terms & Conditions</a> | 
            <a href="privacy-policy.html" class="privacy-policy">Privacy Policy</a>
        </p>
    </footer>
</body>
</html>
