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

// Pagination setup for ucpubg purchases
$ucpubg_limit = 10; // Number of ucpubg purchases per page
$ucpubg_page = isset($_GET['ucpubg_page']) ? (int)$_GET['ucpubg_page'] : 1; // Current page for ucpubg purchases
$ucpubg_offset = ($ucpubg_page - 1) * $ucpubg_limit; // Offset for ucpubg purchases query

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to get ucpubg purchases with search and pagination
$ucpubg_query = "SELECT id, player_id, package_name, amount, price, payment_method, email, phone_number, status, created_at 
                 FROM ucpubg
                 WHERE player_id LIKE ? OR package_name LIKE ? 
                 ORDER BY created_at DESC LIMIT ? OFFSET ?";
$ucpubg_stmt = $conn->prepare($ucpubg_query);
$search_term = "%" . $search . "%";
$ucpubg_stmt->bind_param("ssii", $search_term, $search_term, $ucpubg_limit, $ucpubg_offset);
$ucpubg_stmt->execute();
$ucpubg_result = $ucpubg_stmt->get_result();

// Get total number of ucpubg purchases for pagination with search
$total_ucpubg_query = "SELECT COUNT(*) FROM ucpubg WHERE player_id LIKE ? OR package_name LIKE ?";
$total_ucpubg_stmt = $conn->prepare($total_ucpubg_query);
$total_ucpubg_stmt->bind_param("ss", $search_term, $search_term);
$total_ucpubg_stmt->execute();
$total_ucpubg_result = $total_ucpubg_stmt->get_result();
$total_ucpubg = $total_ucpubg_result->fetch_row()[0];

$total_pages = ceil($total_ucpubg / $ucpubg_limit);

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
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by Player ID or Package Name">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- UCPUBG Purchase Records -->
            <h3>UCPUBG Purchase Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Player ID</th>
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
                    <?php while ($ucpubg = $ucpubg_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $ucpubg['id']; ?></td>
                            <td><?php echo $ucpubg['player_id']; ?></td>
                            <td><?php echo $ucpubg['package_name']; ?></td>
                            <td><?php echo $ucpubg['amount']; ?></td>
                            <td><?php echo $ucpubg['price']; ?></td>
                            <td><?php echo $ucpubg['payment_method']; ?></td>
                            <td><?php echo $ucpubg['email']; ?></td>
                            <td><?php echo $ucpubg['phone_number']; ?></td>
                            <td><?php echo $ucpubg['status']; ?></td>
                            <td><?php echo $ucpubg['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- UCPUBG Purchase Pagination -->
            <div class="pagination">
                <?php
                for ($page = 1; $page <= $total_pages; $page++) {
                    echo "<a href='admin_dashboard.php?ucpubg_page=$page&search=$search' class='".($page == $ucpubg_page ? 'active' : '')."'>$page</a>";
                }
                ?>
            </div>

        </section>
    </main>
</body>
</html>
