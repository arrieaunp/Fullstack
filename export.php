<?php
session_start();
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$secret_key = $_ENV['SECRETKEY'];

if (!isset($_COOKIE['token'])) {
  header('location:index.php');
  exit();
}

try {
    $decoded = JWT::decode($_COOKIE['token'], new Key($secret_key, 'HS256'));
    // $CustName = $decoded->data->CustName;
    // $Address = $decoded->data->Address;
    // $Tel = $decoded->data->Tel;
  } catch (Exception $e) {
    header('location:login.html');
    exit();
  }

if (isset($_POST['export_format']) && $_POST['export_format'] == 'pdf') {
    if (!isset($_SESSION['OrderId']) || !isset($_SESSION['OrderDate']) || !isset($_SESSION['CustName']) || !isset($_SESSION['Address']) || !isset($_SESSION['product_details']) || !isset($_SESSION['OrderTotal'])) {
        die('Session data is missing. Cannot generate PDF.');
    }

    if (!is_array($_SESSION['product_details'])) {
        die('Error: Product details are not available or invalid.');
    }

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Invoice');

    $pdf->AddPage();

    $pdf->SetFont('thsarabun', '', 12);

    $html = '<h1>Invoice</h1>';
    $html .= '<p>Order ID: ' . $_SESSION["OrderId"] . '</p>';
    $html .= '<p>Order Date: ' . $_SESSION["OrderDate"] . '</p>';
    $html .= '<p>Customer Name: ' . $_SESSION["CustName"] . '</p>';
    $html .= '<p>Address: ' . $_SESSION["Address"] . '</p>';

    $html .= '<table border="1">';
    $html .= '<tr><th>Product Code</th><th>Product Name</th><th>Unit Price</th><th>Quantity</th><th>Total Price</th></tr>';
    foreach ($_SESSION["product_details"] as $product) {
        $html .= '<tr>';
        $html .= '<td>' . $product["code"] . '</td>';
        $html .= '<td>' . $product["name"] . '</td>';
        $html .= '<td>' . $product["price"] . '</td>';
        $html .= '<td>' . $product["quantity"] . '</td>';
        $html .= '<td>' . ($product["price"] * $product["quantity"]) . '</td>';
        $html .= '</tr>';
    }
    $html .= '<tr><td colspan="4">Total</td><td>' . $_SESSION["OrderTotal"] . '</td></tr>';
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output('invoice.pdf', 'D');
    exit;
} else {
    echo 'Unsupported export format.';
    exit;
}