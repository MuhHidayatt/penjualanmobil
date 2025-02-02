<?php
session_start();
ob_start(); // Start output buffering

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

$username = $_SESSION['username'];
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '2000-01-01';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

// Fetch total sales and total orders
$sql_sales = "SELECT SUM(total_price) AS total_sales, COUNT(sale_id) AS total_orders
              FROM sale
              WHERE sale_date BETWEEN ? AND ?";
$stmt_sales = $conn->prepare($sql_sales);
$stmt_sales->bind_param("ss", $start_date, $end_date);
$stmt_sales->execute();
$result_sales = $stmt_sales->get_result();
$total_sales = 0;
$total_orders = 0;

if ($result_sales->num_rows > 0) {
    $row_sales = $result_sales->fetch_assoc();
    $total_sales = $row_sales['total_sales'] ?? 0;
    $total_orders = $row_sales['total_orders'] ?? 0;
}

$stmt_sales->close();

// Fetch total number of customers
$sql_customers = "SELECT COUNT(customer_id) AS total_customers FROM customers";
$result_customers = $conn->query($sql_customers);
$total_customers = $result_customers->fetch_assoc()['total_customers'] ?? 0;

// Fetch detailed sales data
$sql_sales_details = "SELECT s.sale_id, c.name AS customer_name, m.model AS mobil_model, s.sale_date, s.total_price, s.payment_type
                      FROM sale s
                      JOIN customers c ON s.customer_id = c.customer_id
                      JOIN mobil m ON s.mobil_id = m.mobil_id
                      WHERE s.sale_date BETWEEN ? AND ?";
$stmt_sales_details = $conn->prepare($sql_sales_details);
$stmt_sales_details->bind_param("ss", $start_date, $end_date);
$stmt_sales_details->execute();
$result_sales_details = $stmt_sales_details->get_result();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
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

            <section class="section dashboard">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="reports d-flex justify-content-center gap-4">
                                <div class="col-xxl-4 col-md-4">
                                    <div class="card info-card sales-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Total Sales</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="lni lni-cart"></i>
                                                </div>
                                                <div class="ps-3">
                                                    <h6><?php echo number_format($total_sales, 2); ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-4 col-md-4">
                                    <div class="card info-card revenue-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Total Orders</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="lni lni-dollar"></i>
                                                </div>
                                                <div class="ps-3">
                                                    <h6><?php echo $total_orders; ?></h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-lg">
                                <div class="card-body">
                                    <h5 class="card-title">Sales Reports</h5>
                                    <form method="POST" action="" class="row g-3 mb-4">
                                        <div class="col-md-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
                                        </div>
                                        <div class="col-md-3 align-self-end">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <button type="submit" formaction="report_sale.php" target="_blank" class="btn btn-primary">Cetak</button>
                                        </div>
                                    </form>

                                    <div class="overflow-x-auto">
                                        <table class="table table-bordered display" id="salesTable">
                                            <thead>
                                                <tr>
                                                    <th>NO</th>
                                                    <th>Sale ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Mobil Model</th>
                                                    <th>Sale Date</th>
                                                    <th>Total Price</th>
                                                    <th>Payment Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; ?>
                                                <?php while ($row = $result_sales_details->fetch_assoc()) : ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td><?php echo htmlspecialchars($row['sale_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['mobil_model']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                                                        <td class="price"><?php echo htmlspecialchars($row['total_price']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['payment_type']); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <?php include 'template/footer.php'; ?>

    <script>
        $('#salesTable').DataTable();
        const sidebarToggle = document.querySelector("#sidebar-toggle");
        sidebarToggle.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });

        // Format prices in IDR
        const priceFormatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        document.querySelectorAll('.price').forEach(function(cell) {
            const price = parseFloat(cell.textContent.replace(/[^0-9.-]+/g, ""));
            cell.textContent = priceFormatter.format(price);
        });
    </script>
</body>
</html>
