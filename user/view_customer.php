<?php
session_start();
require_once '../config.php';

// Check if the user is logged in and has the right access
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'sales') {
    header("Location: login.php");
    exit();
}

// Get the customer ID from the URL parameter
$customer_id = $_GET['id'] ?? null;

if (!$customer_id) {
    $_SESSION['error_message'] = "Invalid customer ID.";
    header("Location: manage_customers.php");
    exit();
}

// Fetch customer data from the database
$sql = "SELECT * FROM customers WHERE customer_id=?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['error_message'] = "Database error.";
    header("Location: manage_customers.php");
    exit();
}
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    $_SESSION['error_message'] = "Customer not found.";
    header("Location: manage_customers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer</title>
    <?php include 'template/header.php'; ?>
  
</head>

<body>
    <div class="wrapper">
        <?php include 'template/sidebar.php'; ?>
        <main id="main" class="main">
            <?php include 'template/nav.php'; ?>

            <div class="pagetitle">
                <h1>View Customer</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active">View Customer</li>
                    </ol>
                </nav>
            </div>

            <section class="section">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title">Customer Details</h5>
                            <a href="manage_customers.php" class="btn btn-primary mt-3 mb-3">Back to Customers</a>
                        </div>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label">ID</label>
                                        <input type="text" id="customer_id" class="form-control" value="<?php echo htmlspecialchars($customer['customer_id']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nik" class="form-label">NIK</label>
                                        <input type="text" id="nik" class="form-control" value="<?php echo htmlspecialchars($customer['nik']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="no_kk" class="form-label">NO KK</label>
                                        <input type="text" id="no_kk" class="form-control" value="<?php echo htmlspecialchars($customer['no_kk']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" id="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="text" id="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" id="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea id="address" class="form-control" rows="3" readonly><?php echo htmlspecialchars($customer['address']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ktp" class="form-label">KTP</label>
                                        <img src="<?php echo htmlspecialchars($customer['ktp']); ?>" alt="KTP Image" width="100" data-bs-toggle="modal" data-bs-target="#ktpModal" style="cursor: pointer;">
                                    </div>
                                    <div class="mb-3">
                                        <label for="kk" class="form-label">KK</label>
                                        <img src="<?php echo htmlspecialchars($customer['kk']); ?>" alt="KK Image" width="100" data-bs-toggle="modal" data-bs-target="#kkModal" style="cursor: pointer;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- KTP Modal -->
    <div class="modal fade" id="ktpModal" tabindex="-1" aria-labelledby="ktpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ktpModalLabel">KTP Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="<?php echo htmlspecialchars($customer['ktp']); ?>" alt="KTP Image" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- KK Modal -->
    <div class="modal fade" id="kkModal" tabindex="-1" aria-labelledby="kkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kkModalLabel">KK Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="<?php echo htmlspecialchars($customer['kk']); ?>" alt="KK Image" class="img-fluid">
                </div>
            </div>
        </div>
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