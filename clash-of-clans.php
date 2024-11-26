<?php
// Include database connection
include('db_connect.php');

// Define Gems options for Clash of Clans
$gemsOptions = [
    "Clash of Clans" => [
        ["amount" => 88, "price" => 4.00],
        ["amount" => 550, "price" => 25.00],
        ["amount" => 1320, "price" => 75.00],
        ["amount" => 2500, "price" => 150.00]
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if required POST variables are set
    if (isset($_POST['player-tag'], $_POST['package-name'], $_POST['amount'], $_POST['price'], $_POST['payment-method'], $_POST['phone'], $_POST['email'])) {
        // Capture form data
        $player_tag = $_POST['player-tag'];
        $package_name = $_POST['package-name'];
        $amount = $_POST['amount'];
        $price = $_POST['price'];
        $payment_method = $_POST['payment-method'];
        $phone_number = $_POST['phone'];
        $email = $_POST['email'];

        // Insert purchase data into gemscoc table
        $query = "INSERT INTO gemscoc (player_tag, package_name, amount, price, payment_method, email, phone_number, status, created_at) 
                  VALUES ('$player_tag', '$package_name', '$amount', '$price', '$payment_method', '$email', '$phone_number', 'pending', NOW())";

        if (mysqli_query($conn, $query)) {
            // After successful insertion, redirect to receipt page with playerTag
            header("Location: coc_receipt.php?orderId=" . mysqli_insert_id($conn) . "&game=" . urlencode($package_name) . "&amount=" . $amount . "&price=" . $price . "&playerTag=" . urlencode($player_tag));
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
                <li><a href="#">Clash of Clans Tools</a></li>
                <li><a href="register.php" class="register-btn">Register</a></li>
            </ul>
        </nav>
    </header>

    <section class="confirmation">
        <!-- Step 1: Fill Data -->
        <div class="step">
            <h2>1. Fill Data</h2>
            <form method="POST" action="">
                <input type="text" name="player-tag" placeholder="Enter Player Tag" required>
        </div>

        <!-- Step 2: Choose Packages -->
        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <?php foreach ($gemsOptions as $package => $gemsList): ?>
                    <button type="button" onclick="selectPackage('<?php echo $package; ?>')"><?php echo $package; ?></button>
                <?php endforeach; ?>
            </div>
            <p>This package is available for all account regions.</p>
        </div>

        <!-- Step 3: Choose Gems -->
        <div class="step">
            <h2>3. Choose Gems</h2>
            <div class="gems-options" id="gems-options">
                <!-- Gems buttons will be dynamically added here -->
            </div>
        </div>

        <!-- Step 4: Choose Payment Methods -->
        <div class="step">
            <h2>4. Choose Payment Methods</h2>
            <p>Selected Price: RM <span id="price-display">0.00</span></p>
            <div class="payment-options">
                <button type="button" class="payment-button" id="payment-bank" onclick="selectPaymentMethod('bank')">Bank Transfer</button>
                <button type="button" class="payment-button" id="payment-ewallet" onclick="selectPaymentMethod('ewallet')">E-Wallet</button>
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
            <input type="email" name="email" placeholder="your@email.com (Optional)">
            <input type="text" name="phone" placeholder="+60123456789 (Required)" required>
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
            const gemsContainer = document.getElementById('gems-options');
            gemsContainer.innerHTML = '';

            const gemsOptions = <?php echo json_encode($gemsOptions); ?>;
            gemsOptions[packageName].forEach(gem => {
                const button = document.createElement('button');
                button.textContent = `${gem.amount} Gems - RM ${gem.price}`;
                button.onclick = () => selectGems(gem.price, gem.amount);
                gemsContainer.appendChild(button);
            });

            // Set the hidden field for package name
            document.getElementById('package-name').value = packageName;
        }

        function selectGems(price, amount) {
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

    <footer class="footer">
        <div class="help">
            <h3>NEED HELP?</h3>
            <a href="https://www.whatsapp.com/" class="whatsapp-btn" target="_blank">WhatsApp</a>
        </div>
        <div class="social-media">
            <a href="https://www.facebook.com/">Facebook</a>
            <a href="https://www.instagram.com/">Instagram</a>
        </div>
    </footer>
</body>
</html>
