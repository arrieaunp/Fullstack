<?php
    $a1 = $_GET['a1'];

    /* get connection */
    $conn = mysqli_connect("localhost", "root", "", "mydb");
    /* run query to get data based on a1 */
    $stmt = mysqli_query($conn, "SELECT * FROM Cust WHERE CustNo = '$a1'");


while ($row = mysqli_fetch_array($stmt)) {
    $name1 = $row["CustNo"];
    $name2 = $row["CustName"];
    $name3 = $row["Sex"];
    $name4 = $row["Address"];
    $name5 = $row["Tel"];
}    
mysqli_close($conn);
?>

<html>
<form method="get" action="SaveDB_Customer.php">
    <h1>

        <input type="hidden" name="a1" value="<?php echo $name1 ?>">
        <?php echo $name1 ?>
        ชื่อ 
        <input type="text" name="a2" size="20" maxlength="20" value="<?php echo $name2 ?>"><br>
        เพศ 
        <input type="text" name="a3" size="1" maxlength="1" value="<?php echo $name3 ?>"><br>
        ที่อยู่ 
        <input type="text" name="a4" size="50" maxlength="80" value="<?php echo $name4 ?>"><br>
        เบอร์โทรศัพท์ 
        <input type="text" name="a5" size="10" maxlength="10" value="<?php echo $name5 ?>"><br>

        <input type="submit" value="ยืนยัน">
        <input type="reset" value="ยกเลิก">
    </h1>
</form>

</body>
</html>