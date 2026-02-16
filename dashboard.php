<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Website Tahfidz</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #121212; color: #fff; }
        .container { max-width: 600px; margin: auto; }
        h1 { text-align: center; color: #fff; }
        ul { list-style: none; padding: 0; }
        li { margin: 10px 0; }
        a { color: #4CAF50; text-decoration: none; display: block; padding: 10px; background: #1e1e1e; border-radius: 5px; }
        a:hover { background: #2a2a2a; }
        .logout { margin-top: 20px; text-align: center; }
        @media (max-width: 600px) { padding: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daftar Santri</h1>
        <div style="margin: 30px; text-align:center;">
        Halaqoh: <b><?php echo htmlspecialchars($_SESSION['halaqoh']); ?></b><br>
        Username: <b><?php echo htmlspecialchars($_SESSION['user']); ?></b>
    </div>
        <ul id="santriList"></ul>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            fetchSantriList();
        });

        function fetchSantriList() {
            const formData = new FormData();
            formData.append('action', 'read');
            formData.append('range', '<?php echo $_SESSION['halaqoh']; ?>!A3:B17'); // Range Nomor dan Nama
            
            fetch('backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                const list = document.getElementById('santriList');
                list.innerHTML = '';
                data.values.forEach((row, index) => {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = `edit_santri.php?index=${index}`;
                    a.textContent = `${row[0] || ''} - ${row[1] || ''}`;
                    li.appendChild(a);
                    list.appendChild(li);
                });
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    </script>
</body>
</html>