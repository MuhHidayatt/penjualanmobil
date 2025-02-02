<?php
session_start();
require_once '../config.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'sales') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '2000-01-01';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

// Fetch detailed sales data
$sql_sales_details = "SELECT s.sale_id, c.name AS customer_name, m.model AS mobil_model, s.sale_date, s.total_price, s.payment_type
                      FROM sale s
                      JOIN users u ON s.user_id = u.user_id
                      JOIN customers c ON s.customer_id = c.customer_id
                      JOIN mobil m ON s.mobil_id = m.mobil_id
                      WHERE u.username = ? AND s.sale_date BETWEEN ? AND ?";
$stmt_sales_details = $conn->prepare($sql_sales_details);
$stmt_sales_details->bind_param("sss", $username, $start_date, $end_date);

if (!$stmt_sales_details->execute()) {
    echo "SQL Error: " . $stmt_sales_details->error;
    exit();
}

$result_sales_details = $stmt_sales_details->get_result();

if ($result_sales_details->num_rows === 0) {
    echo "No sales data found for the specified dates.";
    exit();
}

// Fetch total sales and total orders
$sql_sales = "SELECT SUM(total_price) AS total_sales, COUNT(sale_id) AS total_orders
              FROM sale s
              JOIN users u ON s.user_id = u.user_id
              WHERE u.username = ? AND s.sale_date BETWEEN ? AND ?";
$stmt_sales = $conn->prepare($sql_sales);
$stmt_sales->bind_param("sss", $username, $start_date, $end_date);
$stmt_sales->execute();
$result_sales = $stmt_sales->get_result();

$total_sales = 0;
$total_orders = 0;

if ($result_sales->num_rows > 0) {
    $row_sales = $result_sales->fetch_assoc();
    $total_sales = isset($row_sales['total_sales']) ? (float)$row_sales['total_sales'] : 0;
    $total_orders = isset($row_sales['total_orders']) ? (int)$row_sales['total_orders'] : 0;
}

$stmt_sales->close();

// Initialize total variables
$total_price = 0;

// Generate HTML for PDF
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .signature-table {
            width: 100%;
            border: none
            margin-top: 2rem;
        }
        .signature-table td {
            text-align: center;
            border: none;
            padding: 40px;
            width: 50%;
        }
        .date {
            text-align: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sales Report</h1>
        <p>Total Orders: ' . $total_orders . '</p>
        <p>Total Price: <span class="total-price">' . number_format($total_sales, 2) . '</span></p>
        <table>
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Customer Name</th>
                    <th>mobil Model</th>
                    <th>Sale Date</th>
                    <th>Total Price</th>
                    <th>Payment Type</th>
                </tr>
            </thead>
            <tbody>';

while ($row = $result_sales_details->fetch_assoc()) {
    $row_total_price = $row['total_price'] ?? 0;
    $total_price += $row_total_price;

    $html .= '
                <tr>
                    <td>' . htmlspecialchars($row['sale_id']) . '</td>
                    <td>' . htmlspecialchars($row['customer_name']) . '</td>
                    <td>' . htmlspecialchars($row['mobil_model']) . '</td>
                    <td>' . htmlspecialchars($row['sale_date']) . '</td>
                    <td class="price">' . htmlspecialchars(number_format($row_total_price, 2)) . '</td>
                    <td>' . htmlspecialchars($row['payment_type']) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>

        <p class="date">Tangerang, ' . date('d-m-Y') . '</p>

        <table class="signature-table">
            <tr>
                <td>Kepala</td>
                <td>Sales</td>
            </tr>
            <tr>
                <td>Kepala Sales: [Nama Kepala Sales]</td>
                <td>' . htmlspecialchars($username) . '</td>
            </tr>
        </table>
    </div>
</body>
</html>';

// Initialize Dompdf
$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

// Load HTML content
ob_end_clean();
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the PDF
$dompdf->render();

// Output the generated PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="sales_report.pdf"');
$dompdf->stream("sales_report.pdf", ["Attachment" => false]);
?>
