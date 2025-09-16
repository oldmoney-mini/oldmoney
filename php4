public/users/tambah.php (Frontend & Logika)
Ini adalah file utama yang menampilkan formulir 
dan memproses data yang dikirimkan. Formulir 
menggunakan Bootstrap untuk styling. 
Logika PHP ditempatkan di bagian atas file 
untuk memproses pengiriman formulir sebelum 
tampilan HTML.

<?php

require_once '../../init.php';
require_once '../../functions.php'; // Anggap ada file functions.php

// Pastikan pengguna sudah login dan memiliki hak akses
require_login();
verify_csrf(); // Pastikan CSRF token di-verify

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil dan bersihkan data dari form
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Validasi input
    if (empty($name)) {
        $errors[] = 'Nama tidak boleh kosong.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        // Cek apakah email sudah ada di database (unique)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email sudah digunakan. Silakan gunakan email lain.';
        }
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // 3. Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        // Hash password sebelum disimpan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Asumsi struktur tabel users: id, name, email, password, created_at
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        try {
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashed_password
            ]);
            $success = true;
            // Arahkan kembali ke halaman daftar user jika berhasil
            header('Location: ../users.php?status=success_add');
            exit();

        } catch (PDOException $e) {
            // Tangani error database jika ada
            $errors[] = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
            // Untuk debugging: $errors[] = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Tambah Pengguna Baru</h1>
        <p>Silakan isi detail pengguna di bawah ini.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="tambah.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="8">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
            <a href="../users.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>
