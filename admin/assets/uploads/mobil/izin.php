<?php
$folder = 'admin/assets/uploads/mobil/';
if (is_writable($folder)) {
    echo "Folder is writable.";
} else {
    echo "Folder is not writable. Please check permissions.";
}
