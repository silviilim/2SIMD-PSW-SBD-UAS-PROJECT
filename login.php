<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure'   => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

include 'connection.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Akses ilegal terdeteksi (CSRF Token Invalid).");
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Username and Password Must Be Filled!";
    } else {

        $sql_admin = "SELECT admin_id, username, password FROM maru_bake_house.dbo.admin WHERE username = ?";
        $stmt = sqlsrv_query($conn, $sql_admin, array($username));

        if ($stmt === false) {
            die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
        }

        if ($row_admin = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            

            $is_password_correct = false;
            if (password_verify($password, $row_admin['password'])) {
                $is_password_correct = true;
            } elseif ($password === $row_admin['password']) {
                $is_password_correct = true;
            }

            if ($is_password_correct) {

                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $row_admin['admin_id'];
                $_SESSION['admin_username'] = $row_admin['username'];


                unset($_SESSION['csrf_token']);

                header("Location: admin.php");
                exit();
            } else {
                $error_message = "Username / Password Wrong Please Try Again.";
            }
        } else {
            $error_message = "Username / Password Wrong Please Try Again.";
        }
    }
}
?>

<?php
$show_error = false;

if (isset($_POST['login'])) {

    
    if ($login_gagal) {
        $show_error = true;
    } else {

    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Maru Bake House</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #7A1E13; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }


        .top-admin-icon {
            position: absolute;
            top: 30px;
            right: 40px;
            color: rgba(242, 234, 211, 0.6);
            font-size: 32px;
            cursor: pointer;
        }

        .login-container {
            width: 100%;
            max-width: 540px;
            text-align: center;
        }


        .brand-logo-container {
            margin-bottom: 25px;
        }
        .brand-logo-main {
            font-size: 48px;
            font-weight: 800;
            color: #F2EAD3;
            line-height: 1;
            letter-spacing: 1px;
        }
        .brand-logo-sub {
            font-size: 11px;
            font-weight: 700;
            color: #F2EAD3;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-top: 5px;
        }


        .login-card-custom {
            background-color: #F2EAD3;
            padding: 50px 55px;
            border-radius: 40px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            text-align: left;
            position: relative;
            overflow: hidden;
        }


        .login-card-custom::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 140px;
            height: 70px;
            background-color: #7A1E13; 
            border-bottom-left-radius: 70px;
            border-bottom-right-radius: 70px;
            z-index: 1;
        }

        .login-card-custom form {
            margin-top: 25px; 
        }

        .form-group-custom {
            margin-bottom: 25px;
        }

        .form-group-custom label {
            display: block;
            font-weight: 700;
            font-size: 14px;
            color: #581C14;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group-custom input {
            width: 100%;
            padding: 14px 25px;
            border: 1px solid #7A1E13;
            border-radius: 30px;
            font-size: 14px;
            color: #581C14;
            background-color: transparent;
            outline: none;
            font-style: italic;
        }

        .form-group-custom input::placeholder {
            color: #AB826A;
            opacity: 0.7;
        }


        .alert-custom {
            background-color: #7A1E13;
            color: #FFFFFF;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }


        .instruction-text {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #581C14;
            font-style: italic;
            margin-bottom: 20px;
            margin-top: 10px;
        }


        .btn-login-custom {
            background-color: #581C14; 
            color: #FFFFFF;
            border: none;
            padding: 14px;
            width: 100%;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            border-radius: 30px; 
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .btn-login-custom:hover {
            background-color: #40130E;
            transform: translateY(-2px);
        }


        .copyright-text {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: rgba(242, 234, 211, 0.6);
            font-weight: 300;
        }
    </style>
</head>
<body>

<div class="top-admin-icon"><i class="fa-solid fa-user-large"></i></div>

<div class="login-container">
    
    <div class="brand-logo-container">
        <div class="brand-logo-main">MARU</div>
        <div class="brand-logo-sub">Bake House</div>
    </div>

    <div class="login-card-custom">
        
        <?php if (!empty($error_message)): ?>
            <div class="alert-custom">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="form-group-custom">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Input your username admin" required autofocus>
            </div>
            
            <div class="form-group-custom">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Input your password" required>
            </div>
            
            
            <button type="submit" class="btn-login-custom">Login</button>
        </form>
    </div>

    <div class="copyright-text">@2026 Maru Bake House. All rights reserved</div>
</div>

</body>
</html>