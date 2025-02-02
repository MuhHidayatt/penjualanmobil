<aside id="sidebar" class="js-sidebar">
    <!-- Content For Sidebar -->
    <div class="h-100">
        <div class="sidebar-logo">
            <a href="#">Admin Dashboard</a>
        </div>
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="http://localhost/penjualan/mobil/admin/index.php" class="sidebar-link">
                    <i class="lni lni-grid-alt"></i>
                    Dashboard
                </a>    
            </li>
            <li class="sidebar-item">
                <a href="http://localhost/penjualan/mobil/admin/manage_mobil.php" class="sidebar-link">
                    <i class="lni lni-webhooks"></i>
                    Mobil
                </a>
            </li>
            <li class="sidebar-item">
                <a href="http://localhost/penjualan/mobil/admin/manage_customers.php" class="sidebar-link">
                    <i class="lni lni-customer"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="http://localhost/penjualan/mobil/admin/manage_orders.php" class="sidebar-link">
                    <i class="lni lni-dollar"></i>
                    <span>Sale</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="http://localhost/penjualan/mobil/admin/sales_reports.php" class="sidebar-link">
                    <i class="lni lni-stats-up"></i>
                    <span>Sales Report</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="http://localhost/penjualan/mobil/admin/manage_users.php" class="sidebar-link">
                    <i class="lni lni-users"></i>
                    <span>Users</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="#" class="sidebar-link" id="logout-link">
                <i class="lni lni-exit"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</aside>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('logout-link').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default action

        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'http://localhost/penjualan/mobil/logout.php'; // Redirect to logout
            }
        });
    });
</script>
