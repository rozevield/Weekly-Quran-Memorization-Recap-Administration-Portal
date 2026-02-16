<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: login.php'); // Redirect ke login kalau belum login
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Penting untuk HP -->
    <title>Selamat Datang - Website Tahfidz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 20px;
            background: #f4f4f4;
        }

        h1 { 
            color: #333; 
            font-size: 1.8em;
        }

        .container {
            max-width: 600px;
            width: 95%; /* agar menyesuaikan layar kecil */
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        button {
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin: 10px 0;
        }

        button:hover { background: #45a049; }

        a {
            color: #4CAF50;
            text-decoration: none;
            word-break: break-word; /* supaya link panjang pecah ke baris berikutnya */
        }

        /* Media query untuk layar kecil */
        @media (max-width: 480px) {
            h1 { font-size: 1.5em; }
            .container { padding: 15px; }
            button { width: 100%; font-size: 1em; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Selamat Datang di Website Tahfidz</h1>
        <p>Website ini untuk mengelola rekap data Tahfidz menggunakan Google Sheets.</p>
        <p>Silakan login untuk mengedit spreadsheet.</p>
        <button onclick="window.location.href='login.php'">Login</button>
        <p>Untuk mengelola hosting, akses cPanel:<br> 
            <a href="https://app.infinityfree.net/login" target="_blank">Login cPanel InfinityFree</a>.
        </p>
    </div>
</body>
</html>
