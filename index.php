<?php
// ডেটাবেজ কনফিগারেশন চেক
if (!file_exists('config.php')) {
    die("দয়া করে প্রথমে <a href='setup.php' style='color:blue;'>setup.php</a> রান করে ডেটাবেজ সেটআপ করুন।");
}
require 'config.php';

$shortened_url = "";
$error_msg = "";

// রিডাইরেক্ট লজিক (যদি URL-এ ?c=code থাকে)
if (isset($_GET['c'])) {
    $code = $_GET['c'];
    $stmt = $pdo->prepare("SELECT long_url FROM urls WHERE short_code = :code");
    $stmt->execute(['code' => $code]);
    $url = $stmt->fetchColumn();
    
    if ($url) {
        header("Location: " . $url);
        exit;
    } else {
        $error_msg = "দুঃখিত, URL টি পাওয়া যায়নি!";
    }
}

// URL Shortেন লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['long_url'])) {
    $long_url = trim($_POST['long_url']);
    
    if (filter_var($long_url, FILTER_VALIDATE_URL)) {
        // আগে থেকে ডাটাবেজে আছে কি না চেক
        $stmt = $pdo->prepare("SELECT short_code FROM urls WHERE long_url = :long_url");
        $stmt->execute(['long_url' => $long_url]);
        $existing_code = $stmt->fetchColumn();

        if ($existing_code) {
            $short_code = $existing_code;
        } else {
            // নতুন ৬ ক্যারেক্টারের কোড জেনারেট
            $short_code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            $stmt = $pdo->prepare("INSERT INTO urls (long_url, short_code) VALUES (:long_url, :short_code)");
            $stmt->execute(['long_url' => $long_url, 'short_code' => $short_code]);
        }
        
        // শর্ট লিংক তৈরি (আপনার ডোমেইনের নাম অনুযায়ী)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"], '?');
        $shortened_url = $base_url . "?c=" . $short_code;
    } else {
        $error_msg = "অনুগ্রহ করে একটি সঠিক URL প্রদান করুন।";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Shortener - Fast & Secure</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-gray-800">

    <header class="bg-white shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="logo.png" alt="Logo" class="h-10 w-auto" onerror="this.src='https://via.placeholder.com/150x50?text=LOGO'">
                <h1 class="text-xl font-bold text-blue-600 tracking-wide">ShortURL</h1>
            </div>
            <nav>
                <a href="index.php" class="text-gray-600 hover:text-blue-600 font-medium">Home</a>
            </nav>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-2xl bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Paste the URL to be shortened</h2>
                <p class="text-gray-500">Fast, secure, and easy-to-use URL shortener.</p>
            </div>

            <?php if($error_msg): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-link text-gray-400"></i>
                    </div>
                    <input type="url" name="long_url" placeholder="https://example.com/very-long-url" required
                        class="block w-full pl-10 pr-3 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg shadow-sm">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-lg text-lg transition duration-200 shadow-md">
                    Shorten URL
                </button>
            </form>

            <?php if($shortened_url): ?>
                <div class="mt-8 p-6 bg-blue-50 border border-blue-100 rounded-lg text-center">
                    <p class="text-sm text-gray-500 mb-2 font-semibold uppercase tracking-wide">Your Shortened URL</p>
                    <div class="flex items-center justify-center gap-2">
                        <input type="text" id="shortUrlInput" value="<?php echo $shortened_url; ?>" class="bg-white border border-gray-300 px-4 py-2 rounded-l-md w-2/3 text-blue-700 font-medium" readonly>
                        <button onclick="copyToClipboard()" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-r-md transition">
                            <i class="fa-regular fa-copy mr-1"></i> Copy
                        </button>
                    </div>
                </div>
                <script>
                    function copyToClipboard() {
                        var copyText = document.getElementById("shortUrlInput");
                        copyText.select();
                        document.execCommand("copy");
                        alert("URL Copied to clipboard!");
                    }
                </script>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-400 py-6 text-center">
        <div class="max-w-5xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
            <p>&copy; <?php echo date("Y"); ?> ShortURL. All rights reserved.</p>
            <div class="flex gap-4 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" class="hover:text-white transition"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="hover:text-white transition"><i class="fa-brands fa-github"></i></a>
            </div>
        </div>
    </footer>

</body>
</html>
