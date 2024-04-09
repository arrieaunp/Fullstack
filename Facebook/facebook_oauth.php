<?php
session_start();
include "../db_config.php";

$facebook_oauth_app_id = '3581058565541846';
$facebook_oauth_app_secret = '68379a0efad195ca76ab3be329a7fdbf';
$facebook_oauth_redirect_uri = 'http://localhost/bb/Fullstack/Facebook/facebook_oauth.php';
$facebook_oauth_version = 'v18.0';

// If the captured code param exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
   // Execute cURL request to retrieve the access token
   $params = [
      'client_id' => $facebook_oauth_app_id,
      'client_secret' => $facebook_oauth_app_secret,
      'redirect_uri' => $facebook_oauth_redirect_uri,
      'code' => $_GET['code']
  ];
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/oauth/access_token');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);
  $response = json_decode($response, true);
   // Make sure access token is valid
   if (isset($response['access_token']) && !empty($response['access_token'])) {
      // Execute cURL request to retrieve the user info associated with the Facebook account
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/' . $facebook_oauth_version . '/me?fields=name,email,picture');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
      $response = curl_exec($ch);
      curl_close($ch);
      $profile = json_decode($response, true);
       // Make sure the profile data exists
      if (isset($profile['email'])) {
         $Email = mysqli_real_escape_string($conn, $profile['email']);
         $query = "SELECT * FROM Cust WHERE Email = '$Email'";
         $result = mysqli_query($conn, $query);

         if (mysqli_num_rows($result) == 0) {
            $CustName = mysqli_real_escape_string($conn, $profile['name']);


            $query = "INSERT INTO Cust (Email, CustName) 
                      VALUES ('$Email', '$CustName')";

            if (mysqli_query($conn, $query)) {
                echo "เพิ่มข้อมูลลูกค้าใหม่เข้าสู่ระบบสำเร็จ";
            } else {
                echo "มีข้อผิดพลาดในการเพิ่มข้อมูลลูกค้าใหม่: " . mysqli_error($conn);
            }
        }
        $row = mysqli_fetch_assoc($result);
        $_SESSION['CustNo'] = $row['CustNo'];        

        mysqli_close($conn);  

        $facebook_name_parts = [];
        $facebook_name_parts[] = isset($profile['given_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['given_name']) : '';
        $facebook_name_parts[] = isset($profile['family_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['family_name']) : '';

         // Authenticate the user
         session_regenerate_id();
         $_SESSION['facebook_loggedin'] = TRUE;
         $_SESSION['facebook_email'] = $profile['email'];
         $_SESSION['facebook_name'] = $profile['name'];
         // Redirect to profile page
         header('Location: ../index.php');
         exit;
      } else {
         exit('Could not retrieve profile information! Please try again later!');
   }
} else {
   exit('Invalid access token! Please try again later!');
}

} else {
   // Define params and redirect to Facebook OAuth page
   $params = [
       'client_id' => $facebook_oauth_app_id,
       'redirect_uri' => $facebook_oauth_redirect_uri,
       'response_type' => 'code',
       'scope' => 'email'
   ];
   header('Location: https://www.facebook.com/dialog/oauth?' . http_build_query($params));
   exit;
}


