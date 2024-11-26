<?php
// Include database connection
include('db_connect.php');

// Define coin options for E-Football
$coinOptions = [
    "E-Football via Login" => [
        ["amount" => 1040, "price" => 20.00],
        ["amount" => 2130, "price" => 40.00],
        ["amount" => 3250, "price" => 60.00],
        ["amount" => 5700, "price" => 100.00],
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if required POST variables are set
    if (isset($_POST['email'], $_POST['password'], $_POST['package-name'], $_POST['amount'], $_POST['price'], $_POST['payment-method'], $_POST['phone'])) {
        // Capture form data
        $email = $_POST['email'];
        $password = $_POST['password']; // Store password as is (plain text)
        $package_name = $_POST['package-name'];
        $amount = $_POST['amount'];
        $price = $_POST['price'];
        $payment_method = $_POST['payment-method'];
        $phone_number = $_POST['phone'];

        // Insert email, password, and purchase data into the database
        $query = "INSERT INTO coinefootball (email, password, package_name, amount, price, payment_method, phone_number, status, created_at) 
                  VALUES ('$email', '$password', '$package_name', '$amount', '$price', '$payment_method', '$phone_number', 'pending', NOW())";

        if (mysqli_query($conn, $query)) {
            // After successful insertion, redirect to receipt page with email in query string
            header("Location: e-football_receipt.php?orderId=" . mysqli_insert_id($conn) . "&game=" . urlencode($package_name) . "&amount=" . $amount . "&price=" . $price . "&promoCode=" . urlencode($_POST['promo-code']) . "&email=" . urlencode($email));
            exit(); // Make sure to call exit() after header redirection
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
    <title>Gems Store - E-Football</title>
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
                <li><a href="#">E-Football Tools</a></li>
                <li><a href="register.php" class="register-btn">Register</a></li>
            </ul>
        </nav>
    </header>

    <section class="confirmation">
        <!-- Step 1: Fill Data -->
        <div class="step">
            <h2>1. Enter Email & Password</h2>
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Enter Email" required>
                <input type="password" name="password" placeholder="Enter Password" required>
        </div>

        <!-- Step 2: Choose Packages -->
        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <?php foreach ($coinOptions as $package => $coins): ?>
                    <button type="button" onclick="selectPackage('<?php echo $package; ?>')"><?php echo $package; ?></button>
                <?php endforeach; ?>
            </div>
            <p>This package is available for all regions.</p>
        </div>

        <!-- Step 3: Choose Coins -->
        <div class="step">
            <h2>3. Choose Coins</h2>
            <div class="coin-options" id="coin-options">
                <!-- Coin buttons will be dynamically added here -->
            </div>
        </div>

        <!-- Step 4: Choose Payment Methods -->
        <div class="step">
            <h2>4. Choose Payment Methods</h2>
            <p>Selected Price: RM <span id="price-display">0.00</span></p>
            <div class="payment-options">
                <button type="button" class="payment-button" id="payment-bank" onclick="selectPaymentMethod('bank')">Bank Transfer</button>
                <button type="button" class="payment-button" id="payment-tng" onclick="selectPaymentMethod('tng')">TNG e-Wallet</button>
                <button type="button" class="payment-button" id="payment-whatsapp" onclick="selectPaymentMethod('whatsapp')">WhatsApp</button>
            </div>
            <input type="hidden" name="payment-method" id="payment-method">
            <input type="hidden" name="package-name" id="package-name">
            <input type="hidden" name="amount" id="amount">
            <input type="hidden" name="price" id="price">
        </div>

        <!-- Step 5: Make a Purchase (Only One Buy Now Button) -->
        <div class="step">
            <h2>5. Make a Purchase</h2>
            <input type="text" name="phone" placeholder="+60123456789 (Required)" id="phone" required>
            <input type="text" id="promo-code" placeholder="Enter Promo Code">
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
            const pointsContainer = document.getElementById('coin-options');
            pointsContainer.innerHTML = '';

            const coinOptions = <?php echo json_encode($coinOptions); ?>;
            coinOptions[packageName].forEach(coin => {
                const button = document.createElement('button');
                button.textContent = `${coin.amount} Coins - RM ${coin.price}`;
                button.onclick = () => selectCoins(coin.price, coin.amount);
                pointsContainer.appendChild(button);
            });

            // Set the hidden field for package name
            document.getElementById('package-name').value = packageName;
        }

        function selectCoins(price, amount) {
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
    </script>

    <!-- Footer -->
    <footer class="footer">
        <div class="help">
            <h3>NEED HELP?</h3>
        </div>
        <a href="https://www.whatsapp.com/" class="whatsapp-btn" target="_blank">WhatsApp</a>
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
