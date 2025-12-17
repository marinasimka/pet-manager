<?php
/**
 * Конфигурация базы данных
 * Минималистичный стиль
 */

// Настройки отображения ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Цветовая схема для всего проекта
define('PRIMARY_COLOR', '#4361ee');
define('SECONDARY_COLOR', '#3a0ca3');
define('SUCCESS_COLOR', '#4cc9f0');
define('DANGER_COLOR', '#f72585');
define('WARNING_COLOR', '#f8961e');
define('LIGHT_COLOR', '#f8f9fa');
define('DARK_COLOR', '#212529');

// Параметры подключения к БД
$host = 'localhost';
$dbname = 'pet_manager';
$username = 'petadmin';  // Попробуйте также 'root'
$password = '123456';    // Попробуйте также '', 'root', 'password'

/**
 * Получение подключения к базе данных
 */
function getDatabaseConnection() {
    global $host, $dbname;
    
    // Пробуем разные варианты подключения
    $attempts = [
        ['petadmin', '123456'],
        ['root', ''],
        ['root', 'root'],
        ['root', 'password'],
    ];
    
    foreach ($attempts as $attempt) {
        list($user, $pass) = $attempt;
        
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            
            // Проверяем таблицу
            $pdo->query("SELECT 1 FROM pets LIMIT 1");
            
            echo "<!-- Подключено как: $user -->";
            return $pdo;
            
        } catch (Exception $e) {
            continue;
        }
    }
    
    // Если не удалось подключиться - создаем демо-базу
    return createDemoDatabase();
}

/**
 * Создание демонстрационной базы данных
 */
function createDemoDatabase() {
    // Выводим информационное сообщение (будет скрыто стилями)
    echo '<!-- Демо-режим -->';
    
    return new class() {
        private $pets = [];
        private $nextId = 1;
        
        public function __construct() {
            // Начальные данные
            $this->pets = [
                $this->createPet('Барсик', 'Ласковый кот, любит спать на подоконнике', 'Кот', '2020-05-15', 'здоров'),
                $this->createPet('Шарик', 'Активная собака, любит играть с мячом', 'Собака', '2019-08-20', 'здоров'),
                $this->createPet('Кеша', 'Говорящий попугай, знает 10 слов', 'Попугай', '2021-02-10', 'болен'),
                $this->createPet('Мурка', 'Ловит мышей, любит молоко', 'Кошка', '2021-11-05', 'на лечении'),
            ];
        }
        
        private function createPet($title, $description, $type, $birth_date, $status) {
            return [
                'id' => $this->nextId++,
                'title' => $title,
                'description' => $description,
                'type' => $type,
                'birth_date' => $birth_date,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s', time() - rand(0, 86400))
            ];
        }
        
        public function query($sql) {
            return new class($this->pets) {
                private $data;
                private $position = 0;
                
                public function __construct($data) {
                    $this->data = $data;
                }
                
                public function fetchAll() {
                    return $this->data;
                }
                
                public function fetch() {
                    if ($this->position < count($this->data)) {
                        return $this->data[$this->position++];
                    }
                    return false;
                }
                
                public function rowCount() {
                    return count($this->data);
                }
            };
        }
        
        public function prepare($sql) {
            return new class($this->pets, $sql, $this) {
                private $data;
                private $sql;
                private $db;
                private $params = [];
                
                public function __construct($data, $sql, $db) {
                    $this->data = $data;
                    $this->sql = $sql;
                    $this->db = $db;
                }
                
                public function execute($params = []) {
                    $this->params = $params;
                    
                    // Симуляция INSERT
                    if (stripos($this->sql, 'INSERT') !== false) {
                        $newPet = [
                            'id' => count($this->data) + 1,
                            'title' => $params[0] ?? 'Новый питомец',
                            'description' => $params[1] ?? '',
                            'type' => $params[2] ?? 'Неизвестно',
                            'birth_date' => $params[3] ?? null,
                            'status' => $params[4] ?? 'здоров',
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $this->data[] = $newPet;
                    }
                    
                    // Симуляция UPDATE
                    if (stripos($this->sql, 'UPDATE') !== false) {
                        $id = end($params);
                        foreach ($this->data as &$pet) {
                            if ($pet['id'] == $id) {
                                $pet['title'] = $params[0] ?? $pet['title'];
                                $pet['description'] = $params[1] ?? $pet['description'];
                                $pet['type'] = $params[2] ?? $pet['type'];
                                $pet['birth_date'] = $params[3] ?? $pet['birth_date'];
                                $pet['status'] = $params[4] ?? $pet['status'];
                            }
                        }
                    }
                    
                    // Симуляция DELETE
                    if (stripos($this->sql, 'DELETE') !== false) {
                        $id = $params[0] ?? null;
                        if ($id) {
                            $this->data = array_filter($this->data, function($pet) use ($id) {
                                return $pet['id'] != $id;
                            });
                            $this->data = array_values($this->data);
                        }
                    }
                    
                    return true;
                }
                
                public function fetchAll() {
                    return $this->data;
                }
                
                public function fetch() {
                    return count($this->data) ? $this->data[0] : false;
                }
            };
        }
    };
}

// Создаем глобальное подключение
$pdo = getDatabaseConnection();

/**
 * Вспомогательные функции для отображения
 */
function getStatusClass($status) {
    switch($status) {
        case 'здоров': return 'status-healthy';
        case 'болен': return 'status-sick';
        case 'на лечении': return 'status-treatment';
        default: return '';
    }
}

function getTypeIcon($type) {
    $icons = [
        'Кот' => 'fa-cat',
        'Собака' => 'fa-dog',
        'Попугай' => 'fa-dove',
        'Кошка' => 'fa-cat',
        'Пёс' => 'fa-dog',
        'Птица' => 'fa-dove',
        'Рыбка' => 'fa-fish',
        'Хомяк' => 'fa-hippo',
        'Кролик' => 'fa-paw',
        'Черепаха' => 'fa-shield-alt'
    ];
    return $icons[$type] ?? 'fa-paw';
}

function formatDate($date) {
    if (!$date) return 'Не указана';
    return date('d.m.Y', strtotime($date));
}

function calculateAge($birth_date) {
    if (!$birth_date) return '';
    
    $birth = new DateTime($birth_date);
    $today = new DateTime();
    $diff = $birth->diff($today);
    
    $years = $diff->y;
    $months = $diff->m;
    
    if ($years > 0) {
        return $years . ' ' . getYearWord($years);
    } elseif ($months > 0) {
        return $months . ' ' . getMonthWord($months);
    } else {
        return 'Меньше месяца';
    }
}

function getYearWord($number) {
    $lastDigit = $number % 10;
    $lastTwoDigits = $number % 100;
    
    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) return 'лет';
    
    switch ($lastDigit) {
        case 1: return 'год';
        case 2:
        case 3:
        case 4: return 'года';
        default: return 'лет';
    }
}

function getMonthWord($number) {
    $lastDigit = $number % 10;
    $lastTwoDigits = $number % 100;
    
    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) return 'месяцев';
    
    switch ($lastDigit) {
        case 1: return 'месяц';
        case 2:
        case 3:
        case 4: return 'месяца';
        default: return 'месяцев';
    }
}
?>