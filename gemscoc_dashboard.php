<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include "db_connect.php";

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Pagination setup for gemscoc purchases
$gemscoc_limit = 10; // Number of gemscoc purchases per page
$gemscoc_page = isset($_GET['gemscoc_page']) ? (int)$_GET['gemscoc_page'] : 1; // Current page for gemscoc purchases
$gemscoc_offset = ($gemscoc_page - 1) * $gemscoc_limit; // Offset for gemscoc purchases query

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to get gemscoc purchases with search and pagination
$gemscoc_query = "SELECT id, player_tag, package_name, amount, price, payment_method, email, phone_number, status, created_at 
                  FROM gemscoc
                  WHERE player_tag LIKE ? OR package_name LIKE ? 
                  ORDER BY created_at DESC LIMIT ? OFFSET ?";
$gemscoc_stmt = $conn->prepare($gemscoc_query);
$search_term = "%" . $search . "%";
$gemscoc_stmt->bind_param("ssii", $search_term, $search_term, $gemscoc_limit, $gemscoc_offset);
$gemscoc_stmt->execute();
$gemscoc_result = $gemscoc_stmt->get_result();

// Get total number of gemscoc purchases for pagination with search
$total_gemscoc_query = "SELECT COUNT(*) FROM gemscoc WHERE player_tag LIKE ? OR package_name LIKE ?";
$total_gemscoc_stmt = $conn->prepare($total_gemscoc_query);
$total_gemscoc_stmt->bind_param("ss", $search_term, $search_term);
$total_gemscoc_stmt->execute();
$total_gemscoc_result = $total_gemscoc_stmt->get_result();
$total_gemscoc = $total_gemscoc_result->fetch_row()[0];

$total_pages = ceil($total_gemscoc / $gemscoc_limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #f4f4f4;
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 16px;
            border: 1px solid #ddd;
            text-decoration: none;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        .search-bar {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-bar input {
            padding: 8px;
            width: 250px;
            margin-right: 10px;
        }

        .search-bar button {
            padding: 8px 16px;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <img src="image/gems_store_4-removebg-preview.png" alt="GEMS STORE Logo" style="height: 60px;">
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="dashboard-section">
            <h2>Admin Dashboard</h2>
            <h3>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>

            <!-- Search Bar -->
            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by Player Tag or Package Name">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- Gemscoc Purchase Records -->
            <h3>Gemscoc Purchase Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Player Tag</th>
                        <th>Package Name</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Payment Method</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($gemscoc = $gemscoc_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $gemscoc['id']; ?></td>
                            <td><?php echo $gemscoc['player_tag']; ?></td>
                            <td><?php echo $gemscoc['package_name']; ?></td>
                            <td><?php echo $gemscoc['amount']; ?></td>
                            <td><?php echo $gemscoc['price']; ?></td>
                            <td><?php echo $gemscoc['payment_method']; ?></td>
                            <td><?php echo $gemscoc['email']; ?></td>
                            <td><?php echo $gemscoc['phone_number']; ?></td>
                            <td><?php echo $gemscoc['status']; ?></td>
                            <td><?php echo $gemscoc['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Gemscoc Purchase Pagination -->
            <div class="pagination">
                <?php
                for ($page = 1; $page <= $total_pages; $page++) {
                    echo "<a href='admin_dashboard.php?gemscoc_page=$page&search=$search' class='".($page == $gemscoc_page ? 'active' : '')."'>$page</a>";
                }
                ?>
            </div>

        </section>
    </main>
</body>
</html>
