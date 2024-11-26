<?php
// Fetch query parameters from URL, using a fallback for missing data
$orderId = 'GEMS' . rand(10000000, 99999999); // Generate random 8-digit Order ID with "GEMS" prefix
$gameName = isset($_GET['game']) ? $_GET['game'] : 'Unknown Game';
$itemAmount = isset($_GET['amount']) ? $_GET['amount'] : '0';
$price = isset($_GET['price']) ? $_GET['price'] : '0.00'; // Default price is numeric, not including 'RM' yet
$promoCode = isset($_GET['promoCode']) ? $_GET['promoCode'] : 'None';
$email = isset($_GET['email']) ? $_GET['email'] : 'N/A'; // Default 'N/A' if not found in the query string

// Format price with RM currency
$formattedPrice = "RM " . number_format($price, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
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

    <section class="receipt">
        <h1>Thank You For Your Order!</h1>
        <p id="order-message">
            <?php
                // Custom thank-you message based on the game
                if ($gameName === 'PUBG Mobile') {
                    echo "The $itemAmount UC have been added to your PUBG Mobile account.";
                } else {
                    echo "Your purchase of $itemAmount has been processed successfully!";
                }
            ?>
        </p>

        <!-- Order Summary -->
        <div class="order-summary">
            <h2>Your Order Summary</h2>
            <p><strong>Order ID:</strong> <?php echo $orderId; ?></p>
            <p><strong>Game:</strong> <?php echo htmlspecialchars($gameName); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Item Purchased:</strong> <?php echo htmlspecialchars($itemAmount) . " Coins"; ?></p>
            <p><strong>Promo Code:</strong> <?php echo htmlspecialchars($promoCode); ?></p>
            <p><strong>Total Price:</strong> <?php echo $formattedPrice; ?></p>
        </div>

        <button onclick="window.location.href='index.php'">Make Another Purchase</button>
    </section>
</body>
</html>
