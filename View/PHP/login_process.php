<?php
session_start();
// Since db_connect.php is in the same folder (View/PHP/)
require_once('db_connect.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = mysqli_real_escape_string($conn, $_POST['username']);
    $pass_input = $_POST['password']; 

    $sql = "SELECT admin_id, admin_name, password, authority_level FROM admins WHERE admin_name = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $user_input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($admin = mysqli_fetch_assoc($result)) {
        if (password_verify($pass_input, $admin['password'])) {
            session_regenerate_id();
            
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['admin_name'];
            $_SESSION['authority_level'] = $admin['authority_level'];

            $update_sql = "UPDATE admins SET updated_at = NOW() WHERE admin_id = ?";
            $up_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($up_stmt, "i", $admin['admin_id']);
            mysqli_stmt_execute($up_stmt);


            header("Location: admin.php");
            exit();
        } else {
            header("Location: login.php?error=invalid");
            exit();
        }
    } else {
        header("Location: login.php?error=invalid");
        exit();
    }
}
?>  