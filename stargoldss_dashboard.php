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

// Pagination setup for stargoldss purchases
$stargoldss_limit = 10; // Number of stargoldss purchases per page
$stargoldss_page = isset($_GET['stargoldss_page']) ? (int)$_GET['stargoldss_page'] : 1; // Current page for stargoldss purchases
$stargoldss_offset = ($stargoldss_page - 1) * $stargoldss_limit; // Offset for stargoldss purchases query

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to get stargoldss purchases with search and pagination
$stargoldss_query = "SELECT id, user_id, package_name, amount, price, payment_method, email, phone_number, status, created_at 
                     FROM stargoldss
                     WHERE user_id LIKE ? OR package_name LIKE ? 
                     ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stargoldss_stmt = $conn->prepare($stargoldss_query);
$search_term = "%" . $search . "%";
$stargoldss_stmt->bind_param("ssii", $search_term, $search_term, $stargoldss_limit, $stargoldss_offset);
$stargoldss_stmt->execute();
$stargoldss_result = $stargoldss_stmt->get_result();

// Get total number of stargoldss purchases for pagination with search
$total_stargoldss_query = "SELECT COUNT(*) FROM stargoldss WHERE user_id LIKE ? OR package_name LIKE ?";
$total_stargoldss_stmt = $conn->prepare($total_stargoldss_query);
$total_stargoldss_stmt->bind_param("ss", $search_term, $search_term);
$total_stargoldss_stmt->execute();
$total_stargoldss_result = $total_stargoldss_stmt->get_result();
$total_stargoldss = $total_stargoldss_result->fetch_row()[0];

$total_pages = ceil($total_stargoldss / $stargoldss_limit);

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
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by User ID or Package Name">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- Stargoldss Purchase Records -->
            <h3>Stargoldss Purchase Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>User ID</th>
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
                    <?php while ($stargoldss = $stargoldss_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $stargoldss['id']; ?></td>
                            <td><?php echo $stargoldss['user_id']; ?></td>
                            <td><?php echo $stargoldss['package_name']; ?></td>
                            <td><?php echo $stargoldss['amount']; ?></td>
                            <td><?php echo $stargoldss['price']; ?></td>
                            <td><?php echo $stargoldss['payment_method']; ?></td>
                            <td><?php echo $stargoldss['email']; ?></td>
                            <td><?php echo $stargoldss['phone_number']; ?></td>
                            <td><?php echo $stargoldss['status']; ?></td>
                            <td><?php echo $stargoldss['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Stargoldss Purchase Pagination -->
            <div class="pagination">
                <?php
                for ($page = 1; $page <= $total_pages; $page++) {
                    echo "<a href='admin_dashboard.php?stargoldss_page=$page&search=$search' class='".($page == $stargoldss_page ? 'active' : '')."'>$page</a>";
                }
                ?>
            </div>

        </section>
    </main>
</body>
</html>