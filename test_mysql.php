<?php
echo "<h2>Тест подключения к MySQL</h2>";

$host = 'localhost';
$dbname = 'pet_manager';

// Пробуем разные пароли
$attempts = [
    ['root', ''],
    ['root', 'root'],
    ['root', 'password'],
    ['root', '123456'],
    ['root', 'admin'],
];

foreach ($attempts as $attempt) {
    list($user, $pass) = $attempt;
    
    echo "Пробуем: <strong>$user</strong> / пароль: <strong>'$pass'</strong>... ";
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Проверяем таблицу
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM pets");
        $result = $stmt->fetch();
        
        echo "<span style='color:green;font-weight:bold;'>УСПЕХ!</span> ";
        echo "(питомцев в базе: {$result['count']})<br>";
        
        // Сохраняем рабочую комбинацию
        $working_combination = [$user, $pass];
        break;
        
    } catch (PDOException $e) {
        echo "<span style='color:red;'>ошибка: {$e->getMessage()}</span><br>";
    }
}

if (isset($working_combination)) {
    echo "<hr>";
    echo "<h3 style='color:green;'> Найдено рабочее подключение!</h3>";
    echo "Используйте в config.php:<br><br>";
    echo "<pre style='background:#f0f0f0;padding:10px;'>";
    echo "&lt;?php\n";
    echo "\$host = 'localhost';\n";
    echo "\$dbname = 'pet_manager';\n";
    echo "\$username = '{$working_combination[0]}';\n";
    echo "\$password = '{$working_combination[1]}';\n";
    echo "// остальной код...\n";
    echo "?&gt;";
    echo "</pre>";
} else {
    echo "<hr>";
    echo "<h3 style='color:red;'> Не удалось подключиться</h3>";
    echo "Нужно сбросить пароль MySQL. Выполните в командной строке:<br><br>";
    echo "<pre style='background:#f0f0f0;padding:10px;'>";
    echo "# Откройте командную строку от имени администратора\n";
    echo "cd C:\\xampp\\mysql\\bin\n";
    echo "mysqladmin -u root password \"\"\n";
    echo "</pre>";
}
?>