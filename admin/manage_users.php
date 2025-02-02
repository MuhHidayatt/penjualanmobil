<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config.php';

// Handle form submissions for adding/editing users
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Handle password
    if ($user_id) {
        // Check if the password field is empty
        if (empty($_POST['password'])) {
            // Fetch the existing password
            $sql = "SELECT password FROM users WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($password);
            $stmt->fetch();
            $stmt->close();
        } else {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        // Update existing user
        $sql = "UPDATE users SET nama=?, username=?, password=?, role=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nama, $username, $password, $role, $user_id);
    } else {
        // Add new user
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nama, $username, $password, $role);
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User saved successfully.";
        header("Location: manage_users.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error saving user: " . $stmt->error;
    }
}

// Handle deletion of users
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $_SESSION['success_message'] = "User deleted successfully.";
    header("Location: manage_users.php");
    exit();
}

// Fetch users from the database
$sql = "SELECT user_id, nama, username, role, created_at FROM users";
$result = $conn->query($sql);

if (!$result) {
    $_SESSION['error_message'] = "Error fetching users: " . $conn->error;
    $result = []; // Ensure $result is defined as an empty array
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <?php include 'template/header.php'; ?>
</head>

<body>
    <div class="wrapper">
        <?php include 'template/sidebar.php'; ?>
        <main id="main" class="main">
            <?php include 'template/nav.php'; ?>

            <div class="pagetitle">
                <h1 class="mb-2">Data Users</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Home</a></li>
                        <li class="breadcrumb-item active">Users</li>
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
                                        <button type="button" class="btn btn-primary mb-3 mt-3" data-bs-toggle="modal" data-bs-target="#UserModal">Tambah Users</button>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="table table-bordered display overflow-scroll" id="userTable">
                                            <thead>
                                                <tr>
                                                    <th>NO</th>
                                                    <th>ID</th>
                                                    <th>Nama</th>
                                                    <th>Username</th>
                                                    <th>Role</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1; // Initialize counter ?>
                                                <?php while ($row = $result->fetch_assoc()) : ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td> <!-- Use counter for number -->
                                                        <td><?php echo $row['user_id']; ?></td>
                                                        <td><?php echo $row['nama']; ?></td>
                                                        <td><?php echo $row['username']; ?></td>
                                                        <td><?php echo $row['role']; ?></td>
                                                        <td><?php echo $row['created_at']; ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['user_id']; ?>" data-nama="<?php echo $row['nama']; ?>" data-username="<?php echo $row['username']; ?>" data-role="<?php echo $row['role']; ?>" data-bs-toggle="modal" data-bs-target="#UserModal">Edit</button>
                                                            <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['user_id']; ?>">Delete</button>
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

                <div class="modal fade" id="UserModal" tabindex="-1" aria-labelledby="UserModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST" action="manage_users.php">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="UserModalLabel">User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="user_id" id="user_id">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama</label>
                                        <input type="text" class="form-control" name="nama" id="nama" placeholder="Enter nama" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" name="username" id="username" placeholder="Enter username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select name="role" id="role" class="form-select" required>
                                            <option value="admin">Admin</option>
                                            <option value="sales">Sales</option>
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
        $(document).ready(function() {
            const sidebarToggle = document.querySelector("#sidebar-toggle");
            sidebarToggle.addEventListener("click", function() {
                document.querySelector("#sidebar").classList.toggle("collapsed");
            });

            $('#userTable').DataTable();

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
                $('#user_id').val($(this).data('id'));
                $('#nama').val($(this).data('nama'));
                $('#username').val($(this).data('username'));
                $('#role').val($(this).data('role'));
                $('#password').val(''); // Clear password field
                $('#UserModal').modal('show');
            });

            // Clear modal fields when opening the modal for adding a new user
            $('#UserModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                if (!button.hasClass('edit-btn')) {
                    $('#user_id').val('');
                    $('#nama').val('');
                    $('#username').val('');
                    $('#password').val('');
                    $('#role').val('admin'); // Default to 'admin'
                }
            });

            // Handle delete button click
            $('.delete-btn').click(function() {
                var userId = $(this).data('id');
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
                        window.location.href = 'manage_users.php?delete=' + userId;
                    }
                });
            });
        });
    </script>

</body>
</html>

