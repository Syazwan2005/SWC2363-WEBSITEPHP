<?php
// Include database connection
include('db_connect.php');

// Define VP options for Valorant
$vpOptions = [
    "Valorant Malaysia" => [
        ["amount" => 475, "price" => 18.40],
        ["amount" => 1000, "price" => 38.00],
        ["amount" => 1475, "price" => 56.30],
        ["amount" => 2050, "price" => 74.00],
        ["amount" => 3050, "price" => 111.00],
        ["amount" => 3650, "price" => 129.00],
        ["amount" => 5350, "price" => 184.00],
        ["amount" => 11000, "price" => 370.00]
    ],
    "Valorant Malaysia Slow" => [
        ["amount" => 475, "price" => 17.40],
        ["amount" => 1000, "price" => 36.00],
        ["amount" => 1475, "price" => 53.30],
        ["amount" => 2050, "price" => 70.00],
        ["amount" => 3050, "price" => 105.50],
        ["amount" => 3650, "price" => 121.70],
        ["amount" => 5350, "price" => 174.00],
        ["amount" => 11000, "price" => 342.40]
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if required POST variables are set
    if (isset($_POST['riot-username'], $_POST['package-name'], $_POST['amount'], $_POST['price'], $_POST['payment-method'], $_POST['phone'], $_POST['email'])) {
        // Capture form data
        $riot_username = $_POST['riot-username'];
        $package_name = $_POST['package-name'];
        $amount = $_POST['amount'];
        $price = $_POST['price'];
        $payment_method = $_POST['payment-method'];
        $phone_number = $_POST['phone'];
        $email = $_POST['email'];

        // Insert purchase data into the valorant table
        $query = "INSERT INTO vpvalorant (riot_username, package_name, amount, price, payment_method, email, phone_number, status, created_at) 
                  VALUES ('$riot_username', '$package_name', '$amount', '$price', '$payment_method', '$email', '$phone_number', 'pending', NOW())";

if (mysqli_query($conn, $query)) {
    // After successful insertion, redirect to receipt page
    header("Location: valorant_receipt.php?orderId=" . mysqli_insert_id($conn) . "&game=" . urlencode($package_name) . "&item=" . urlencode($amount) . "&price=" . urlencode($price) . "&promoCode=" . urlencode($_POST['promo-code']) . "&username=" . urlencode($riot_username));
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
                <li><a href="#">Valorant Tools</a></li>
                <li><a href="register.php" class="register-btn">Register</a></li>
            </ul>
        </nav>
    </header>

    <section class="confirmation">
        <div class="step">
            <h2>1. Fill Data</h2>
            <form method="POST" action="">
                <input type="text" name="riot-username" placeholder="Enter Riot Username" required>
        </div>

        <div class="step">
            <h2>2. Choose Packages</h2>
            <div class="package-options">
                <?php foreach ($vpOptions as $package => $vpList): ?>
                    <button type="button" onclick="selectPackage('<?php echo $package; ?>')"><?php echo $package; ?></button>
                <?php endforeach; ?>
            </div>
            <p>This package is only available for Malaysia region accounts.</p>
        </div>

        <div class="step">
            <h2>3. Choose Valorant Points</h2>
            <div class="vp-options" id="vp-options"></div>
        </div>

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
            const pointsContainer = document.getElementById('vp-options');
            pointsContainer.innerHTML = '';

            const vpOptions = <?php echo json_encode($vpOptions); ?>;
            vpOptions[packageName].forEach(vp => {
                const button = document.createElement('button');
                button.textContent = `${vp.amount} VP - RM ${vp.price}`;
                button.onclick = () => selectVP(vp.price, vp.amount);
                pointsContainer.appendChild(button);
            });

            // Set the hidden field for package name
            document.getElementById('package-name').value = packageName;
        }

        function selectVP(price, amount) {
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

</body>
</html>
