<?php
include "../db_config.php";
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (isset($_COOKIE['token'])) {
    $jwt = $_COOKIE['token'];
    $secret_key = $_ENV['SECRETKEY'];

    try {
        $decoded = JWT::decode($_COOKIE['token'], new Key($secret_key, 'HS256'));
        if ($decoded->data->Role == "admin" || $decoded->data->Role == "superadmin") {
            
        } else {
            echo "Error: You don't have permission to access this page.";
            //header("Location: ../login.html");
            exit();
        }
    } catch (Exception $e) {
        "Error: " . $e->getMessage();
        //header("Location: ../login.html");
        exit();
    }
} else {
    echo "Error: Token not found.";
    //header("Location: ../login.html");
    exit();
}

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
} else {
    header("Location: Adminpage.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        $order_date = mysqli_real_escape_string($conn, $_POST['OrderDate']);
        $cust_name = mysqli_real_escape_string($conn, $_POST['CustName']);
        $address = mysqli_real_escape_string($conn, $_POST['Address']);
        if (isset($_POST['status'])) {
            $status = mysqli_real_escape_string($conn, $_POST['status']);
        } else {
            $status = ''; 
        }

        $query = "UPDATE OrderHeader SET OrderDate='$order_date', CustName='$cust_name', Address='$address', OrderStatus='$status' WHERE OrderId='$order_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            header("Location: Adminpage.php");
            exit();
        } else {
            $error_message = "Failed to update order.";
        }
    } elseif (isset($_POST['delete'])) {
        $query = "DELETE FROM OrderHeader WHERE OrderId='$order_id'";
        $result = mysqli_query($conn, $query);

        if ($result) {
            header("Location: Adminpage.php");
            exit();
        } else {
            $error_message = "Failed to delete order.";
        }
    }
}

$query = "SELECT * FROM OrderHeader WHERE OrderId = '$order_id'";
$result = mysqli_query($conn, $query);

if ($result) {
    $order = mysqli_fetch_assoc($result);
} else {
    header("Location: Adminpage.php");
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <link rel="stylesheet" href="../AdminPage/Back_Styles/edit.css">
</head>
<body>
    <div class="container">
        <h1>Edit Order</h1>
        <?php if (isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=$order_id"; ?>" method="post">
        <?php if ($decoded->data->Role == "superadmin") { ?>
            <input type="hidden" name="OrderId" value="<?php echo $order['OrderId']; ?>">
            <label for="order_date">Order Date:</label>
            <input type="text" id="OrderDate" name="OrderDate" value="<?php echo $order['OrderDate']; ?>">
            <label for="cust_name">Customer Name:</label>
            <input type="text" id="CustName" name="CustName" value="<?php echo $order['CustName']; ?>">
            <label for="Address">Address:</label>
            <input type="text" id="Address" name="Address" value="<?php echo $order['Address']; ?>">
            <label>Status:</label><br>
                <input type="radio" name="status" value="Paid" <?php if ($order['OrderStatus'] == 'Paid') echo 'checked="checked"'; ?>> Paid<br>
                <input type="radio" name="status" value="Pending" <?php if ($order['OrderStatus'] == 'Pending') echo 'checked="checked"'; ?>> Pending<br>
            <?php } elseif ($decoded->data->Role == "admin") { ?>
                <label>Status:</label><br>
                <input type="radio" name="status" value="Paid" <?php if ($order['OrderStatus'] == 'Paid') echo 'checked="checked"'; ?>> Paid<br>
                <input type="radio" name="status" value="Pending" <?php if ($order['OrderStatus'] == 'Pending') echo 'checked="checked"'; ?>> Pending<br>
            <?php } ?>
            
            <button type="submit" name="update" class="btn">Update</button>
            <?php if ($decoded->data->Role == "superadmin") { ?>
                <button type="submit" name="delete" class="btn delete" onclick="return confirm('Are you sure you want to delete this order?')">Delete</button>
            <?php } ?>
        </form>
    </div>
</body>
</html>

