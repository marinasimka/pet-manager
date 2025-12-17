<?php
require_once 'config.php';

// Проверяем ID питомца
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int)$_GET['id'];
$error = '';
$success = '';

// Получаем данные питомца
try {
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
    $stmt->execute([$id]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    die("Ошибка загрузки данных: " . $e->getMessage());
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $status = $_POST['status'] ?? 'здоров';

    // Валидация
    if (empty($title)) {
        $error = 'Введите кличку питомца';
    } elseif (empty($type)) {
        $error = 'Выберите вид животного';
    } else {
        try {
            $sql = "UPDATE pets SET title = ?, description = ?, type = ?, birth_date = ?, status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $type, $birth_date ?: null, $status, $id]);
            
            $success = 'Данные питомца обновлены!';
            
            // Обновляем локальные данные
            $pet['title'] = $title;
            $pet['description'] = $description;
            $pet['type'] = $type;
            $pet['birth_date'] = $birth_date;
            $pet['status'] = $status;
            
        } catch (PDOException $e) {
            $error = 'Ошибка при обновлении: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать питомца - Учёт питомцев</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container fade-in">
        <!-- Навигация -->
        <div class="breadcrumb mb-3">
            <a href="index.php"><i class="fas fa-home"></i> Главная</a>
            <span class="separator">/</span>
            <span>Редактировать питомца</span>
        </div>

        <!-- Заголовок -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Редактировать питомца
            </h1>
            <p class="page-subtitle">Изменение информации о питомце: <strong><?php echo htmlspecialchars($pet['title']); ?></strong></p>
        </div>

        <!-- Основной контент -->
        <div class="card">
            <!-- Сообщения -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Форма -->
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title" class="form-label">
                                <i class="fas fa-signature"></i> Кличка *
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo htmlspecialchars($pet['title']); ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="type" class="form-label">
                                <i class="fas fa-paw"></i> Вид животного *
                            </label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Выберите вид...</option>
                                <option value="Кот" <?php echo $pet['type'] == 'Кот' ? 'selected' : ''; ?>>Кот</option>
                                <option value="Собака" <?php echo $pet['type'] == 'Собака' ? 'selected' : ''; ?>>Собака</option>
                                <option value="Кошка" <?php echo $pet['type'] == 'Кошка' ? 'selected' : ''; ?>>Кошка</option>
                                <option value="Попугай" <?php echo $pet['type'] == 'Попугай' ? 'selected' : ''; ?>>Попугай</option>
                                <option value="Хомяк" <?php echo $pet['type'] == 'Хомяк' ? 'selected' : ''; ?>>Хомяк</option>
                                <option value="Кролик" <?php echo $pet['type'] == 'Кролик' ? 'selected' : ''; ?>>Кролик</option>
                                <option value="Рыбка" <?php echo $pet['type'] == 'Рыбка' ? 'selected' : ''; ?>>Рыбка</option>
                                <option value="Черепаха" <?php echo $pet['type'] == 'Черепаха' ? 'selected' : ''; ?>>Черепаха</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="birth_date" class="form-label">
                                <i class="fas fa-birthday-cake"></i> Дата рождения
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="birth_date" 
                                   name="birth_date" 
                                   value="<?php echo htmlspecialchars($pet['birth_date'] ?? ''); ?>">
                            <?php if ($pet['birth_date']): ?>
                                <div class="age-badge">
                                    Возраст: <?php echo calculateAge($pet['birth_date']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="form-label">
                                <i class="fas fa-heartbeat"></i> Статус здоровья
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="здоров" <?php echo $pet['status'] == 'здоров' ? 'selected' : ''; ?>>Здоров</option>
                                <option value="болен" <?php echo $pet['status'] == 'болен' ? 'selected' : ''; ?>>Болен</option>
                                <option value="на лечении" <?php echo $pet['status'] == 'на лечении' ? 'selected' : ''; ?>>На лечении</option>
                            </select>
                            <div class="mt-2">
                                <span class="status-badge <?php echo getStatusClass($pet['status']); ?>">
                                    <i class="fas fa-heartbeat"></i>
                                    Текущий статус: <?php echo htmlspecialchars($pet['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt"></i> Описание
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="6"><?php echo htmlspecialchars($pet['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Информация о записи -->
                <div class="mt-4 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="far fa-calendar"></i>
                                Дата добавления: <?php echo date('d.m.Y H:i', strtotime($pet['created_at'])); ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-hashtag"></i>
                                ID записи: #<?php echo $pet['id']; ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                        <div>
                            <a href="delete.php?id=<?php echo $pet['id']; ?>" 
                               class="btn btn-danger me-2"
                               onclick="return confirm('Вы уверены, что хотите удалить питомца <?php echo htmlspecialchars(addslashes($pet['title'])); ?>?')">
                                <i class="fas fa-trash"></i> Удалить
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Автофокус на поле клички
        document.getElementById('title').focus();
        
        // Ограничение длины описания
        document.getElementById('description').addEventListener('input', function() {
            if (this.value.length > 500) {
                this.value = this.value.substring(0, 500);
            }
        });
    </script>
</body>
</html>