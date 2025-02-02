<?php
session_start();
require_once '../config.php';

// Check if the user is logged in and has the right access
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get the mobil ID from the URL parameter
$mobil_id = $_GET['id'] ?? null;

if (!$mobil_id) {
    $_SESSION['error_message'] = "Invalid mobil ID.";
    header("Location: manage_mobil.php");
    exit();
}

// Fetch mobil data from the database
$sql = "SELECT * FROM mobil WHERE mobil_id=?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['error_message'] = "Database error.";
    header("Location: manage_mobil.php");
    exit();
}
$stmt->bind_param("i", $mobil_id);
$stmt->execute();
$result = $stmt->get_result();
$mobil = $result->fetch_assoc();

if (!$mobil) {
    $_SESSION['error_message'] = "mobil not found.";
    header("Location: manage_mobil.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Mobil</title>
    <?php include 'template/header.php'; ?>
    <link rel="stylesheet" href="path/to/bootstrap.css">
    <script src="path/to/bootstrap.bundle.js"></script>
</head>

<body>
    <div class="wrapper">
        <?php include 'template/sidebar.php'; ?>
        <main id="main" class="main">
            <?php include 'template/nav.php'; ?>

            <div class="pagetitle">
                <h1>View Mobil</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active">View Mobil</li>
                    </ol>
                </nav>
            </div>

            <section class="section">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title">Mobil Details</h5>
                            <a href="manage_mobil.php" class="btn btn-primary mt-3 mb-3">Back to mobil</a>
                        </div>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mobil_id" class="form-label">ID</label>
                                        <input type="text" id="mobil_id" class="form-control" value="<?php echo htmlspecialchars($mobil['mobil_id']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="model" class="form-label">MODEL</label>
                                        <input type="text" id="model" class="form-control" value="<?php echo htmlspecialchars($mobil['model']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="brand" class="form-label">BRAND</label>
                                        <input type="text" id="brand" class="form-control" value="<?php echo htmlspecialchars($mobil['brand']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">PRICE</label>
                                        <input type="text" id="price" class="form-control" value="<?php echo htmlspecialchars($mobil['price']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">STOCK</label>
                                        <input type="text" id="stock" class="form-control" value="<?php echo htmlspecialchars($mobil['stock']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="warna" class="form-label">WARNA</label>
                                        <input type="text" id="warna" class="form-control" value="<?php echo htmlspecialchars($mobil['warna']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="foto" class="form-label">FOTO</label>
                                        <img src="<?php echo htmlspecialchars($mobil['foto']); ?>" alt="foto Image" width="100" data-bs-toggle="modal" data-bs-target="#fotolModal" style="cursor: pointer;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Foto Modal -->
    <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fotoModalLabel">Foto Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="<?php echo htmlspecialchars($mobil['foto']); ?>" alt="foto Image" class="img-fluid">
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