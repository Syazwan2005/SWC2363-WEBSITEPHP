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

// Pagination setup for coinefootball purchases
$coinefootball_limit = 10; // Number of coinefootball purchases per page
$coinefootball_page = isset($_GET['coinefootball_page']) ? (int)$_GET['coinefootball_page'] : 1; // Current page for coinefootball purchases
$coinefootball_offset = ($coinefootball_page - 1) * $coinefootball_limit; // Offset for coinefootball purchases query

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to get coinefootball purchases with search and pagination
$coinefootball_query = "SELECT id, email, password, package_name, amount, price, payment_method, phone_number, status, created_at 
                        FROM coinefootball
                        WHERE email LIKE ? OR package_name LIKE ? 
                        ORDER BY created_at DESC LIMIT ? OFFSET ?";
$coinefootball_stmt = $conn->prepare($coinefootball_query);
$search_term = "%" . $search . "%";
$coinefootball_stmt->bind_param("ssii", $search_term, $search_term, $coinefootball_limit, $coinefootball_offset);
$coinefootball_stmt->execute();
$coinefootball_result = $coinefootball_stmt->get_result();

// Get total number of coinefootball purchases for pagination with search
$total_coinefootball_query = "SELECT COUNT(*) FROM coinefootball WHERE email LIKE ? OR package_name LIKE ?";
$total_coinefootball_stmt = $conn->prepare($total_coinefootball_query);
$total_coinefootball_stmt->bind_param("ss", $search_term, $search_term);
$total_coinefootball_stmt->execute();
$total_coinefootball_result = $total_coinefootball_stmt->get_result();
$total_coinefootball = $total_coinefootball_result->fetch_row()[0];

$total_pages = ceil($total_coinefootball / $coinefootball_limit);

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
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by Email or Package Name">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- Coinefootball Purchase Records -->
            <h3>Coinefootball Purchase Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Package Name</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Payment Method</th>
                        <th>Phone Number</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($coinefootball = $coinefootball_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $coinefootball['id']; ?></td>
                            <td><?php echo $coinefootball['email']; ?></td>
                            <td><?php echo $coinefootball['password']; ?></td>
                            <td><?php echo $coinefootball['package_name']; ?></td>
                            <td><?php echo $coinefootball['amount']; ?></td>
                            <td><?php echo $coinefootball['price']; ?></td>
                            <td><?php echo $coinefootball['payment_method']; ?></td>
                            <td><?php echo $coinefootball['phone_number']; ?></td>
                            <td><?php echo $coinefootball['status']; ?></td>
                            <td><?php echo $coinefootball['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Coinefootball Purchase Pagination -->
            <div class="pagination">
                <?php
                for ($page = 1; $page <= $total_pages; $page++) {
                    echo "<a href='admin_dashboard.php?coinefootball_page=$page&search=$search' class='".($page == $coinefootball_page ? 'active' : '')."'>$page</a>";
                }
                ?>
            </div>

        </section>
    </main>
</body>
</html>
