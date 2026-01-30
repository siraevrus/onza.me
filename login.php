<?php
require_once __DIR__ . '/config/auth.php';

// Если уже авторизован, перенаправляем в админку
if (isAuthenticated()) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = login($username, $password);
    if ($result['success']) {
        header('Location: admin.php');
        exit;
    } else {
        $error = $result['message'] ?? 'Неверный логин или пароль';
    }
}
?>
<!doctype html>
<html lang="ru" data-theme="corporate">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Вход в админ-панель</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght,CRSV@100..900,0&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <link href="assets/styles.css" rel="stylesheet" />
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #F5F5F5;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card bg-white shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Вход в админ-панель</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error mb-4">
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Логин</span>
                        </label>
                        <input type="text" name="username" placeholder="Введите логин" class="input input-bordered" required autofocus />
                    </div>
                    
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Пароль</span>
                        </label>
                        <input type="password" name="password" placeholder="Введите пароль" class="input input-bordered" required />
                    </div>
                    
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary">Войти</button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</body>
</html>
