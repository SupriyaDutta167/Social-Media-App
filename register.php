<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];

        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            $error = 'Password must be at least 8 characters, include 1 capital letter and 1 number.';
        } else {
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $check_stmt->bind_param("ss", $email, $phone);
            $check_stmt->execute();
            $existing = $check_stmt->get_result();

            if ($existing->num_rows > 0) {
                $error = 'Email or phone number already registered.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

                if ($stmt->execute()) {
                    header("Location: login.php?registered=1");
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            width: 550px;
            height: 600px;
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
        .register 
        {
            position: absolute;
            width: 350px;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 15px;
        }
        .register h1 
        {
            font-size: 2em;
            color: #fff;
            margin-bottom: 10px;
        }
        .register .inputBx 
        {
            position: relative;
            width: 100%;
        }
        .register .inputBx input 
        {
            position: relative;
            width: 100%;
            padding: 12px 20px;
            background: transparent;
            border: 2px solid #fff;
            border-radius: 40px;
            font-size: 1.1em;
            color: #fff;
            box-shadow: none;
            outline: none;
        }
        .register .inputBx input[type="submit"],
        .register .inputBx button
        {
            width: 100%;
            background: #0078ff;
            background: linear-gradient(45deg,#ff357a,#fff172);
            border: none;
            cursor: pointer;
            font-weight: bold;
            height: 50px;
            border-radius: 40px;
        }
        .register .inputBx input::placeholder 
        {
            color: rgba(255,255,255,0.75);
        }
        .register .links
        {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            margin-top: 10px;
            color:white;
        }
        .register .links a 
        {
            color: #fff;
            text-decoration: none;
        }
        .register .links a:hover 
        {
            text-decoration: underline;
        }

        .error {
            color: #ff357a;
            text-align: center;
            margin-bottom: 10px;
            padding: 8px;
            background-color: rgba(255, 53, 122, 0.1);
            border: 1px solid rgba(255, 53, 122, 0.3);
            border-radius: 20px;
            font-size: 0.85em;
            width: 100%;
        }

        .success {
            color: #00ff0a;
            text-align: center;
            margin-bottom: 10px;
            padding: 8px;
            background-color: rgba(0, 255, 10, 0.1);
            border: 1px solid rgba(0, 255, 10, 0.3);
            border-radius: 20px;
            font-size: 0.85em;
            width: 100%;
        }

        .password-requirements {
            font-size: 0.8em;
            color: rgba(255,255,255,0.7);
            text-align: center;
            margin-top: -10px;
            margin-bottom: 5px;
            padding: 0 10px;
            line-height: 1.3;
        }
    </style>
</head>
<body>

<div class="square">
    <i style="--clr:#00ff0a;"></i>
    <i style="--clr:#ff0057;"></i>
    <i style="--clr:#fffd44;"></i>
    <form method="post" class="register">
        <h1>Register</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>
        
        <div class="inputBx">
            <input type="text" name="name" placeholder="Full Name" required value="<?php echo h($_POST['name'] ?? ''); ?>">
        </div>

        <div class="inputBx">
            <input type="email" name="email" placeholder="Email" required value="<?php echo h($_POST['email'] ?? ''); ?>">
        </div>

        <div class="inputBx">
            <input type="text" name="phone" placeholder="Phone Number" required value="<?php echo h($_POST['phone'] ?? ''); ?>">
        </div>

        <div class="inputBx">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        
        <div class="password-requirements">
            Password must be at least 8 characters with 1 capital letter and 1 number
        </div>

        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="inputBx">
            <button type="submit">Register</button>
        </div>

        <div class="links">
            Already have an account? <a href="login.php">Login Here</a>
        </div>
    </form>
</div>

</body>
</html>