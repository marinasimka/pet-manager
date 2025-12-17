<?php
require_once 'config.php';

// Проверяем ID питомца
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Получаем имя питомца для сообщения (если возможно)
    $petName = '';
    try {
        $stmt = $pdo->prepare("SELECT title FROM pets WHERE id = ?");
        $stmt->execute([$id]);
        $pet = $stmt->fetch();
        if ($pet) {
            $petName = $pet['title'];
        }
    } catch (Exception $e) {
        // Пропускаем ошибку
    }
    
    // Удаляем питомца
    try {
        $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
        $stmt->execute([$id]);
        
        // Логируем удаление
        error_log("Питомец удален: ID {$id}" . ($petName ? " ({$petName})" : ""));
        
    } catch (PDOException $e) {
        error_log("Ошибка удаления питомца ID {$id}: " . $e->getMessage());
    }
}

// Редирект на главную
header('Location: index.php');
exit();
?>