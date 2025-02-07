    <?php
    session_start();

    // Check if the user is logged in and is an admin
    if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
        header("Location: login.php");
        exit();
    }

    require_once '../config.php';

    // Initialize error message variable
    $error = "";

    // Handle form submissions for adding/editing mobil
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $mobil_id = $_POST['mobil_id'] ?? null;
        $model = $_POST['model'];
        $brand = $_POST['brand'];
        $price = $_POST['price'];
        $warna = $_POST['warna'];
        $stock = $_POST['stock'];
    

        // Default values for foto
        $foto_upload_path = null;

        // Handle foto upload if a new file is provided
        if (!empty($_FILES['foto']['name'])) {
            $foto = $_FILES['foto']['name'];
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_upload_path = 'assets/uploads/mobil/' . basename($foto);
            if (move_uploaded_file($foto_tmp, $foto_upload_path)) {
                echo "File uploaded to: " . $foto_upload_path;
            } else {
                echo "Failed to upload file.";
            }
        }
        

        if ($mobil_id) {
            // Update existing mobil
            $sql = "SELECT foto FROM mobil WHERE mobil_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $mobil_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $mobil = $result->fetch_assoc();

            // Use existing paths if no new files are uploaded
            if (!$foto_upload_path) {
                $foto_upload_path = $mobil['foto'];
            }

            $sql = "UPDATE mobil SET model=?, brand=?, price=?, warna=?, stock=?, foto=? WHERE mobil_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdsisi", $model, $brand, $price, $warna, $stock, $foto_upload_path, $mobil_id);
        } else {
            // Add new mobil
            $sql = "INSERT INTO mobil (model, brand, price, warna, stock, foto) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdis", $model, $brand, $price, $warna, $stock, $foto_upload_path);

        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Mobil saved successfully!";
            header("Location: manage_mobil.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error saving mobil: " . $stmt->error;
        }
    }
    

    // Handle deletion of mobil
    if (isset($_GET['delete'])) {
        $mobil_id = $_GET['delete'];
        $sql = "DELETE FROM mobil WHERE mobil_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $mobil_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "mobil deleted successfully!";
            header("Location: manage_mobil.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error deleting mobil: " . $stmt->error;
        }
    }

    // Fetch mobil from the database
    $sql = "SELECT * FROM mobil";
    $result = $conn->query($sql);
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Mobil</title>
    <?php include 'template/header.php'; ?>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            text-align: center; /* Mengatur agar teks berada di tengah secara horizontal */
            vertical-align: middle; /* Mengatur agar teks berada di tengah secara vertikal */
            padding: 10px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        img {
            display: block;
            margin: 0 auto; /* Agar gambar di tengah */
            max-width: 100px;
            height: auto;
        }

        .btn {
            margin: 0 5px;
        }
    </style>
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
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-primary mb-3 mt-3" data-bs-toggle="modal" data-bs-target="#mobilModal">Tambah Data</button>
                                        </div>

                                        <div class="overflow-x-auto">
                                                <table class="table table-bordered display" id="mobilTable" style="text-align: center;">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th>NO</th>
                                                            <th>ID</th>
                                                            <th>Model</th>
                                                            <th>Brand</th>
                                                            <th>Price</th>
                                                            <th>Stock</th>
                                                            <th>Warna</th>
                                                            <th>Gambar</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                <tbody>
                                                    <?php $no = 1; ?>
                                                    <?php while ($row = $result->fetch_assoc()) : ?>
                                                        <tr class="text-center">
                                                            <td class="text-center"><?php echo $no++; ?></td>
                                                            <td><?php echo htmlspecialchars($row['mobil_id']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['model']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                                            <td class="price"><?php echo htmlspecialchars($row['price']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['warna']); ?></td>
                                                            <td>
                                                            <?php if (!empty($row['foto'])): ?>
                                                                <img src="<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto Mobil" style="width: 100px; height: auto;">
                                                            <?php else: ?>
                                                                <span>No Image</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                                <button type="button" class="btn btn-info btn-sm view-btn" data-id="<?php echo $row['mobil_id']; ?>">View</button>
                                                                <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['mobil_id']; ?>" data-model="<?php echo htmlspecialchars($row['model']); ?>" data-brand="<?php echo htmlspecialchars($row['brand']); ?>" data-price="<?php echo htmlspecialchars($row['price']); ?>" data-stock="<?php echo htmlspecialchars($row['stock']); ?>" data-warna="<?php echo htmlspecialchars($row['warna']); ?>"data-foto="<?php echo $row['foto']; ?>" data-bs-toggle="modal" data-bs-target="#mobilModal">Edit</button>
                                                                <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['mobil_id']; ?>">Delete</button>
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
                </section>

                <!-- Add/Edit mobil Modal -->
                <div class="modal fade" id="mobilModal" tabindex="-1" aria-labelledby="mobilModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST" action="manage_mobil.php">
                                <input type="hidden" name="mobil_id" id="mobil_id">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="mobilModalLabel">Data Mobil</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" class="form-control" name="model" id="model" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="brand" class="form-label">Brand</label>
                                        <input type="text" class="form-control" name="brand" id="brand" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <input type="text" class="form-control" name="price" id="price" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stock</label>
                                        <input type="number" class="form-control" name="stock" id="stock" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="warna" class="form-label">Warna</label>
                                        <input type="text" class="form-control" name="warna" id="warna" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="foto" class="form-label">Foto (Upload Image)</label>
                                        <input type="file" class="form-control" name="foto" id="foto" accept="image/*">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </main>
        </div>

        <?php include 'template/footer.php'; ?>

        <script>
            $(document).ready(function() {
                const sidebarToggle = document.querySelector("#sidebar-toggle");
                sidebarToggle.addEventListener("click", function() {
                    document.querySelector("#sidebar").classList.toggle("collapsed");
                });

                $('#mobilTable').DataTable();

                $('.view-btn').click(function() {
                    const mobilId = $(this).data('id');
                    window.location.href = 'view_mobil.php?id=' + mobilId;
                });

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
                    $('#mobil_id').val($(this).data('id'));
                    $('#model').val($(this).data('model'));
                    $('#brand').val($(this).data('brand'));
                    $('#price').val($(this).data('price'));
                    $('#stock').val($(this).data('stock'));
                    $('#warna').val($(this).data('warna'));
                    $('#MobilModal').modal('show');
                });

                // Clear modal fields when opening for a new mobil
                $('#mobilModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    if (!button.hasClass('edit-btn')) {
                        $('#mobil_id').val('');
                        $('#model').val('');
                        $('#brand').val('');
                        $('#price').val('');
                        $('#stock').val('');
                        $('#warna').val('');
                        $('#foto').val('');
                    }
                });

                // Handle delete button click
                $('.delete-btn').click(function() {
                    var mobil_id = $(this).data('id');
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
                            window.location.href = 'manage_mobil.php?delete=' + mobil_id;
                        }
                    });
                });

                // Format prices in IDR
                const priceFormatter = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                });

                $('.price').each(function() {
                    const price = parseFloat($(this).text().replace(/[^0-9.-]+/g, ""));
                    $(this).text(priceFormatter.format(price));
                });
            });
        </script>

    </body>

    </html>