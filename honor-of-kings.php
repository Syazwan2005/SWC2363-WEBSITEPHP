<?php
// Include database connection
include('db_connect.php');

// Initialize the token options for Honor of Kings
$tokenOptions = [
    "Honor of Kings" => [
        ["amount" => 8, "price" => 0.30],
        ["amount" => 16, "price" => 0.50],
        ["amount" => 23, "price" => 1.25],
        ["amount" => 80, "price" => 3.75],
        ["amount" => 240, "price" => 10.50],
        ["amount" => 400, "price" => 20.75],
        ["amount" => 560, "price" => 30.90],
        ["amount" => 800, "price" => 50.40],
        ["amount" => 1200, "price" => 75.85],
        ["amount" => 2400, "price" => 150.30],
        ["amount" => 4000, "price" => 250.75],
        ["amount" => 8000, "price" => 500.60],
        ["amount" => 13740, "price" => 600.45],
        ["amount" => 18320, "price" => 711.00]
    ],
    "Honor of Kings via Login" => [
        ["amount" => 830, "price" => 45.00],
        ["amount" => 1245, "price" => 55.00],
        ["amount" => 2508, "price" => 125.00],
        ["amount" => 4180, "price" => 200.00],
        ["amount" => 8360, "price" => 340.00]
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['player-id'], $_POST['package-name'], $_POST['amount'], $_POST['price'], $_POST['payment-method'], $_POST['phone'], $_POST['email'])) {
        $player_id = $_POST['player-id'];
        $package_name = $_POST['package-name'];
        $amount = $_POST['amount'];
        $price = $_POST['price'];
        $payment_method = $_POST['payment-method'];
        $phone_number = $_POST['phone'];
        $email = $_POST['email'];

        $query = "INSERT INTO tokenhok (player_id, package_name, amount, price, payment_method, phone_number, email, status) 
                  VALUES ('$player_id', '$package_name', '$amount', '$price', '$payment_method', '$phone_number', '$email', 'pending')";

        if (mysqli_query($conn, $query)) {
            header("Location: hok_receipt.php?orderId=" . mysqli_insert_id($conn) . "&game=" . urlencode($package_name) . "&amount=" . $amount . "&price=" . $price . "&promoCode=" . urlencode($_POST['promo-code']) . "&playerId=" . $player_id);
            exit();
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
                <li><a href="#">Honor of Kings</a></li>
                <li><a href="register.php" class="register-btn">Register</a></li>
            </ul>
        </nav>
    </header>

    <section class="confirmation">
        <!-- Step 1: Fill Data -->
        <div class="step">
            <h2>1. Fill Data</h2>
            <form method="POST" action="">
                <input type="text" name="player-id" placeholder="Enter Player ID" required>
        </div>

        <!-- Step 2: Choose Packages -->
        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <?php foreach ($tokenOptions as $package => $tokens): ?>
                    <button type="button" onclick="selectPackage('<?php echo $package; ?>')"><?php echo $package; ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Step 3: Choose Tokens -->
        <div class="step">
            <h2>3. Choose Tokens</h2>
            <div class="token-options" id="token-options">
                <!-- Token buttons will be dynamically added here -->
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

        <!-- Step 5: Make a Purchase -->
        <div class="step">
            <h2>5. Make a Purchase</h2>
            <input type="email" name="email" placeholder="your@email.com (Optional)" id="email">
            <input type="text" name="phone" placeholder="+60123456789 (Required)" id="phone" required>
            <input type="text" id="promo-code" placeholder="Enter Promo Code">
            <button type="button" class="apply-code" onclick="applyPromoCode()">Apply Promo Code</button>
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
            const pointsContainer = document.getElementById('token-options');
            pointsContainer.innerHTML = '';

            const tokenOptions = <?php echo json_encode($tokenOptions); ?>;
            tokenOptions[packageName].forEach(token => {
                const button = document.createElement('button');
                button.textContent = `${token.amount} Tokens - RM ${token.price}`;
                button.onclick = () => selectToken(token.price, token.amount);
                pointsContainer.appendChild(button);
            });

            // Set the hidden field for package name
            document.getElementById('package-name').value = packageName;
        }

        function selectToken(price, amount) {
            selectedPrice = price;
            selectedAmount = amount;

            // Update the hidden fields
            document.getElementById('price').value = price;
            document.getElementById('amount').value = amount;

            document.getElementById('price-display').textContent = price.toFixed(2);
        }

        function selectPaymentMethod(method) {
            // Highlight the selected payment method button
            const buttons = document.querySelectorAll('.payment-button');
            buttons.forEach(button => button.classList.remove('selected'));

            const selectedButton = document.getElementById('payment-' + method);
            selectedButton.classList.add('selected');

            // Set the hidden input value for payment method
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
