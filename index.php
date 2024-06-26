<?php
include "db_config.php";
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;

function generateGuestJWT($guest_id) {
    $payload = array(
        'iat' => time(),
        'exp' => strtotime("+1 hour"),
        'data' => array(
            'UserId' => $guest_id,
            'Username' => "Guest",
            'Role' => "guest",
        ),
    );

    $secret_key = $_ENV['SECRETKEY'];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    return $jwt;
}

if (!isset($_COOKIE['token'])) {
    $guest_id = uniqid('guest_');
    $guest_jwt = generateGuestJWT($guest_id);

    setcookie("token", $guest_jwt, time() + 3600, "/", "", true, true);
    $insert_query = "INSERT INTO Cust (CustNo, Role, Email) VALUES (?, ?, ' ')";
    if ($stmt = mysqli_prepare($conn, $insert_query)) {
      mysqli_stmt_bind_param($stmt, "ss", $guest_id, $role);
      $role = 'guest';
      if (mysqli_stmt_execute($stmt)) {
    
      } else {
          echo "Error: " . mysqli_error($conn);
      }
      mysqli_stmt_close($stmt);
      echo '<meta http-equiv="refresh" content="0">';
  } else {
      echo "Error: " . mysqli_error($conn);
  }
    
}

include "header.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="Styles/Menu.css">
  <title>Menu</title>
</head>

<body>
  <center>
    <form method="GET" action="index.php">
      <input type="text" name="search" placeholder="ค้นหาสินค้า..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
      <button type="submit">ค้นหา</button>
    </form>
    <form method="GET" action="Cart.php">
    <?php

    if(isset($_GET['search'])) {
      $search = mysqli_real_escape_string($conn, $_GET['search']);
      $query = "SELECT * FROM `Stock` WHERE `ProductName` LIKE '%$search%'";
      $result = mysqli_query($conn, $query);
    } else {
      $result = mysqli_query($conn, "SELECT * FROM `Stock`");
    }

    if(mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_array($result)) {
          $name1 = $row["ProductCode"];
          $name2 = $row["ProductName"];
          $name3 = $row["PricePerUnit"];
          $name4 = $row["StockQty"];
          $name6 = $row["ProductImg"];

          echo "<div class='product-container'>",
                  "<div class='product-card'>",
                  "<img class='img' src='image/$name6'>",
                  "<p><b>$name2</b></p>",
                  "<p><b> ราคา : </b>$name3 บาท</p>",
                  "<p><b>สินค้าคงเหลือ : </b>$name4 ชิ้น</p>",
                  "<button type='submit' class='button button1' value='$name1' name='selected'> ซื้อทันที </button>",
                  "<button type='submit' class='button button1' value='$name1' name='selected'> เพิ่มลงตะกร้า </button>",
                  "</div>",
              "</div>";
      }
  } else {
      echo '<div class="center">No product found</div>';
  }
    ?>
  </center>

</body>
</html>
