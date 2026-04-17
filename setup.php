<?php
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = trim($_POST['host']);
    $db = trim($_POST['db']);
    $user = trim($_POST['user']);
    $pass = trim($_POST['pass']);

    try {
        // ডেটাবেজ কানেকশন
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQL কমান্ড: urls টেবিল তৈরি
        $sql = "CREATE TABLE IF NOT EXISTS urls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            long_url TEXT NOT NULL,
            short_code VARCHAR(10) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);

        // config.php ফাইল তৈরি করা
        $config_content = "<?php\n"
            . "\$host = '$host';\n"
            . "\$db = '$db';\n"
            . "\$user = '$user';\n"
            . "\$pass = '$pass';\n\n"
            . "try {\n"
            . "    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$db\", \$user, \$pass);\n"
            . "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n"
            . "} catch(PDOException \$e) {\n"
            . "    die('Database connection failed: ' . \$e->getMessage());\n"
            . "}\n"
            . "?>";

        file_put_contents('config.php', $config_content);
        $message = "ডেটাবেজ সফলভাবে সেটআপ হয়েছে এবং config.php তৈরি হয়েছে! এখন আপনি <a href='index.php' class='underline font-bold'>index.php</a> এ যেতে পারেন।";
        
    } catch (PDOException $e) {
        $error = "সেটআপ ব্যর্থ হয়েছে: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - URL Shortener</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">URL Shortener Setup</h2>
        
        <?php if($message): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Database Host</label>
                <input type="text" name="host" value="localhost" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Database Name</label>
                <input type="text" name="db" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Database User</label>
                <input type="text" name="user" value="root" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Database Password</label>
                <input type="password" name="pass" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition">Run Setup</button>
        </form>
    </div>
</body>
</html>
