<?php
// Include database connection
include('db_connect.php');

// Initialize the diamond options
$diamondOptions = [
    "MLBB Malaysia" => [
        ["amount" => 11, "price" => 1.00],
        ["amount" => 22, "price" => 2.00],
        ["amount" => 33, "price" => 3.00],
        ["amount" => 56, "price" => 5.00],
        ["amount" => 112, "price" => 10.00],
        ["amount" => 168, "price" => 15.00],
        ["amount" => 224, "price" => 20.00],
        ["amount" => 336, "price" => 30.00],
        ["amount" => 448, "price" => 40.00],
        ["amount" => 504, "price" => 45.00],
        ["amount" => 626, "price" => 55.00],
        ["amount" => 794, "price" => 70.00],
        ["amount" => 1018, "price" => 90.00],
        ["amount" => 1275, "price" => 110.00],
        ["amount" => 1443, "price" => 130.00],
        ["amount" => 1733, "price" => 150.00]
    ],
    "MLBB Indonesia" => [
        ["amount" => 5, "price" => 0.50],
        ["amount" => 12, "price" => 1.20],
        ["amount" => 19, "price" => 1.90],
        ["amount" => 28, "price" => 2.80],
        ["amount" => 33, "price" => 3.30],
        ["amount" => 50, "price" => 5.00],
        ["amount" => 85, "price" => 7.50],
        ["amount" => 184, "price" => 16.00],
        ["amount" => 366, "price" => 32.00],
        ["amount" => 568, "price" => 50.00],
        ["amount" => 758, "price" => 65.00],
        ["amount" => 875, "price" => 75.00],
        ["amount" => 2010, "price" => 160.00],
        ["amount" => 4026, "price" => 320.00],
        ["amount" => 4830, "price" => 380.00]
    ],
    "MLBB via Login" => [
        ["amount" => 1155, "price" => 50.00],
        ["amount" => 1765, "price" => 75.00],
        ["amount" => 2330, "price" => 100.00],
        ["amount" => 2975, "price" => 125.00],
        ["amount" => 3540, "price" => 150.00],
        ["amount" => 4130, "price" => 175.00],
        ["amount" => 4740, "price" => 200.00],
        ["amount" => 5305, "price" => 225.00],
        ["amount" => 6000, "price" => 250.00],
        ["amount" => 6565, "price" => 275.00],
        ["amount" => 7155, "price" => 300.00],
        ["amount" => 7765, "price" => 325.00],
        ["amount" => 8330, "price" => 350.00],
        ["amount" => 8975, "price" => 375.00],
        ["amount" => 9540, "price" => 400.00],
        ["amount" => 10130, "price" => 425.00],
        ["amount" => 10740, "price" => 450.00],
        ["amount" => 12000, "price" => 500.00]
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if required POST variables are set
    if (isset($_POST['user-id'], $_POST['server-id'], $_POST['package-name'], $_POST['amount'], $_POST['price'], $_POST['payment-method'], $_POST['phone'], $_POST['email'])) {
        // Capture form data
        $user_id = $_POST['user-id'];
        $server_id = $_POST['server-id'];
        $package_name = $_POST['package-name'];
        $amount = $_POST['amount'];
        $price = $_POST['price'];
        $payment_method = $_POST['payment-method'];
        $phone_number = $_POST['phone'];
        $email = $_POST['email'];

        // Insert purchase data into diamondsml table
        $query = "INSERT INTO diamondsml (user_id, server_id, package_name, amount, price, payment_method, phone_number, email, status) 
                  VALUES ('$user_id', '$server_id', '$package_name', '$amount', '$price', '$payment_method', '$phone_number', '$email', 'pending')";

        if (mysqli_query($conn, $query)) {
            // After successful insertion, redirect to receipt page
            header("Location: mlbb_receipt.php?orderId=" . mysqli_insert_id($conn) . "&game=" . urlencode($package_name) . "&amount=" . $amount . "&price=" . $price . "&promoCode=" . urlencode($_POST['promo-code']) . "&userId=" . $user_id . "&serverId=" . $server_id);
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
            </ul>
        </nav>
    </header>

    <section class="confirmation">
        <!-- Step 1: Fill Data -->
        <div class="step">
            <h2>1. Fill Data</h2>
            <form method="POST" action="">
                <input type="text" name="user-id" placeholder="Enter User ID" required>
                <input type="text" name="server-id" placeholder="Enter Server ID" required>
        </div>

        <!-- Step 2: Choose Packages -->
        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <?php foreach ($diamondOptions as $package => $diamonds): ?>
                    <button type="button" onclick="selectPackage('<?php echo $package; ?>')"><?php echo $package; ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Step 3: Choose Diamonds -->
        <div class="step">
            <h2>3. Choose Diamonds</h2>
            <div class="diamond-options" id="diamond-options">
                <!-- Diamond buttons will be dynamically added here -->
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
            <input type="email" name="email" placeholder="your@email.com (Optional)" id="email">
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
            const pointsContainer = document.getElementById('diamond-options');
            pointsContainer.innerHTML = '';

            const diamondOptions = <?php echo json_encode($diamondOptions); ?>;
            diamondOptions[packageName].forEach(points => {
                const button = document.createElement('button');
                button.textContent = `${points.amount} Diamonds - RM ${points.price}`;
                button.onclick = () => selectPoints(points.price, points.amount);
                pointsContainer.appendChild(button);
            });

            // Set the hidden field for package name
            document.getElementById('package-name').value = packageName;
        }

        function selectPoints(price, amount) {
            selectedPrice = price;
            selectedAmount = amount;

            // Update the hidden fields
            document.getElementById('price').value = price;
            document.getElementById('amount').value = amount;

            document.getElementById('price-display').textContent = price.toFixed(2);
            document.getElementById('selected-product').textContent = `RM ${price.toFixed(2)}`;
        }

        // Function to handle payment method selection
    function selectPaymentMethod(method) {
        // Highlight the selected payment method button
        const buttons = document.querySelectorAll('.payment-button');
        buttons.forEach(button => button.classList.remove('selected'));

        const selectedButton = document.getElementById('payment-' + method);
        selectedButton.classList.add('selected');

        // Set the hidden input value for payment method
        document.getElementById('payment-method').value = method;
    }

    </script>

    <footer class="footer">
        <div class="help">
            <h3>NEED HELP?</h3><br>
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