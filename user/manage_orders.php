<?php
session_start();

// Check if the user is logged in and is a sales
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'sales') {
    header("Location: login.php");
    exit();
}

require_once '../config.php';

// Handle form submissions for adding/editing sale
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sale_id = $_POST['sale_id'] ?? null;
    $customer_id = $_POST['customer_id'];
    $mobil_id = $_POST['mobil_id'];
    $sale_date = $_POST['sale_date'];
    $payment_type = $_POST['payment_type'];
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session when user logs in

    // Fetch the price of the selected mobil
    $sql_price = "SELECT price, stock FROM mobil WHERE mobil_id=?";
    $stmt_price = $conn->prepare($sql_price);
    $stmt_price->bind_param("i", $mobil_id);
    $stmt_price->execute();
    $stmt_price->bind_result($mobil_price, $mobil_stock);
    $stmt_price->fetch();
    $stmt_price->close();

    if ($mobil_stock > 0) {
        $total_price = $mobil_price; // Set total price to mobil price

        if ($sale_id) {
            // Update existing sale
            $sql = "UPDATE sale SET customer_id=?, mobil_id=?, sale_date=?, total_price=?, payment_type=?, user_id=? WHERE sale_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssii", $customer_id, $mobil_id, $sale_date, $total_price, $payment_type, $user_id, $sale_id);
        } else {
            // Add new sale
            $sql = "INSERT INTO sale (customer_id, mobil_id, sale_date, total_price, payment_type, user_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssi", $customer_id, $mobil_id, $sale_date, $total_price, $payment_type, $user_id);

            // Update mobil stock
            $sql_stock = "UPDATE mobil SET stock = stock - 1 WHERE mobil_id=?";
            $stmt_stock = $conn->prepare($sql_stock);
            $stmt_stock->bind_param("i", $mobil_id);
            $stmt_stock->execute();
            $stmt_stock->close();
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Sale saved successfully.";
            header("Location: manage_orders.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error saving sale: " . $stmt->error;
        }
    } else {
        $_SESSION['error_message'] = "Error: The mobil is out of stock.";
    }
}

// Handle deletion of sale
if (isset($_GET['delete'])) {
    $sale_id = $_GET['delete'];
    $sql = "DELETE FROM sale WHERE sale_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $_SESSION['success_message'] = "Sale deleted successfully.";
    header("Location: manage_orders.php");
    exit();
}

// Fetch sale from the database
$sql = "SELECT sale.sale_id, sale.sale_date, sale.total_price, sale.payment_type, customers.customer_id, customers.name AS customer_name, mobil.mobil_id, mobil.model AS mobil_model, mobil.brand AS mobil_brand 
        FROM sale 
        JOIN customers ON sale.customer_id = customers.customer_id 
        JOIN mobil ON sale.mobil_id = mobil.mobil_id";
$result = $conn->query($sql);

// Fetch customer data for dropdown
$sql_customers = "SELECT customer_id, name FROM customers";
$result_customers = $conn->query($sql_customers);

// Fetch mobil data for dropdown
$sql_mobil = "SELECT mobil_id, brand, model, price, stock FROM mobil";
$result_mobil = $conn->query($sql_mobil);

if (!$result) {
    $_SESSION['error_message'] = "Error fetching sale: " . $conn->error;
    $result = []; // Ensure $result is defined as an empty array
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sale</title>
    <?php include 'template/header.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'template/sidebar.php'; ?>
        <main id="main" class="main">
            <?php include 'template/nav.php'; ?>

            <div class="pagetitle">
                <h1 class="mb-2">Data Sale</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active">Sale</li>
                    </ol>
                </nav>
            </div>

            <section class="section dashboard">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="card shadow-lg">
                                <div class="card-body">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-primary mb-3 mt-3" data-bs-toggle="modal" data-bs-target="#SaleModal">Tambah Data</button>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="table table-bordered display overflow-scroll" id="saleTable">
                                            <thead>
                                                <tr>
                                                    <th>NO</th>
                                                    <th>ID</th>
                                                    <th>Customer</th>
                                                    <th>mobil</th>
                                                    <th>Sale Date</th>
                                                    <th>Total Price</th>
                                                    <th>Payment Type</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; ?>
                                                <?php while ($row = $result->fetch_assoc()) : ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td><?php echo $row['sale_id']; ?></td>
                                                        <td><?php echo $row['customer_name']; ?></td>
                                                        <td><?php echo $row['mobil_brand'] . ' ' . $row['mobil_model']; ?></td>
                                                        <td><?php echo $row['sale_date']; ?></td>
                                                        <td class="total_price"><?php echo $row['total_price']; ?></td>
                                                        <td><?php echo $row['payment_type']; ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['sale_id']; ?>" data-customer_id="<?php echo $row['customer_id']; ?>" data-mobil_id="<?php echo $row['mobil_id']; ?>" data-sale_date="<?php echo $row['sale_date']; ?>" data-total_price="<?php echo $row['total_price']; ?>" data-payment_type="<?php echo $row['payment_type']; ?>" data-bs-toggle="modal" data-bs-target="#SaleModal">Edit</button>
                                                            <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['sale_id']; ?>">Delete</button>
                                                        </td>
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

                <div class="modal fade" id="SaleModal" tabindex="-1" aria-labelledby="SaleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="manage_orders.php" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="SaleModalLabel">Sale</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="sale_id" id="sale_id">
                                    <input type="hidden" name="user_id" id="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label">Customer</label>
                                        <select name="customer_id" id="customer_id" class="form-select" required>
                                            <?php while ($row = $result_customers->fetch_assoc()) : ?>
                                                <option value="<?php echo $row['customer_id']; ?>"><?php echo $row['name']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mobil_id" class="form-label">mobil</label>
                                        <select class="form-select" id="mobil_id" name="mobil_id" aria-label="Floating label select example" onchange="updatePrice()" required>
                                            <option value="" data-price="">Select a mobil</option>
                                            <?php while ($row = $result_mobil->fetch_assoc()) : ?>
                                                <option value="<?= $row['mobil_id'] ?>" data-price="<?= $row['price'] ?>"><?= $row['model'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="total_price" class="form-label">Total Price</label>
                                        <input type="text" class="form-control" id="total_price" name="total_price" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sale_date" class="form-label">Sale Date</label>
                                        <input type="date" class="form-control" name="sale_date" id="sale_date" placeholder="Enter sale date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="payment_type" class="form-label">Payment Type</label>
                                        <select name="payment_type" id="payment_type" class="form-select" required>
                                            <option value="Cash">Cash</option>
                                            <option value="Credit">Credit</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <?php include 'template/footer.php'; ?>

    <script>
        function updatePrice() {
            var select = document.getElementById('mobil_id');
            var selectedOption = select.options[select.selectedIndex];
            var price = selectedOption.getAttribute('data-price');

            if (price) {
                var formattedPrice = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(price);
                document.getElementById('total_price').value = formattedPrice;
            } else {
                document.getElementById('total_price').value = '';
            }
        }

        // Format prices in IDR
        const priceFormatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        document.querySelectorAll('.total_price').forEach(function(cell) {
            const price = parseFloat(cell.textContent.replace(/[^0-9.-]+/g, ""));
            cell.textContent = priceFormatter.format(price);
        });

        $(document).ready(function() {
            const sidebarToggle = document.querySelector("#sidebar-toggle");
            sidebarToggle.addEventListener("click", function() {
                document.querySelector("#sidebar").classList.toggle("collapsed");
            });

            $('#saleTable').DataTable();

            <?php if (isset($_SESSION['success_message'])) : ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '<?php echo $_SESSION['success_message']; ?>'
                });
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])) : ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $_SESSION['error_message']; ?>'
                });
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            // Handle edit button click
            $('.edit-btn').click(function() {
                var sale_id = $(this).data('id');
                var customer_id = $(this).data('customer_id');
                var mobil_id = $(this).data('mobil_id');
                var sale_date = $(this).data('sale_date');
                var total_price = $(this).data('total_price');
                var payment_type = $(this).data('payment_type');

                $('#sale_id').val(sale_id);
                $('#customer_id').val(customer_id);
                $('#mobil_id').val(mobil_id);
                $('#sale_date').val(sale_date);
                $('#total_price').val(total_price);
                $('#payment_type').val(payment_type);
            });

            // Handle delete button click
            $('.delete-btn').click(function() {
                var sale_id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'manage_orders.php?delete=' + sale_id;
                    }
                });
            });

            // Reset form on modal close
            $('#SaleModal').on('hidden.bs.modal', function() {
                $('#sale_id').val('');
                $('#customer_id').val('');
                $('#mobil_id').val('');
                $('#sale_date').val('');
                $('#total_price').val('');
                $('#payment_type').val('Cash');
            });
        });
    </script>
</body>

</html>
