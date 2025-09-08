<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['vamsaid'] == 0)) {
    header('location:logout.php');
    exit;
}

// Handle Add
if (isset($_POST['add'])) {
    $scrap_name = $_POST['scrap_name'];
    $price = $_POST['price'];
    $unit_type = $_POST['unit_type'];

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $image = str_replace(' ', '_', $_FILES['image']['name']);
        $image_tmp = $_FILES['image']['tmp_name'];
        $target_path = "images/" . $image;
        if (!is_dir("images")) mkdir("images", 0777, true);
        move_uploaded_file($image_tmp, $target_path);
    }

    $query = "INSERT INTO scrap_price_list (scrap_name, price, unit_type, image) 
              VALUES (:scrap_name, :price, :unit_type, :image)";
    $stmt = $dbh->prepare($query);
    $stmt->execute([
        ':scrap_name' => $scrap_name,
        ':price' => $price,
        ':unit_type' => $unit_type,
        ':image' => $image
    ]);
    header("Location: admin_price_list.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $dbh->prepare("DELETE FROM scrap_price_list WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    header("Location: admin_price_list.php");
    exit;
}

// Handle Edit Fetch
$editData = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $dbh->prepare("SELECT * FROM scrap_price_list WHERE id = :id");
    $stmt->bindParam(':id', $edit_id);
    $stmt->execute();
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $scrap_name = $_POST['scrap_name'];
    $price = $_POST['price'];
    $unit_type = $_POST['unit_type'];

    if (!empty($_FILES['image']['name'])) {
        $image = str_replace(' ', '_', $_FILES['image']['name']);
        $image_tmp = $_FILES['image']['tmp_name'];
        $target_path = "images/" . $image;
        move_uploaded_file($image_tmp, $target_path);

        $query = "UPDATE scrap_price_list SET scrap_name = :scrap_name, price = :price, unit_type = :unit_type, image = :image WHERE id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->execute([
            ':scrap_name' => $scrap_name,
            ':price' => $price,
            ':unit_type' => $unit_type,
            ':image' => $image,
            ':id' => $id
        ]);
    } else {
        $query = "UPDATE scrap_price_list SET scrap_name = :scrap_name, price = :price, unit_type = :unit_type WHERE id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->execute([
            ':scrap_name' => $scrap_name,
            ':price' => $price,
            ':unit_type' => $unit_type,
            ':id' => $id
        ]);
    }
    header("Location: admin_price_list.php");
    exit;
}

// Fetch all scrap items
$stmt = $dbh->prepare("SELECT * FROM scrap_price_list");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <title>Admin - Scrap Price List</title>

    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>
    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="#">Scrap Price List Management</a>
            </nav>

            <div class="container-fluid">
                <!-- Form Container -->
                <div class="card mt-4">
                    <div class="header">
                        <h2><?= $editData ? 'Edit Scrap Item' : 'Add New Scrap Item' ?></h2>
                    </div>
                    <div class="body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                            <div class="form-group">
                                <input type="text" name="scrap_name" class="form-control" placeholder="Scrap Name" value="<?= $editData['scrap_name'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <input type="text" name="price" class="form-control" placeholder="Price (e.g., 25)" value="<?= $editData['price'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <input type="text" name="unit_type" class="form-control" placeholder="Unit (e.g., per KG)" value="<?= $editData['unit_type'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="form-group">
                                <?php if ($editData): ?>
                                    <button type="submit" name="update" class="btn btn-warning">Update</button>
                                    <a href="admin_price_list.php" class="btn btn-secondary">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add" class="btn btn-success">Add</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="card">
                    <div class="header">
                        <h2>Scrap Price List</h2>
                    </div>
                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th>Scrap Name</th>
                                        <th>Price (₹)</th>
                                        <th>Unit</th>
                                        <th>Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($result as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['scrap_name']) ?></td>
                                        <td>₹<?= htmlspecialchars($row['price']) ?></td>
                                        <td><?= htmlspecialchars($row['unit_type']) ?></td>
                                        <td>
                                            <?php if (!empty($row['image']) && file_exists("images/" . $row['image'])): ?>
                                                <img src="images/<?= htmlspecialchars($row['image']) ?>" width="60">
                                            <?php else: ?>
                                                No image
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div> <!-- page -->
    </div> <!-- main_content -->

<!-- JS -->
<script src="../assets/bundles/libscripts.bundle.js"></script>
<script src="../assets/bundles/vendorscripts.bundle.js"></script>
<script src="../assets/bundles/datatablescripts.bundle.js"></script>
<script src="../assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
<script src="../assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/pages/tables/jquery-datatable.js"></script>
</body>
</html>
