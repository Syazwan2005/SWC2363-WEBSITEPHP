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

// Pagination setup for vpvalorant purchases
$vpvalorant_limit = 10; // Number of vpvalorant purchases per page
$vpvalorant_page = isset($_GET['vpvalorant_page']) ? (int)$_GET['vpvalorant_page'] : 1; // Current page for vpvalorant purchases
$vpvalorant_offset = ($vpvalorant_page - 1) * $vpvalorant_limit; // Offset for vpvalorant purchases query

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to get vpvalorant purchases with search and pagination
$vpvalorant_query = "SELECT id, riot_username, package_name, amount, price, payment_method, email, phone_number, status, created_at 
                     FROM vpvalorant
                     WHERE riot_username LIKE ? OR package_name LIKE ? 
                     ORDER BY created_at DESC LIMIT ? OFFSET ?";
$vpvalorant_stmt = $conn->prepare($vpvalorant_query);
$search_term = "%" . $search . "%";
$vpvalorant_stmt->bind_param("ssii", $search_term, $search_term, $vpvalorant_limit, $vpvalorant_offset);
$vpvalorant_stmt->execute();
$vpvalorant_result = $vpvalorant_stmt->get_result();

// Get total number of vpvalorant purchases for pagination with search
$total_vpvalorant_query = "SELECT COUNT(*) FROM vpvalorant WHERE riot_username LIKE ? OR package_name LIKE ?";
$total_vpvalorant_stmt = $conn->prepare($total_vpvalorant_query);
$total_vpvalorant_stmt->bind_param("ss", $search_term, $search_term);
$total_vpvalorant_stmt->execute();
$total_vpvalorant_result = $total_vpvalorant_stmt->get_result();
$total_vpvalorant = $total_vpvalorant_result->fetch_row()[0];

$total_pages = ceil($total_vpvalorant / $vpvalorant_limit);

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
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by Riot Username or Package Name">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- vpvalorant Purchase Records -->
            <h3>VP Valorant Purchase Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Riot Username</th>
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
                    <?php while ($vpvalorant = $vpvalorant_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $vpvalorant['id']; ?></td>
                            <td><?php echo $vpvalorant['riot_username']; ?></td>
                            <td><?php echo $vpvalorant['package_name']; ?></td>
                            <td><?php echo $vpvalorant['amount']; ?></td>
                            <td><?php echo $vpvalorant['price']; ?></td>
                            <td><?php echo $vpvalorant['payment_method']; ?></td>
                            <td><?php echo $vpvalorant['email']; ?></td>
                            <td><?php echo $vpvalorant['phone_number']; ?></td>
                            <td><?php echo $vpvalorant['status']; ?></td>
                            <td><?php echo $vpvalorant['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- VP Valorant Purchase Pagination -->
            <div class="pagination">
                <?php
                // Previous page link
                if ($vpvalorant_page > 1) {
                    echo '<a href="?vpvalorant_page=' . ($vpvalorant_page - 1) . '&search=' . urlencode($search) . '">&laquo; Previous</a>';
                }

                // Loop through total pages and create a link for each page
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo '<a href="?vpvalorant_page=' . $i . '&search=' . urlencode($search) . '" class="' . ($i == $vpvalorant_page ? 'active' : '') . '">' . $i . '</a>';
                }

                // Next page link
                if ($vpvalorant_page < $total_pages) {
                    echo '<a href="?vpvalorant_page=' . ($vpvalorant_page + 1) . '&search=' . urlencode($search) . '">Next &raquo;</a>';
                }
                ?>
            </div>

        </section>
    </main>
</body>
</html>
