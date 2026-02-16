<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$host = 'sql301.infinityfree.com';
$db = 'if0_39939897_tahfidz_db';
$user = 'if0_39939897';
$pass = 'cnTWRyCicK';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Username atau password kosong!';
    } else {
        $stmt = $conn->prepare('SELECT password, spreadsheet_id, halaqoh FROM users WHERE username = ?');
        if ($stmt === false) {
            $error = 'Prepare statement gagal: ' . $conn->error;
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user'] = $username;
                    $_SESSION['spreadsheet_id'] = $row['spreadsheet_id'];
                    $_SESSION['halaqoh'] = $row['halaqoh'];
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Password salah!';
                }
            } else {
                $error = 'Username tidak ditemukan!';
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- penting untuk HP -->
    <title>Login - Website Tahfidz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f4f4f4;
        }

        form {
            max-width: 400px;
            width: 95%; /* menyesuaikan layar kecil */
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            text-align: left;
            margin-top: 10px;
            font-weight: bold;
        }

        input {
            margin: 8px 0;
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 15px;
            padding: 12px;
            width: 100%;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 480px) {
            form { padding: 15px; }
            h2 { font-size: 1.5em; }
            button { font-size: 1em; }
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
