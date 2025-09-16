Kode Backend (PHP)
File assets/php/sales_backend.php 
akan berisi logika untuk mengambil data 
penjualan dari database. 
Buatlah fungsi yang bisa menerima ID 
pengguna dan mengembalikan total penjualan 
serta jumlah transaksi.
<?php
// Pastikan file init.php sudah terhubung
require_once __DIR__ . '/../../init.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Invalid request.'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'get_sales_info' && isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        
        // Asumsikan $pdo adalah objek PDO untuk koneksi database
        // Query untuk mendapatkan total penjualan dan jumlah transaksi
        try {
            $sql = "SELECT SUM(total_price) as total_sales, COUNT(id) as total_transactions FROM sales WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['success'] = true;
            $response['message'] = 'Sales data fetched successfully.';
            $response['data'] = [
                'total_sales' => $result['total_sales'] ?? 0,
                'total_transactions' => $result['total_transactions'] ?? 0
            ];

        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_user' && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        
        try {
            // Hapus data yang terkait dengan user_id
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            
            $response['success'] = true;
            $response['message'] = 'User deleted successfully.';
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

echo json_encode($response);
