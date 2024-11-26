<?php
// Include database connection
include('db_connect.php');

// Initialize the primogem options for Genshin Impact
$primogemOptions = [
    "Genshin Impact Asia" => [
        ["amount" => 20, "price" => 1.00],
        ["amount" => 40, "price" => 2.00],
        ["amount" => 60, "price" => 3.00],
        ["amount" => 80, "price" => 4.00],
        ["amount" => 160, "price" => 8.00],
        ["amount" => 240, "price" => 12.00],
        ["amount" => 320, "price" => 16.00],
        ["amount" => 400, "price" => 20.00],
        ["amount" => 500, "price" => 25.00],
        ["amount" => 1000, "price" => 50.00],
        ["amount" => 1500, "price" => 75.00],
        ["amount" => 2000, "price" => 100.00],
        ["amount" => 2500, "price" => 125.00]
    ],
    "Genshin Impact Europe" => [
        ["amount" => 20, "price" => 1.00],
        ["amount" => 50, "price" => 2.50],
        ["amount" => 100, "price" => 5.00],
        ["amount" => 200, "price" => 10.00],
        ["amount" => 300, "price" => 15.00],
        ["amount" => 400, "price" => 20.00],
        ["amount" => 500, "price" => 25.00],
        ["amount" => 1000, "price" => 50.00],
        ["amount" => 1500, "price" => 75.00]
    ],
    "Genshin Impact America" => [
        ["amount" => 20, "price" => 1.00],
        ["amount" => 50, "price" => 2.50],
        ["amount" => 100, "price" => 5.00],
        ["amount" => 200, "price" => 10.00],
        ["amount" => 300, "price" => 15.00],
        ["amount" => 400, "price" => 20.00],
        ["amount" => 500, "price" => 25.00],
        ["amount" => 1000, "price" => 50.00]
    ],
    "Genshin Impact Taiwan, Hong Kong, Mongolia" => [
        ["amount" => 20, "price" => 1.00],
        ["amount" => 50, "price" => 2.50],
        ["amount" => 100, "price" => 5.00],
        ["amount" => 200, "price" => 10.00],
        ["amount" => 300, "price" => 15.00],
        ["amount" => 400, "price" => 20.00],
        ["amount" => 500, "price" => 25.00],
        ["amount" => 1000, "price" => 50.00]
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

        // Insert purchase data into genshinimpact table
        $query = "INSERT INTO primogemgenshin (user_id, server_id, package_name, amount, price, payment_method, phone_number, email, status) 
                  VALUES ('$user_id', '$server_id', '$package_name', '$amount', '$price', '$payment_method', '$phone_number', '$email', 'pending')";

        if (mysqli_query($conn, $query)) {
            // After successful insertion, redirect to receipt page
            header("Location: genshinimpact_receipt.php?orderId=" . mysqli_insert_id($conn) . "&game=" . urlencode($package_name) . "&amount=" . $amount . "&price=" . $price . "&promoCode=" . urlencode($_POST['promo-code']) . "&userId=" . $user_id . "&serverId=" . $server_id);
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
    <title>Genshin Impact Store</title>
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
                <label for="server-id">Choose Server Region:</label>
                <select name="server-id" id="server-id" required>
                    <option value="asia">Asia</option>
                    <option value="america">America</option>
                    <option value="europe">Europe</option>
                    <option value="taiwan_hongkong_mongolia">Taiwan, Hong Kong, Mongolia</option>
                </select>
        </div>

        <!-- Step 2: Choose Packages -->
        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <?php foreach ($primogemOptions as $package => $primogems): ?>
                    <button type="button" class="package-btn" onclick="selectPackage('<?php echo $package; ?>')"><?php echo $package; ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Step 3: Choose Primogems -->
        <div class="step">
            <h2>3. Choose Primogems</h2>
            <div class="primogem-options" id="primogem-options">
                <!-- Primogem buttons will be dynamically added here -->
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

    <script>
        let selectedPackage = '';
        let selectedAmount = 0;
        let selectedPrice = 0;

        function selectPackage(packageName) {
            selectedPackage = packageName;
            const pointsContainer = document.getElementById('primogem-options');
            pointsContainer.innerHTML = '';  // Clear previous options

            // Get the primogem options for the selected package
            const primogemData = <?php echo json_encode($primogemOptions); ?>;
            const primogems = primogemData[packageName];

            // Dynamically create the buttons for each primogem amount and price
            primogems.forEach(function(primogem) {
                const button = document.createElement('button');
                button.textContent = `${primogem.amount} Primogems - RM ${primogem.price}`;
                button.onclick = function() {
                    selectPoints(primogem.price, primogem.amount);
                };
                pointsContainer.appendChild(button);
            });

            // Set the hidden field for package name
            document.getElementById('package-name').value = packageName;
        }

        function selectPoints(price, amount) {
            selectedPrice = price;
            selectedAmount = amount;

            // Update the hidden fields and the displayed price
            document.getElementById('price').value = price;
            document.getElementById('amount').value = amount;

            document.getElementById('price-display').textContent = price.toFixed(2);
            document.getElementById('selected-product').textContent = `RM ${price.toFixed(2)}`;
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
    </script>
</body>
</html>
