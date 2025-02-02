<?php
session_start();

// Check if the user is logged in and is a sales user
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'sales') {
    header("Location: login.php");
    exit();
}

require_once '../config.php';

// Fetch mobil data
$sql = "SELECT * FROM mobil";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sales Dashboard</title>
    <?php include 'template/header.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'template/sidebar.php'; ?>
        <main id="main" class="main">
            <?php include 'template/nav.php'; ?>

            <div class="pagetitle">
                <h1 class="mb-2">Data Mobil</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Mobil</li>
                    </ol>
                </nav>
            </div>

            <section class="section dashboard">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">View Data Mobil</h5>

                                    <div class="overflow-x-auto">
                                        <table class="table table-bordered display" id="mobilTable">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>NO</th>
                                                    <th>ID</th>
                                                    <th>Model</th>
                                                    <th>Brand</th>
                                                    <th>Price</th>
                                                    <th>Stock</th>
                                                    <th>Warna</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; // Initialize counter ?>
                                                <?php while ($row = $result->fetch_assoc()) : ?>
                                                    <tr class="text-center">
                                                        <td><?php echo $no++; ?></td>
                                                        <td><?php echo htmlspecialchars($row['mobil_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['model']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                                        <td class="price"><?php echo htmlspecialchars($row['price']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['stock']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['warna']); ?></td>
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
        <!-- End #main -->
    </div>

    <?php include 'template/footer.php'; ?>

    <script>
        const sidebarToggle = document.querySelector("#sidebar-toggle");
        sidebarToggle.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });


        $('#mobilTable').DataTable();

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

<?php
$conn->close();
?>