<?php
require_once 'config.php';

// Вспомогательные функции для правильного склонения
if (!function_exists('getYearWord')) {
    function getYearWord($number) {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;
        
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) {
            return 'лет';
        }
        
        switch ($lastDigit) {
            case 1: return 'год';
            case 2:
            case 3:
            case 4: return 'года';
            default: return 'лет';
        }
    }
}

if (!function_exists('getMonthWord')) {
    function getMonthWord($number) {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;
        
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) {
            return 'месяцев';
        }
        
        switch ($lastDigit) {
            case 1: return 'месяц';
            case 2:
            case 3:
            case 4: return 'месяца';
            default: return 'месяцев';
        }
    }
}

// Получаем всех питомцев из БД
try {
    $stmt = $pdo->query("SELECT * FROM pets ORDER BY created_at DESC");
    $pets = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка запроса: " . $e->getMessage());
}

// Подсчет статистики
$totalPets = count($pets);
$healthyPets = array_filter($pets, function($pet) {
    return $pet['status'] == 'здоров';
});
$needCarePets = array_filter($pets, function($pet) {
    return $pet['status'] != 'здоров';
});

$healthyCount = count($healthyPets);
$needCareCount = count($needCarePets);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учёт питомцев</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --border-color: #e9ecef;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--dark-color);
            padding-top: 20px;
        }
        
        .container {
            max-width: 1400px;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            border-left: 5px solid var(--primary-color);
        }
        
        .header h1 {
            color: var(--secondary-color);
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header h1 i {
            color: var(--primary-color);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary-color);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stats-icon.primary {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            line-height: 1;
        }
        
        .stats-label {
            color: var(--gray-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        
        .add-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
            color: white;
        }
        
        .pets-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            border: none;
            padding: 20px;
            font-weight: 600;
            color: var(--dark-color);
            background: var(--light-color);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        .table tbody td {
            padding: 20px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table tbody tr:hover {
            background: rgba(67, 97, 238, 0.03);
        }
        
        .pet-name {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1.1rem;
        }
        
        .pet-type {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .status-healthy {
            background: rgba(76, 201, 240, 0.1);
            color: #0c8599;
        }
        
        .status-sick {
            background: rgba(247, 37, 133, 0.1);
            color: #c2255c;
        }
        
        .status-treatment {
            background: rgba(248, 150, 30, 0.1);
            color: #e67700;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: var(--transition);
        }
        
        .btn-edit {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }
        
        .btn-edit:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-delete {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger-color);
        }
        
        .btn-delete:hover {
            background: var(--danger-color);
            color: white;
        }
        
        .status-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .status-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid transparent;
        }
        
        .status-btn.active {
            border-color: currentColor;
        }
        
        .btn-healthy {
            background: rgba(76, 201, 240, 0.1);
            color: #0c8599;
        }
        
        .btn-sick {
            background: rgba(247, 37, 133, 0.1);
            color: #c2255c;
        }
        
        .btn-treatment {
            background: rgba(248, 150, 30, 0.1);
            color: #e67700;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 20px;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid var(--border-color);
                border-radius: 15px;
            }
            
            .table tbody td {
                display: block;
                text-align: right;
            }
            
            .table tbody td:before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                color: var(--gray-color);
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-paw"></i> Учёт домашних животных</h1>
            <p>Управляйте информацией о ваших питомцах в одном месте</p>
        </div>
        
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon primary"><i class="fas fa-paw"></i></div>
                            <div class="stats-number"><?= $totalPets ?></div>
                            <div class="stats-label">Всего питомцев</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon primary"><i class="fas fa-heart"></i></div>
                            <div class="stats-number"><?= $healthyCount ?></div>
                            <div class="stats-label">Здоровы</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon primary"><i class="fas fa-stethoscope"></i></div>
                            <div class="stats-number"><?= $needCareCount ?></div>
                            <div class="stats-label">Требуют внимания</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="add.php" class="add-btn"><i class="fas fa-plus-circle"></i> Добавить питомца</a>
            </div>
        </div>
        
        <div class="pets-table">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> Список всех питомцев</h3>
            </div>
            
            <?php if (empty($pets)): ?>
                <div class="empty-state">
                    <i class="fas fa-dog"></i>
                    <h4>Питомцев пока нет</h4>
                    <p>Добавьте первого питомца, нажав кнопку выше</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Питомец</th>
                                <th>Вид</th>
                                <th>Дата рождения</th>
                                <th>Статус</th>
                                <th>Действия</th>
                                <th>Быстрый статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pets as $pet): 
                                // Рассчитываем возраст
                                $age = '';
                                if ($pet['birth_date']) {
                                    $birthDate = new DateTime($pet['birth_date']);
                                    $today = new DateTime();
                                    $ageYears = $birthDate->diff($today)->y;
                                    $ageMonths = $birthDate->diff($today)->m;
                                    if ($ageYears > 0) {
                                        $age = $ageYears . ' ' . getYearWord($ageYears);
                                    } else {
                                        $age = $ageMonths . ' ' . getMonthWord($ageMonths);
                                    }
                                }
                                
                                // Статус
                                $statusClass = '';
                                switch($pet['status']) {
                                    case 'здоров': $statusClass = 'status-healthy'; break;
                                    case 'болен': $statusClass = 'status-sick'; break;
                                    case 'на лечении': $statusClass = 'status-treatment'; break;
                                }
                            ?>
                            <tr>
                                <td data-label="Питомец">
                                    <div class="pet-name"><?= htmlspecialchars($pet['title']) ?></div>
                                    <?php if ($pet['description']): ?>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(substr($pet['description'], 0, 50)) ?>
                                            <?= strlen($pet['description']) > 50 ? '...' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Вид">
                                    <span class="pet-type">
                                        <?php 
                                            $icons = [
                                                'Кот' => 'fa-cat',
                                                'Собака' => 'fa-dog',
                                                'Попугай' => 'fa-dove',
                                                'Кошка' => 'fa-cat',
                                                'Пёс' => 'fa-dog',
                                                'Птица' => 'fa-dove',
                                                'Рыбка' => 'fa-fish',
                                                'Хомяк' => 'fa-hippo'
                                            ];
                                            $icon = $icons[$pet['type']] ?? 'fa-paw';
                                        ?>
                                        <i class="fas <?= $icon ?>"></i>
                                        <?= htmlspecialchars($pet['type']) ?>
                                    </span>
                                </td>
                                <td data-label="Дата рождения">
                                    <?php if ($pet['birth_date']): ?>
                                        <div><?= date('d.m.Y', strtotime($pet['birth_date'])) ?></div>
                                        <small class="text-muted"><?= $age ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Не указана</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Статус">
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?php 
                                            $statusIcons = [
                                                'здоров' => 'fa-heart',
                                                'болен' => 'fa-thermometer-half',
                                                'на лечении' => 'fa-stethoscope'
                                            ];
                                        ?>
                                        <i class="fas <?= $statusIcons[$pet['status']] ?? 'fa-circle' ?>"></i>
                                        <?= htmlspecialchars($pet['status']) ?>
                                    </span>
                                </td>
                                <td data-label="Действия">
                                    <div class="action-buttons">
                                        <a href="edit.php?id=<?= $pet['id'] ?>" class="btn-icon btn-edit" title="Изменить">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $pet['id'] ?>" 
                                           class="btn-icon btn-delete" 
                                           title="Удалить"
                                           onclick="return confirm('Удалить питомца <?= htmlspecialchars(addslashes($pet['title'])) ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                                <td data-label="Быстрый статус">
                                    <div class="status-actions">
                                        <a href="update_status.php?id=<?= $pet['id'] ?>&status=здоров" 
                                           class="status-btn btn-healthy <?= $pet['status'] == 'здоров' ? 'active' : '' ?>"
                                           title="Отметить здоровым">
                                            <i class="fas fa-heart"></i>
                                        </a>
                                        <a href="update_status.php?id=<?= $pet['id'] ?>&status=болен" 
                                           class="status-btn btn-sick <?= $pet['status'] == 'болен' ? 'active' : '' ?>"
                                           title="Отметить больным">
                                            <i class="fas fa-thermometer-half"></i>
                                        </a>
                                        <a href="update_status.php?id=<?= $pet['id'] ?>&status=на лечении" 
                                           class="status-btn btn-treatment <?= $pet['status'] == 'на лечении' ? 'active' : '' ?>"
                                           title="Отметить на лечении">
                                            <i class="fas fa-stethoscope"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4 mb-5">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                Всего записей: <?= $totalPets ?> 
                • Обновлено: <?= date('d.m.Y H:i') ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Вы уверены, что хотите удалить этого питомца? Это действие нельзя отменить.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>