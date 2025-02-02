<?php
session_start();

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login page if not logged in or not admin
    exit();
}

require_once '../config.php';

// Fetch total sales and total orders for all sales
$sql_sales = "SELECT SUM(total_price) AS total_sales, COUNT(sale_id) AS total_orders FROM sale";
$stmt_sales = $conn->prepare($sql_sales);
$stmt_sales->execute();
$result_sales = $stmt_sales->get_result();

if ($result_sales->num_rows > 0) {
    $row_sales = $result_sales->fetch_assoc();
    $total_sales = $row_sales['total_sales'];
    $total_orders = $row_sales['total_orders'];
} else {
    $total_sales = 0;
    $total_orders = 0;
}

$stmt_sales->close();

// Fetch total number of customers
$sql_customers = "SELECT COUNT(customer_id) AS total_customers FROM customers";
$result_customers = $conn->query($sql_customers);

if ($result_customers->num_rows > 0) {
    $row_customers = $result_customers->fetch_assoc();
    $total_customers = $row_customers['total_customers'];
} else {
    $total_customers = 0;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <?php include 'template/header.php'; ?>
</head>
<body>
    <div class="wrapper">
        <?php include 'template/sidebar.php'; ?>
        <main id="main" class="main">
            <?php include 'template/nav.php'; ?>

            <div class="pagetitle">
                <h1>Dashboard</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <!-- End Page Title -->

            <section class="section dashboard">
                <div class="row">
                    <!-- Left side columns -->
                    <div class="col-lg-12">
                        <div class="row">
                            <!-- Sales Card -->
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card sales-card shadow-lg hover-shadow rounded-xl">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Sales</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-gradient-primary text-white">
                                                <i class="lni lni-cart"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?php echo number_format($total_sales, 2); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Sales Card -->

                            <!-- Orders Card -->
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card orders-card shadow-lg hover-shadow rounded-xl">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Orders</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-gradient-info text-white">
                                                <i class="lni lni-dollar"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?php echo $total_orders; ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Orders Card -->

                            <!-- Customers Card -->
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card customers-card shadow-lg hover-shadow rounded-xl">
                                    <div class="card-body">
                                        <h5 class="card-title">Customers</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-gradient-success text-white">
                                                <i class="lni lni-users"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?php echo $total_customers; ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Customers Card -->

                            <!-- Reports -->
                            <div class="col-12">
                                <div class="card shadow-lg rounded-xl">
                                    <div class="card-body">
                                        <h5 class="card-title">Muhammad Hidayat</h5>
                                        <h5 class="card-title">NIM: 220511088</h5>
                                        <h5 class="card-title">TI22D</h5>
                                        <h5 class="card-title">TEKNIK INFORMATIKA</h5>
                                        <canvas id="reportsChart" class="rounded-xl"></canvas>
                                    </div>
                                </div>
                            </div>
                            <!-- End Reports -->
                        </div>
                    </div>
                    <!-- End Left side columns -->
                </div>
            </section>
        </main>
        <!-- End #main -->
    </div>

    <?php include 'template/footer.php'; ?>

    <!--<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Sales Trend Chart
        const ctx = document.getElementById('reportsChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May', 'June'],
                datasets: [{
                    label: 'Sales Over Time',
                    data: [10, 30, 20, 40, 20, 10], // Example data (can be fetched dynamically)
                    fill: false,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.2,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true,
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>-->

    <script>
        // Sidebar toggle animation
        const sidebarToggle = document.querySelector("#sidebar-toggle");
        sidebarToggle.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });

        // Dark mode / Light mode toggle
        const modeToggle = document.querySelector("#mode-toggle");
        modeToggle.addEventListener("click", function() {
            document.body.classList.toggle("light-mode");
            document.body.classList.toggle("dark-mode");
        });
    </script>
</body>
</html>
