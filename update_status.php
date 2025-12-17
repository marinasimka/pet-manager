<?php
require_once 'config.php';

// Проверяем параметры
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    
    // Проверяем допустимые статусы
    $allowed_statuses = ['здоров', 'болен', 'на лечении'];
    
    if (in_array($status, $allowed_statuses)) {
        try {
            // Обновляем статус
            $stmt = $pdo->prepare("UPDATE pets SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            // Логируем изменение
            error_log("Статус питомца ID {$id} изменен на: {$status}");
            
        } catch (PDOException $e) {
            error_log("Ошибка обновления статуса питомца ID {$id}: " . $e->getMessage());
        }
    }
}

// Редирект на главную
header('Location: index.php');
exit();
?>