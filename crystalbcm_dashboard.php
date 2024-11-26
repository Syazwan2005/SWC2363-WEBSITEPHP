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

// Pagination setup for crystalbcm purchases
$crystalbcm_limit = 10; // Number of crystalbcm purchases per page
$crystalbcm_page = isset($_GET['crystalbcm_page']) ? (int)$_GET['crystalbcm_page'] : 1; // Current page for crystalbcm purchases
$crystalbcm_offset = ($crystalbcm_page - 1) * $crystalbcm_limit; // Offset for crystalbcm purchases query

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query to get crystalbcm purchases with search and pagination
$crystalbcm_query = "SELECT id, player_id, package_name, amount, price, payment_method, phone_number, email, status, created_at 
                     FROM crystalbcm
                     WHERE player_id LIKE ? OR package_name LIKE ? 
                     ORDER BY created_at DESC LIMIT ? OFFSET ?";
$crystalbcm_stmt = $conn->prepare($crystalbcm_query);
$search_term = "%" . $search . "%";
$crystalbcm_stmt->bind_param("ssii", $search_term, $search_term, $crystalbcm_limit, $crystalbcm_offset);
$crystalbcm_stmt->execute();
$crystalbcm_result = $crystalbcm_stmt->get_result();

// Get total number of crystalbcm purchases for pagination with search
$total_crystalbcm_query = "SELECT COUNT(*) FROM crystalbcm WHERE player_id LIKE ? OR package_name LIKE ?";
$total_crystalbcm_stmt = $conn->prepare($total_crystalbcm_query);
$total_crystalbcm_stmt->bind_param("ss", $search_term, $search_term);
$total_crystalbcm_stmt->execute();
$total_crystalbcm_result = $total_crystalbcm_stmt->get_result();
$total_crystalbcm = $total_crystalbcm_result->fetch_row()[0];

$total_pages = ceil($total_crystalbcm / $crystalbcm_limit);

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

            <!-- CrystalBCM Purchase Records -->
            <h3>CrystalBCM Purchase Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>Purchase ID</th>
                        <th>Player ID</th>
                        <th>Package Name</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Payment Method</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($crystalbcm = $crystalbcm_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $crystalbcm['id']; ?></td>
                            <td><?php echo $crystalbcm['player_id']; ?></td>
                            <td><?php echo $crystalbcm['package_name']; ?></td>
                            <td><?php echo $crystalbcm['amount']; ?></td>
                            <td><?php echo $crystalbcm['price']; ?></td>
                            <td><?php echo $crystalbcm['payment_method']; ?></td>
                            <td><?php echo $crystalbcm['phone_number']; ?></td>
                            <td><?php echo $crystalbcm['email']; ?></td>
                            <td><?php echo $crystalbcm['status']; ?></td>
                            <td><?php echo $crystalbcm['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- CrystalBCM Purchase Pagination -->
            <div class="pagination">
                <?php
                for ($page = 1; $page <= $total_pages; $page++) {
                    echo "<a href='admin_dashboard.php?crystalbcm_page=$page&search=$search' class='".($page == $crystalbcm_page ? 'active' : '')."'>$page</a>";
                }
                ?>
            </div>

        </section>
    </main>
</body>
</html>
