<?php
session_start();

// Check if the user is logged in and is a sales user
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'sales') {
    header("Location: login.php");
    exit();
}

require_once '../config.php';

// Fetch customer and mobil data for dropdowns
$sql_customers = "SELECT customer_id, name FROM customers";
$result_customers = $conn->query($sql_customers);

$sql_mobil = "SELECT mobil_id, brand, model, stock FROM mobil";
$result_mobil = $conn->query($sql_mobil);

// Handle form submission for adding new order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'];
    $mobil_id = $_POST['mobil_id'];
    $sale_date = $_POST['sale_date'];
    $total_price = $_POST['total_price'];
    $payment_type = $_POST['payment_type']; // Added payment type
    $down_payment = isset($_POST['down_payment']) ? $_POST['down_payment'] : 0; // Added down payment, default to 0 if not set

    // Check if mobil stock is available
    $get_stock_sql = "SELECT stock FROM mobil WHERE mobil_id = ?";
    $stmt_get_stock = $conn->prepare($get_stock_sql);
    $stmt_get_stock->bind_param("i", $mobil_id);
    $stmt_get_stock->execute();
    $stmt_get_stock->bind_result($stock);
    $stmt_get_stock->fetch();
    $stmt_get_stock->close();

    // Check if stock is available
    if ($stock > 0) {
        // Proceed with order insertion
        $user_id = $_SESSION['user_id']; // Assuming you fetch this during login

        if ($payment_type == 'Credit') {
            // Calculate monthly installment
            $loan_amount = $total_price - $down_payment;
            $monthly_installment = $loan_amount / 12; // Assuming 12 months installment

            $insert_sql = "INSERT INTO sales (user_id, customer_id, mobil_id, sale_date, total_price, down_payment, payment_status, payment_type, monthly_installment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $payment_status = 'Pending'; // Default payment status for new orders
            $stmt->bind_param("iiisdsdsd", $user_id, $customer_id, $mobil_id, $sale_date, $total_price, $down_payment, $payment_status, $payment_type, $monthly_installment);
        } else {
            // For Cash transaction
            $insert_sql = "INSERT INTO sales (user_id, customer_id, mobil_id, sale_date, total_price, payment_status, payment_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $payment_status = 'Pending'; // Default payment status for new orders
            $stmt->bind_param("iiisdss", $user_id, $customer_id, $mobil_id, $sale_date, $total_price, $payment_status, $payment_type);
        }

        if ($stmt->execute()) {
            $success_message = "New order added successfully.";

            // Update mobil stock
            $update_stock_sql = "UPDATE mobil SET stock = stock - 1 WHERE mobil_id = ?";
            $stmt_update_stock = $conn->prepare($update_stock_sql);
            $stmt_update_stock->bind_param("i", $mobil_id);
            $stmt_update_stock->execute();
            $stmt_update_stock->close();

        } else {
            $error_message = "Failed to add new order: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Handle case where stock is 0
        $error_message = "Failed to add new order: mobil is out of stock.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <h1>Add New Order</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
   
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
<?php
$conn->close();
?>
