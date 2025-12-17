<?php
require_once 'config.php';

$error = '';
$success = '';
$formData = [
    'title' => '',
    'description' => '',
    'type' => '',
    'birth_date' => '',
    'status' => 'здоров'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['title'] = trim($_POST['title'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['type'] = trim($_POST['type'] ?? '');
    $formData['birth_date'] = $_POST['birth_date'] ?? '';
    $formData['status'] = $_POST['status'] ?? 'здоров';

    // Валидация
    if (empty($formData['title'])) {
        $error = 'Введите кличку питомца';
    } elseif (empty($formData['type'])) {
        $error = 'Выберите вид животного';
    } else {
        try {
            $sql = "INSERT INTO pets (title, description, type, birth_date, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $formData['title'],
                $formData['description'],
                $formData['type'],
                $formData['birth_date'] ?: null,
                $formData['status']
            ]);
            
            $success = 'Питомец успешно добавлен!';
            $formData = [
                'title' => '',
                'description' => '',
                'type' => '',
                'birth_date' => '',
                'status' => 'здоров'
            ];
            
        } catch (PDOException $e) {
            $error = 'Ошибка при добавлении: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить питомца - Учёт питомцев</title>
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
            <span>Добавить питомца</span>
        </div>

        <!-- Заголовок -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Добавить нового питомца
            </h1>
            <p class="page-subtitle">Заполните информацию о новом питомце</p>
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
                                   value="<?php echo htmlspecialchars($formData['title']); ?>"
                                   placeholder="Например: Барсик, Шарик..."
                                   required>
                            <small class="text-muted">Обязательное поле</small>
                        </div>

                        <div class="form-group">
                            <label for="type" class="form-label">
                                <i class="fas fa-paw"></i> Вид животного *
                            </label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Выберите вид...</option>
                                <option value="Кот" <?php echo $formData['type'] == 'Кот' ? 'selected' : ''; ?>>Кот</option>
                                <option value="Собака" <?php echo $formData['type'] == 'Собака' ? 'selected' : ''; ?>>Собака</option>
                                <option value="Кошка" <?php echo $formData['type'] == 'Кошка' ? 'selected' : ''; ?>>Кошка</option>
                                <option value="Попугай" <?php echo $formData['type'] == 'Попугай' ? 'selected' : ''; ?>>Попугай</option>
                                <option value="Хомяк" <?php echo $formData['type'] == 'Хомяк' ? 'selected' : ''; ?>>Хомяк</option>
                                <option value="Кролик" <?php echo $formData['type'] == 'Кролик' ? 'selected' : ''; ?>>Кролик</option>
                                <option value="Рыбка" <?php echo $formData['type'] == 'Рыбка' ? 'selected' : ''; ?>>Рыбка</option>
                                <option value="Черепаха" <?php echo $formData['type'] == 'Черепаха' ? 'selected' : ''; ?>>Черепаха</option>
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
                                   value="<?php echo htmlspecialchars($formData['birth_date']); ?>">
                            <small class="text-muted">Необязательно</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="form-label">
                                <i class="fas fa-heartbeat"></i> Статус здоровья
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="здоров" <?php echo $formData['status'] == 'здоров' ? 'selected' : ''; ?>>Здоров</option>
                                <option value="болен" <?php echo $formData['status'] == 'болен' ? 'selected' : ''; ?>>Болен</option>
                                <option value="на лечении" <?php echo $formData['status'] == 'на лечении' ? 'selected' : ''; ?>>На лечении</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-alt"></i> Описание
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="6"
                                      placeholder="Опишите питомца: характер, привычки, особенности..."><?php echo htmlspecialchars($formData['description']); ?></textarea>
                            <small class="text-muted">Максимум 500 символов</small>
                        </div>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Добавить питомца
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Подсказка -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lightbulb"></i> Подсказка
                </h3>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Поля отмеченные * обязательны для заполнения</li>
                    <li>Дата рождения поможет автоматически рассчитывать возраст питомца</li>
                    <li>Статус здоровья можно изменить позже в карточке питомца</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Ограничение длины описания
        document.getElementById('description').addEventListener('input', function() {
            if (this.value.length > 500) {
                this.value = this.value.substring(0, 500);
            }
        });
        
        // Автофокус на поле клички
        document.getElementById('title').focus();
    </script>
</body>
</html>