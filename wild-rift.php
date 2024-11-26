<?php
// Include database connection
include('db_connect.php');

// Define Wild Core options for Wild Rift
$wildCoreOptions = [
    "Wild Rift" => [
        ["amount" => 425, "price" => 18.37],
        ["amount" => 1000, "price" => 41.14],
        ["amount" => 1850, "price" => 75.25],
        ["amount" => 3275, "price" => 131.75],
        ["amount" => 4800, "price" => 188.27],
        ["amount" => 10000, "price" => 349.14]
    ],
    "Wild Rift Slow" => [
        ["amount" => 425, "price" => 17.37],
        ["amount" => 1000, "price" => 38.14],
        ["amount" => 1850, "price" => 69.76],
        ["amount" => 3275, "price" => 122.13],
        ["amount" => 4800, "price" => 174.52],
        ["amount" => 10000, "price" => 376.65]
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if required POST variables are set
    if (isset($_POST['riot-id'], $_POST['package-name'], $_POST['amount'], $_POST['price'], $_POST['payment-method'], $_POST['phone'], $_POST['email'])) {
        // Capture form data
        $riot_id = $_POST['riot-id'];
        $package_name = $_POST['package-name'];
        $amount = $_POST['amount'];
        $price = $_POST['price'];
        $payment_method = $_POST['payment-method'];
        $phone_number = $_POST['phone'];
        $email = $_POST['email'];

        // Insert purchase data into wildcorewildrift table
        $query = "INSERT INTO wildcorewildrift (riot_id, package_name, amount, price, payment_method, email, phone_number, status, created_at) 
                  VALUES ('$riot_id', '$package_name', '$amount', '$price', '$payment_method', '$email', '$phone_number', 'pending', NOW())";

        if (mysqli_query($conn, $query)) {
            // After successful insertion, redirect to receipt page
            header("Location: wildrift_receipt.php?orderId=" . mysqli_insert_id($conn) . "&game=" . urlencode($package_name) . "&amount=" . $amount . "&price=" . $price . "&promoCode=" . urlencode($_POST['promo-code']) . "&riotId=" . $riot_id);
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
    <title>Gems Store - Wild Rift</title>
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
                <li><a href="#">Wild Rift Tools</a></li>
                <li><a href="register.php" class="register-btn">Register</a></li>
            </ul>
        </nav>
    </header>

    <section class="confirmation">
        <!-- Step 1: Fill Data -->
        <div class="step">
            <h2>1. Fill Data</h2>
            <form method="POST" action="">
                <input type="text" name="riot-id" placeholder="Enter Riot ID" required>
                <p>To find your Riot ID, go to the "Basic Info" tab in your Wild Rift profile. Your Riot ID is shown below your nickname.</p>
        </div>

        <!-- Step 2: Choose Packages -->
        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <?php foreach ($wildCoreOptions as $package => $coreList): ?>
                    <button type="button" onclick="selectPackage('<?php echo $package; ?>')"><?php echo $package; ?></button>
                <?php endforeach; ?>
            </div>
            <p>Choose your Wild Rift package based on your preference.</p>
        </div>

        <!-- Step 3: Choose Wild Cores -->
        <div class="step">
            <h2>3. Choose Wild Cores</h2>
            <div class="wild-core-options" id="wild-core-options">
                <!-- Wild Core buttons will be dynamically added here -->
            </div>
        </div>

        <!-- Step 4: Choose Payment Methods -->
        <div class="step">
            <h2>4. Choose Payment Methods</h2>
            <p>Selected Price: RM <span id="price-display">0.00</span></p>
            <div class="payment-options">
                <button type="button" onclick="selectPaymentMethod('e-wallet')">E-Wallet</button>
                <button type="button" onclick="selectPaymentMethod('bank')">Bank Transfer</button>
                <button type="button" onclick="selectPaymentMethod('whatsapp')">WhatsApp</button>
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
            const wildCoreContainer = document.getElementById('wild-core-options');
            wildCoreContainer.innerHTML = ''; // Clear previous options

            const coreOptions = <?php echo json_encode($wildCoreOptions); ?>;
            coreOptions[packageName].forEach(core => {
                const button = document.createElement('button');
                button.textContent = `${core.amount} Cores - RM ${core.price}`;
                button.onclick = () => selectCore(core.price, core.amount);
                wildCoreContainer.appendChild(button);
            });

            document.getElementById('package-name').value = packageName;
        }

        function selectCore(price, amount) {
            selectedPrice = price;
            selectedAmount = amount;

            document.getElementById('price').value = price;
            document.getElementById('amount').value = amount;
            document.getElementById('price-display').textContent = price.toFixed(2);
            document.getElementById('selected-product').textContent = `RM ${price.toFixed(2)}`;
        }

        function selectPaymentMethod(method) {
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
