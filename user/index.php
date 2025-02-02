<?php
session_start();

// Check if the user is logged in and has the sales role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'sales') {
  header("Location: ../login.php"); // Redirect to login page if not logged in or not sales
  exit();
}

require_once '../config.php';

$username = $_SESSION['username'];

// Fetch total sales and total orders for the logged-in sales user
$sql_sales = "SELECT SUM(total_price) AS total_sales, COUNT(sale_id) AS total_orders
              FROM sale s
              JOIN users u ON s.user_id = u.user_id
              WHERE u.username = ?";
$stmt_sales = $conn->prepare($sql_sales);
$stmt_sales->bind_param("s", $username);
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
  <title>Sales Dashboard</title>
  <?php include 'template/header.php'; ?>
</head>

<body>
  <div class="wrapper">
    <?php include 'template/sidebar.php' ?>
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
              <!-- End Sales Card -->

              <!-- Revenue Card -->
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
              <!-- End Revenue Card -->

              <!-- Customers Card -->
              <div class="col-xxl-4 col-md-4">
                <div class="card info-card customers-card">
                  <div class="card-body">
                    <h5 class="card-title">Customers</h5>
                    <div class="d-flex align-items-center">
                      <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
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
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title">Reports</h5>

                    <!-- Line Chart -->
                    <div id="reportsChart"></div>

                    <!-- End Line Chart -->
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

  <script>
    const sidebarToggle = document.querySelector("#sidebar-toggle");
    sidebarToggle.addEventListener("click", function() {
      document.querySelector("#sidebar").classList.toggle("collapsed");
    });
  </script>
</body>

</html>