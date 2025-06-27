<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $id = trim($_POST['identifier']);
        $pass = $_POST['password'];
        
        if (empty($id) || empty($pass)) {
            $error = 'Please fill in all fields.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email=? OR phone=?");
            $stmt->bind_param("ss", $id, $id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['user'] = $user;
                $_SESSION['last_activity'] = time();
                header("Location: dashboard.php");
                exit();
            } elseif ($user && $pass === $user['password']) {
                // Backward compatibility: upgrade plain text password to hash
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user['id']);
                $update_stmt->execute();
                
                $_SESSION['user'] = $user;
                $_SESSION['last_activity'] = time();
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid email/phone or password.';
            }
        }
    }
}

if (isset($_GET['registered'])) {
    $success = 'Registration successful! Please login.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans:400,600,700&display=swap');
        *
        {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Open Sans', sans-serif;
        }
        body
        {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #151f28;
        }
        .square
        {
            position: relative;
            width: 500px;
            height: 500px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .square i
        {
            position: absolute;
            inset: 0;
            border: 2px solid #fff;
            transition: 0.5s;
        }
        .square i:nth-child(1)
        {
            border-radius: 38% 62% 63% 37% / 41% 44% 56% 59%;
            animation: animate 6s linear infinite;
        }
        .square i:nth-child(2)
        {
            border-radius: 41% 44% 56% 59%/38% 62% 63% 37%;
            animation: animate 4s linear infinite;
        }
        .square i:nth-child(3)
        {
            border-radius: 41% 44% 56% 59%/38% 62% 63% 37%;
            animation: animate2 10s linear infinite;
        }
        .square:hover i
        {
            border: 6px solid var(--clr);
            filter: drop-shadow(0 0 20px var(--clr));
        }
        @keyframes animate
        {
            0%
            {
                transform: rotate(0deg);
            }
            100%
            {
                transform: rotate(360deg);
            }
        }
        @keyframes animate2
        {
            0%
            {
                transform: rotate(360deg);
            }
            100%
            {
                transform: rotate(0deg);
            }
        }
        .login 
        {
            position: absolute;
            width: 300px;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 20px;
        }
        .login h1 
        {
            font-size: 2em;
            color: #fff;
        }
        .login .inputBx 
        {
            position: relative;
            width: 100%;
        }
        .login .inputBx input 
        {
            position: relative;
            width: 100%;
            padding: 12px 20px;
            background: transparent;
            border: 2px solid #fff;
            border-radius: 40px;
            font-size: 1.2em;
            color: #fff;
            box-shadow: none;
            outline: none;
        }
        .login .inputBx input[type="submit"],
        .login .inputBx button
        {
            width: 100%;
            background: #0078ff;
            background: linear-gradient(45deg,#ff357a,#fff172);
            border: none;
            cursor: pointer;
            font-weight: bold;
            height: 45px;
            border-radius: 40px;
        }
        .login .inputBx input::placeholder 
        {
            color: rgba(255,255,255,0.75);
        }
        .login .links
        {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            color: #fff;
        }
        .login .links a 
        {
            color: #fff;
            text-decoration: none;
        }
        .login .links a:hover 
        {
            text-decoration: underline;
        }
        .login label 
        {
            color: #fff;
            font-size: 0.9em;
            margin-bottom: 5px;
            display: block;
        }

        .error {
            color: #ff357a;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: rgba(255, 53, 122, 0.1);
            border: 1px solid rgba(255, 53, 122, 0.3);
            border-radius: 20px;
            font-size: 0.9em;
        }

        .success {
            color: #00ff0a;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: rgba(0, 255, 10, 0.1);
            border: 1px solid rgba(0, 255, 10, 0.3);
            border-radius: 20px;
            font-size: 0.9em;
        }
        h2{
            color: #fff;
            font-size: 1.5em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="square">
    <i style="--clr:#00ff0a;"></i>
    <i style="--clr:#ff0057;"></i>
    <i style="--clr:#fffd44;"></i>
    <form method="post" class="login">
        <h1>FriendGram</h1>
        <h2>Login</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo h($success); ?></div>
        <?php endif; ?>
        
        <div class="inputBx">
            <input type="text" name="identifier" placeholder="Email or Phone" required value="<?php echo h($_POST['identifier'] ?? ''); ?>">
        </div>

        <div class="inputBx">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="inputBx">
            <button type="submit">Login</button>
        </div>

        <div class="links">
            Don't have an account? <a href="register.php">Register Here</a>
        </div>
    </form>
</div>

</body>
</html>