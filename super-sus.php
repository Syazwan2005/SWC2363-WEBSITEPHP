<?php
// Include database connection
include('db_connect.php');

// Define Stargold options for the game
$stargoldOptions = [
    "Stargold Malaysia" => [
        ["amount" => 60, "price" => 3.20],
        ["amount" => 325, "price" => 16.00],
        ["amount" => 660, "price" => 32.00],
        ["amount" => 1800, "price" => 85.00],
        ["amount" => 3850, "price" => 170.00],
        ["amount" => 8100, "price" => 340.00],
        ["amount" => 10800, "price" => 460.00],
        ["amount" => 16200, "price" => 690.00]
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if required POST variables are set
    if (isset($_POST['user-id'], $_POST['package-name'], $_POST['amount'], $_POST['price'], $_POST['payment-method'], $_POST['phone'], $_POST['email'])) {
        // Capture form data
        $user_id = $_POST['user-id'];
        $package_name = $_POST['package-name'];
        $amount = $_POST['amount'];
        $price = $_POST['price'];
        $payment_method = $_POST['payment-method'];
        $phone_number = $_POST['phone'];
        $email = $_POST['email'];

        // Insert purchase data into stargoldss table
        $query = "INSERT INTO stargoldss (user_id, package_name, amount, price, payment_method, email, phone_number, status, created_at) 
                  VALUES ('$user_id', '$package_name', '$amount', '$price', '$payment_method', '$email', '$phone_number', 'pending', NOW())";

        if (mysqli_query($conn, $query)) {
            // After successful insertion, redirect to receipt page
            header("Location: supersus_receipt.php?orderId=" . mysqli_insert_id($conn) . "&game=" . urlencode($package_name) . "&amount=" . $amount . "&price=" . $price . "&promoCode=" . urlencode($_POST['promo-code']) . "&userId=" . $user_id);
            exit(); // Make sure to call exit() after header redirection
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Required data not provided!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gems Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <img src="image/gems_store_4-removebg-preview.png" alt="GEMS STORE Logo" style="height: 50px;">
            </a>
            <ul class="nav-links">
                <li><a href="blog.html">Blog</a></li>

                <!-- Show Admin Dashboard link only for admins -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                <?php endif; ?>

                <!-- Show Register button if not logged in -->
                <?php if (!isset($_SESSION['username'])): ?>
                    <li><a href="register.php" class="register-btn">Register</a></li>
                <?php endif; ?>
                
                <!-- Display username with dropdown menu if logged in, otherwise show login button -->
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="user-menu">
                        <a href="#" class="register-btn"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                        <div class="user-menu-content">
                            <a href="logout.php">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="login.php" class="login-btn">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="confirmation">
        <!-- Step 1: Fill Data -->
        <div class="step">
            <h2>1. Fill Data</h2>
            <form method="POST" action="">
                <input type="text" name="user-id" placeholder="Enter User ID" required>
        </div>

        <!-- Step 2: Choose Packages -->
        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <?php foreach ($stargoldOptions as $package => $stargoldList): ?>
                    <button type="button" onclick="selectPackage('<?php echo $package; ?>')"><?php echo $package; ?></button>
                <?php endforeach; ?>
            </div>
            <p>This package is only available for Malaysia region accounts.</p>
        </div>

        <!-- Step 3: Choose Stargold -->
        <div class="step">
            <h2>3. Choose Stargold</h2>
            <div class="stargold-options" id="stargold-options">
                <!-- Stargold buttons will be dynamically added here -->
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
            const pointsContainer = document.getElementById('stargold-options');
            pointsContainer.innerHTML = '';

            const stargoldOptions = <?php echo json_encode($stargoldOptions); ?>;
            stargoldOptions[packageName].forEach(stargold => {
                const button = document.createElement('button');
                button.textContent = `${stargold.amount} Stargold - RM ${stargold.price}`;
                button.onclick = () => selectStargold(stargold.price, stargold.amount);
                pointsContainer.appendChild(button);
            });

            // Set the hidden field for package name
            document.getElementById('package-name').value = packageName;
        }

        function selectStargold(price, amount) {
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
        <p>© Copyright Stargold Store 2024, All Rights Reserved.</p>
        <p>
            <a href="terms.html" class="terms">Terms & Conditions</a> | 
            <a href="privacy-policy.html" class="privacy-policy">Privacy Policy</a>
        </p>
    </footer>
</body>
</html>