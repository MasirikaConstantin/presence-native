<?php
// login.php

require_once 'db.php';
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$em="";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['etat']) {
                $_SESSION['admin_id'] = $admin['id'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Votre compte est désactivé. Contactez l\'administrateur.';
            }
        } else {
            $em=$_POST['email'];
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="mmZ5tYVmXtA8vSWdDYDlEfNKYIerMHZeS4wMIOh3">

        <title>Presence</title>
        <style>
                .error { color: red; margin-bottom: 10px; }

        </style>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <link rel="icon" type="image/png" sizes="32x32" href="mas-product.ico"/>
        <link rel="icon" type="image/png" sizes="16x16" href="mas-product.ico"/>
        <link rel="stylesheet" href="style.css">

        <!-- Scripts -->
        
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <img  class="w-20 h-20 fill-current text-gray-500" src="https://api.mascodeproduct.com/php/masproduct.png" />          
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <!-- Session Status -->
                <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST" >
        <!-- Email Address -->
        <div>
            <label class="block font-medium text-sm text-gray-700" for="email">
    E-mail
</label>
            <input value="<?php if($em){echo($em);}; ?>"  class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" id="email" type="email" name="email" required="required" autofocus="autofocus" autocomplete="username">
                    </div>

        <!-- Password -->
        <div class="mt-4">
            <label class="block font-medium text-sm text-gray-700" for="password">
    Mot de passe
</label>

            <input  class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" id="password" type="password" name="password" required="required" autocomplete="current-password">

                    </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <!--label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Se souvenir de moi</span-->
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
                           
            
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 ms-3">
    Se connecter
</button>
        </div>
    </form>
            </div>
        </div>
    </body>
</html>
