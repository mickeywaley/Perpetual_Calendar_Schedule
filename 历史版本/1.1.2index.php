<?php
// 启用会话
session_start();

// 配置时区
date_default_timezone_set('Asia/Shanghai');

// 数据库配置
define('DB_FILE', __DIR__ . '/calendar_data.json');

// 确保数据文件存在
if (!file_exists(DB_FILE)) {
    file_put_contents(DB_FILE, json_encode(['events' => [], 'settings' => [], 'users' => []]));
}

// 加载数据
$data = json_decode(file_get_contents(DB_FILE), true);
$events = $data['events'] ?? [];
$settings = $data['settings'] ?? [];
$users = $data['users'] ?? [];

// 默认设置
$defaultSettings = [
    'dark_mode' => false,
    'language' => 'zh',
    'first_day_of_week' => 0,
    'show_lunar' => true,
    'show_holidays' => true
];

// 合并默认设置和用户设置
$settings = array_merge($defaultSettings, $settings);

// 语言包
$languages = [
    'zh' => [
        'January' => '一月', 'February' => '二月', 'March' => '三月', 'April' => '四月',
        'May' => '五月', 'June' => '六月', 'July' => '七月', 'August' => '八月',
        'September' => '九月', 'October' => '十月', 'November' => '十一月', 'December' => '十二月',
        'Sunday' => '周日', 'Monday' => '周一', 'Tuesday' => '周二', 'Wednesday' => '周三',
        'Thursday' => '周四', 'Friday' => '周五', 'Saturday' => '周六',
        'Add Event' => '添加日程', 'Edit Event' => '编辑日程', 'Delete Event' => '删除日程',
        'Title' => '标题', 'Description' => '描述', 'Date' => '日期', 'Time' => '时间',
        'Reminder' => '提醒', 'Save' => '保存', 'Cancel' => '取消', 'Confirm' => '确认',
        'Are you sure?' => '确定要执行此操作吗？',
        'Previous Month' => '上月', 'Next Month' => '下月', 'Today' => '今天',
        'No events' => '无日程', 'Dark Mode' => '深色模式', 'Light Mode' => '浅色模式',
        'Language' => '语言', 'Settings' => '设置', 'Search' => '搜索', 'Events' => '日程',
        'Login' => '登录', 'Logout' => '退出', 'Username' => '用户名', 'Password' => '密码',
        'Invalid credentials' => '用户名或密码错误', 'Admin' => '管理员', 'User' => '普通用户',
        'Access denied' => '权限不足', 'Save Settings' => '保存设置',
        'Settings Saved' => '设置已保存', 'Event Added' => '日程已添加',
        'Event Updated' => '日程已更新', 'Event Deleted' => '日程已删除',
        'No events found' => '未找到日程', 'All Events' => '所有日程',
        'Upcoming Events' => '即将到来的日程', 'Past Events' => '过去的日程',
        'Filter' => '筛选', 'Clear' => '清除', 'Close' => '关闭',
        'User Management' => '用户管理', 'Add User' => '添加用户', 'Role' => '角色',
        'Actions' => '操作', 'Edit User' => '编辑用户', 'Delete User' => '删除用户',
        'New Password' => '新密码', 'Leave blank to keep current' => '留空则保持当前密码'
    ],
    'en' => [
        'January' => 'January', 'February' => 'February', 'March' => 'March', 'April' => 'April',
        'May' => 'May', 'June' => 'June', 'July' => 'July', 'August' => 'August',
        'September' => 'September', 'October' => 'October', 'November' => 'November', 'December' => 'December',
        'Sunday' => 'Sunday', 'Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday',
        'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday',
        'Add Event' => 'Add Event', 'Edit Event' => 'Edit Event', 'Delete Event' => 'Delete Event',
        'Title' => 'Title', 'Description' => 'Description', 'Date' => 'Date', 'Time' => 'Time',
        'Reminder' => 'Reminder', 'Save' => 'Save', 'Cancel' => 'Cancel', 'Confirm' => 'Confirm',
        'Are you sure?' => 'Are you sure you want to do this?',
        'Previous Month' => 'Previous Month', 'Next Month' => 'Next Month', 'Today' => 'Today',
        'No events' => 'No events', 'Dark Mode' => 'Dark Mode', 'Light Mode' => 'Light Mode',
        'Language' => 'Language', 'Settings' => 'Settings', 'Search' => 'Search', 'Events' => 'Events',
        'Login' => 'Login', 'Logout' => 'Logout', 'Username' => 'Username', 'Password' => 'Password',
        'Invalid credentials' => 'Invalid credentials', 'Admin' => 'Admin', 'User' => 'User',
        'Access denied' => 'Access denied', 'Save Settings' => 'Save Settings',
        'Settings Saved' => 'Settings Saved', 'Event Added' => 'Event Added',
        'Event Updated' => 'Event Updated', 'Event Deleted' => 'Event Deleted',
        'No events found' => 'No events found', 'All Events' => 'All Events',
        'Upcoming Events' => 'Upcoming Events', 'Past Events' => 'Past Events',
        'Filter' => 'Filter', 'Clear' => 'Clear', 'Close' => 'Close',
        'User Management' => 'User Management', 'Add User' => 'Add User', 'Role' => 'Role',
        'Actions' => 'Actions', 'Edit User' => 'Edit User', 'Delete User' => 'Delete User',
        'New Password' => 'New Password', 'Leave blank to keep current' => 'Leave blank to keep current password'
    ]
];

// 获取当前语言
$lang = $languages[$settings['language']];

// 用户角色定义
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');

// 检查用户是否已登录
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// 检查用户是否为管理员
function isAdmin() {
    return isLoggedIn() && $_SESSION['user']['role'] === ROLE_ADMIN;
}

// 农历转换函数（简化版）
function solar2lunar($year, $month, $day) {
    $lunarMonths = ['正月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '冬月', '腊月'];
    $lunarDays = ['初一', '初二', '初三', '初四', '初五', '初六', '初七', '初八', '初九', '初十', 
                 '十一', '十二', '十三', '十四', '十五', '十六', '十七', '十八', '十九', '二十', 
                 '廿一', '廿二', '廿三', '廿四', '廿五', '廿六', '廿七', '廿八', '廿九', '三十'];
    
    $lunarMonthIndex = rand(0, 11);
    $lunarDayIndex = rand(0, 29);
    
    return $lunarMonths[$lunarMonthIndex] . $lunarDays[$lunarDayIndex];
}

// 节假日数据（简化版）
function getHolidays($year) {
    return [
        $year . '-01-01' => '元旦',
        $year . '-02-15' => '春节',
        $year . '-04-05' => '清明节',
        $year . '-05-01' => '劳动节',
        $year . '-06-18' => '端午节',
        $year . '-09-24' => '中秋节',
        $year . '-10-01' => '国庆节'
    ];
}

// 处理登录请求
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    
    $_SESSION['login_error'] = $lang['Invalid credentials'];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 处理登出请求
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isAdmin()) {
        $_SESSION['message'] = $lang['Access denied'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // 添加/编辑日程
    if (isset($_POST['action']) && $_POST['action'] === 'save_event') {
        $event = [
            'id' => isset($_POST['id']) ? $_POST['id'] : uniqid(),
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'date' => $_POST['date'],
            'time' => $_POST['time'],
            'reminder' => isset($_POST['reminder']) ? 'on' : 'off'
        ];
        
        $found = false;
        foreach ($events as $key => $existingEvent) {
            if ($existingEvent['id'] === $event['id']) {
                $events[$key] = $event;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $events[] = $event;
        }
        
        $data['events'] = $events;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        
        $_SESSION['message'] = $found ? $lang['Event Updated'] : $lang['Event Added'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // 删除日程
    if (isset($_POST['action']) && $_POST['action'] === 'delete_event') {
        $id = $_POST['id'];
        
        foreach ($events as $key => $event) {
            if ($event['id'] === $id) {
                unset($events[$key]);
                break;
            }
        }
        
        $data['events'] = $events;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        
        $_SESSION['message'] = $lang['Event Deleted'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // 保存设置
    if (isset($_POST['action']) && $_POST['action'] === 'save_settings') {
        $settings['dark_mode'] = isset($_POST['dark_mode']) ? true : false;
        $settings['language'] = $_POST['language'];
        $settings['first_day_of_week'] = (int)$_POST['first_day_of_week'];
        $settings['show_lunar'] = isset($_POST['show_lunar']) ? true : false;
        $settings['show_holidays'] = isset($_POST['show_holidays']) ? true : false;
        
        $data['settings'] = $settings;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        
        $_SESSION['message'] = $lang['Settings Saved'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// 如果没有用户，创建默认管理员账户
if (empty($users)) {
    $defaultAdmin = [
        'id' => uniqid(),
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => ROLE_ADMIN
    ];
    
    $users[] = $defaultAdmin;
    $data['users'] = $users;
    file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
    
    $_SESSION['user'] = $defaultAdmin;
}

// 获取当前年月
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$currentDay = date('j');

// 获取当月第一天是星期几
$firstDayOfMonth = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$firstDayOfWeek = date('w', $firstDayOfMonth);

// 调整星期开始日
if ($settings['first_day_of_week'] == 1) {
    $firstDayOfWeek = ($firstDayOfWeek == 0) ? 6 : $firstDayOfWeek - 1;
}

// 获取当月天数
$daysInMonth = date('t', strtotime("$currentYear-$currentMonth-01"));

// 获取当月日程
$monthEvents = [];
foreach ($events as $event) {
    $eventDate = date('Y-n', strtotime($event['date']));
    $currentDate = $currentYear . '-' . $currentMonth;
    
    if ($eventDate == $currentDate) {
        $day = date('j', strtotime($event['date']));
        if (!isset($monthEvents[$day])) {
            $monthEvents[$day] = [];
        }
        $monthEvents[$day][] = $event;
    }
}

// 获取节假日
$holidays = getHolidays($currentYear);

// 搜索功能
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredEvents = [];

if (!empty($searchTerm)) {
    foreach ($events as $event) {
        if (strpos(strtolower($event['title']), strtolower($searchTerm)) !== false || 
            strpos(strtolower($event['description']), strtolower($searchTerm)) !== false) {
            $filteredEvents[] = $event;
        }
    }
} else {
    $filteredEvents = $events;
}

// 过滤功能
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filteredEventsFinal = [];

foreach ($filteredEvents as $event) {
    $eventTime = strtotime($event['date'] . ' ' . $event['time']);
    $now = time();
    
    if ($filter == 'upcoming' && $eventTime >= $now) {
        $filteredEventsFinal[] = $event;
    } elseif ($filter == 'past' && $eventTime < $now) {
        $filteredEventsFinal[] = $event;
    } elseif ($filter == 'all') {
        $filteredEventsFinal[] = $event;
    }
}

// 按日期排序
usort($filteredEventsFinal, function($a, $b) {
    return strtotime($a['date'] . ' ' . $a['time']) - strtotime($b['date'] . ' ' . $b['time']);
});
?>
<!DOCTYPE html>
<html lang="<?php echo $settings['language']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>万年历日程安排</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- Tailwind 配置 -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#6366F1',
                        accent: '#F97316',
                        dark: {
                            bg: '#121212',
                            card: '#1E1E1E',
                            text: '#E5E7EB'
                        }
                    },
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <!-- 自定义工具类 -->
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .calendar-day {
                aspect-ratio: 1/1;
            }
            .event-badge {
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body class="font-inter <?php echo $settings['dark_mode'] ? 'dark bg-dark-bg text-dark-text' : 'bg-gray-50 text-gray-900'; ?> transition-colors duration-300">
    <!-- 导航栏 -->
    <header class="sticky top-0 z-50 <?php echo $settings['dark_mode'] ? 'bg-dark-card shadow-lg' : 'bg-white shadow-md'; ?>">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa fa-calendar text-primary text-2xl"></i>
                <h1 class="text-xl font-bold">万年历日程</h1>
            </div>
            
            <div class="flex items-center space-x-3">
                <?php if (isLoggedIn()): ?>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">(<?php echo $_SESSION['user']['role'] === ROLE_ADMIN ? '管理员' : '用户'; ?>)</span>
                        <a href="?action=logout" class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            退出
                        </a>
                    </div>
                <?php else: ?>
                    <button onclick="openLoginModal()" class="px-3 py-1 text-sm bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        登录
                    </button>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <div class="relative hidden md:block">
                        <input 
                            type="text" 
                            placeholder="搜索日程..." 
                            class="pl-10 pr-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50 w-64"
                            value="<?php echo htmlspecialchars($searchTerm); ?>"
                            onchange="this.form.submit()"
                        >
                        <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    
                    <button class="md:hidden p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" onclick="toggleSearch()">
                        <i class="fa fa-search"></i>
                    </button>
                    
                    <button class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" onclick="toggleSettingsModal()">
                        <i class="fa fa-cog"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- 主要内容 -->
    <main class="container mx-auto px-4 py-6">
        <?php if (!isLoggedIn()): ?>
            <div class="min-h-[70vh] flex items-center justify-center">
                <div class="text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa fa-calendar text-primary text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2">万年历日程安排</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">请登录查看日程</p>
                    <button onclick="openLoginModal()" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        登录
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- 成功消息 -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="fixed top-20 right-4 z-50 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg fade-in">
                    <?php echo $_SESSION['message']; ?>
                </div>
                <script>
                    setTimeout(() => {
                        document.querySelector('.fade-in').style.opacity = '0';
                        setTimeout(() => {
                            document.querySelector('.fade-in').style.display = 'none';
                        }, 300);
                    }, 3000);
                </script>
            <?php endif; ?>
            
            <!-- 日历视图 -->
            <div class="lg:flex lg:space-x-6">
                <div class="lg:w-3/4">
                    <div class="bg-white dark:bg-dark-card rounded-xl shadow-lg p-4 mb-6 transition-all duration-300">
                        <!-- 日历导航 -->
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold">
                                <?php echo $lang[date('F', $firstDayOfMonth)] . ' ' . $currentYear; ?>
                            </h2>
                            
                            <div class="flex items-center space-x-2">
                                <button 
                                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo $currentYear - 1; ?>&month=<?php echo $currentMonth; ?>'"
                                >
                                    <i class="fa fa-angle-double-left"></i>
                                </button>
                                
                                <button 
                                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo $currentYear; ?>&month=<?php echo $currentMonth - 1 ?: 12; ?><?php echo $currentMonth - 1 ?: '&year=' . ($currentYear - 1); ?>'"
                                >
                                    <i class="fa fa-angle-left"></i>
                                </button>
                                
                                <button 
                                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors"
                                    onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo date('Y'); ?>&month=<?php echo date('n'); ?>'"
                                >
                                    <?php echo $lang['Today']; ?>
                                </button>
                                
                                <button 
                                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo $currentYear; ?>&month=<?php echo $currentMonth + 1 > 12 ? 1 : $currentMonth + 1; ?><?php echo $currentMonth + 1 > 12 ? '&year=' . ($currentYear + 1) : ''; ?>'"
                                >
                                    <i class="fa fa-angle-right"></i>
                                </button>
                                
                                <button 
                                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo $currentYear + 1; ?>&month=<?php echo $currentMonth; ?>'"
                                >
                                    <i class="fa fa-angle-double-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- 星期标题 -->
                        <div class="grid grid-cols-7 mb-2">
                            <?php
                            $weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            
                            if ($settings['first_day_of_week'] == 1) {
                                $weekdays = array_merge(array_slice($weekdays, 1), array_slice($weekdays, 0, 1));
                            }
                            
                            foreach ($weekdays as $weekday) {
                                echo '<div class="text-center font-medium text-sm text-gray-500 dark:text-gray-400">' . $lang[$weekday] . '</div>';
                            }
                            ?>
                        </div>
                        
                        <!-- 日历网格 -->
                        <div class="grid grid-cols-7 gap-1">
                            <?php for ($i = 0; $i < $firstDayOfWeek; $i++): ?>
                                <div class="calendar-cell p-2 rounded-lg text-center <?php echo $settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-gray-100'; ?> text-gray-400">
                                    <div class="text-sm"><?php echo cal_days_in_month(CAL_GREGORIAN, $currentMonth - 1 ?: 12, $currentYear - ($currentMonth == 1 ? 1 : 0)) - $firstDayOfWeek + $i + 1; ?></div>
                                </div>
                            <?php endfor; ?>
                            
                            <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                                <?php
                                $currentDate = $currentYear . '-' . $currentMonth . '-' . $day;
                                $isToday = ($day == $currentDay && $currentMonth == date('n') && $currentYear == date('Y'));
                                $hasEvents = isset($monthEvents[$day]);
                                $isHoliday = isset($holidays[$currentDate]);
                                $lunarDate = $settings['show_lunar'] ? solar2lunar($currentYear, $currentMonth, $day) : '';
                                ?>
                                
                                <div class="calendar-cell p-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-white'; ?> border border-gray-100 dark:border-gray-800 hover:shadow-md transition-all duration-200 cursor-pointer <?php echo $isToday ? 'ring-2 ring-primary' : ''; ?>" <?php echo isAdmin() ? 'onclick="document.getElementById(\'date\').value=\'' . $currentDate . '\';openEventModal();"' : ''; ?>>
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="font-medium <?php echo $isToday ? 'text-primary' : ''; ?>"><?php echo $day; ?></span>
                                        <?php if ($isHoliday && $settings['show_holidays']): ?>
                                            <span class="text-xs text-amber-500 font-medium"><?php echo $holidays[$currentDate]; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($lunarDate && $settings['show_lunar']): ?>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1"><?php echo $lunarDate; ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="space-y-1 mt-1">
                                        <?php if ($hasEvents): ?>
                                            <?php foreach (array_slice($monthEvents[$day], 0, 2) as $event): ?>
                                                <div class="event-badge text-xs px-1 py-0.5 rounded bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 truncate">
                                                    <?php echo htmlspecialchars($event['title']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <?php if (count($monthEvents[$day]) > 2): ?>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    +<?php echo count($monthEvents[$day]) - 2; ?> 更多
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                                <?php echo $lang['No events']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endfor; ?>
                            
                            <?php $remainingDays = 42 - ($firstDayOfWeek + $daysInMonth); ?>
                            <?php for ($i = 1; $i <= $remainingDays; $i++): ?>
                                <div class="calendar-cell p-2 rounded-lg text-center <?php echo $settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-gray-100'; ?> text-gray-400">
                                    <div class="text-sm"><?php echo $i; ?></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <!-- 日程列表 -->
                <div class="lg:w-1/4">
                    <div class="bg-white dark:bg-dark-card rounded-xl shadow-lg p-4 transition-all duration-300">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold"><?php echo $lang['Events']; ?></h2>
                            <?php if (isAdmin()): ?>
                                <button 
                                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    onclick="openEventModal()"
                                >
                                    <i class="fa fa-plus"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 过滤 -->
                        <div class="flex space-x-2 mb-4 overflow-x-auto pb-2">
                            <button 
                                class="px-3 py-1 text-sm rounded-lg <?php echo $filter == 'all' ? 'bg-primary text-white' : ($settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-gray-100'); ?> transition-colors whitespace-nowrap"
                                onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo $currentYear; ?>&month=<?php echo $currentMonth; ?>&filter=all<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>'"
                            >
                                <?php echo $lang['All Events']; ?>
                            </button>
                            
                            <button 
                                class="px-3 py-1 text-sm rounded-lg <?php echo $filter == 'upcoming' ? 'bg-primary text-white' : ($settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-gray-100'); ?> transition-colors whitespace-nowrap"
                                onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo $currentYear; ?>&month=<?php echo $currentMonth; ?>&filter=upcoming<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>'"
                            >
                                <?php echo $lang['Upcoming Events']; ?>
                            </button>
                            
                            <button 
                                class="px-3 py-1 text-sm rounded-lg <?php echo $filter == 'past' ? 'bg-primary text-white' : ($settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-gray-100'); ?> transition-colors whitespace-nowrap"
                                onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo $currentYear; ?>&month=<?php echo $currentMonth; ?>&filter=past<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>'"
                            >
                                <?php echo $lang['Past Events']; ?>
                            </button>
                        </div>
                        
                        <!-- 日程列表 -->
                        <div class="space-y-3 max-h-[calc(100vh-250px)] overflow-y-auto pr-1">
                            <?php if (empty($filteredEventsFinal)): ?>
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <i class="fa fa-calendar-o text-3xl mb-2"></i>
                                    <p><?php echo $lang['No events found']; ?></p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($filteredEventsFinal as $event): ?>
                                    <div class="p-3 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-gray-50'; ?> border border-gray-100 dark:border-gray-800 hover:shadow-md transition-all duration-200 event-item" data-id="<?php echo $event['id']; ?>">
                                        <div class="flex justify-between items-start">
                                            <h3 class="font-medium truncate"><?php echo htmlspecialchars($event['title']); ?></h3>
                                            <?php if (isAdmin()): ?>
                                                <div class="flex space-x-1">
                                                    <?php if ($event['reminder'] == 'on'): ?>
                                                        <i class="fa fa-bell-o text-amber-500"></i>
                                                    <?php endif; ?>
                                                    <button class="edit-event p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button class="delete-event p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/50 text-red-500 dark:text-red-400 transition-colors">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <?php echo date('Y-m-d', strtotime($event['date'])); ?>
                                            <?php if (!empty($event['time'])): ?>
                                                <span class="ml-2"><?php echo $event['time']; ?></span>
                                            <?php endif; ?>
                                        </p>
                                        
                                        <?php if (!empty($event['description'])): ?>
                                            <p class="text-sm mt-1 line-clamp-2"><?php echo htmlspecialchars($event['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- 页脚 -->
    <footer class="mt-10 py-6 <?php echo $settings['dark_mode'] ? 'bg-dark-card' : 'bg-white'; ?> border-t border-gray-200 dark:border-gray-800">
        <div class="container mx-auto px-4 text-center text-gray-500 dark:text-gray-400 text-sm">
            <p>© <?php echo date('Y'); ?> 万年历日程安排 | 一个功能丰富的PHP单文件应用</p>
        </div>
    </footer>
    
    <!-- 登录模态框 -->
    <div id="login-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl w-full max-w-md p-6 fade-in">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa fa-user text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold">登录</h3>
            </div>
            
            <form id="login-form" method="post">
                <input type="hidden" name="action" value="login">
                
                <div class="space-y-4">
                    <div>
                        <label for="login-username" class="block text-sm font-medium mb-1">用户名</label>
                        <input 
                            type="text" 
                            id="login-username" 
                            name="username" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                            required
                        >
                    </div>
                    
                    <div>
                        <label for="login-password" class="block text-sm font-medium mb-1">密码</label>
                        <input 
                            type="password" 
                            id="login-password" 
                            name="password" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                            required
                        >
                    </div>
                </div>
                
                <div class="flex justify-center space-x-3 mt-6">
                    <button type="button" onclick="closeLoginModal()" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        取消
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        登录
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 日程模态框 -->
    <div id="event-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl w-full max-w-md p-6 fade-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold" id="event-modal-title">添加日程</h3>
                <button onclick="closeEventModal()" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            
            <form id="event-form" method="post">
                <input type="hidden" name="action" value="save_event">
                <input type="hidden" id="event-id" name="id">
                
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium mb-1">标题</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                            required
                        >
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium mb-1">描述</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                            rows="3"
                        ></textarea>
                    </div>
                    
                    <div>
                        <label for="date" class="block text-sm font-medium mb-1">日期</label>
                        <input 
                            type="date" 
                            id="date" 
                            name="date" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                            required
                        >
                    </div>
                    
                    <div>
                        <label for="time" class="block text-sm font-medium mb-1">时间</label>
                        <input 
                            type="time" 
                            id="time" 
                            name="time" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                        >
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="reminder" 
                            name="reminder" 
                            class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                        >
                        <label for="reminder" class="text-sm">提醒</label>
                    </div>
                </div>
                
                <div class="flex justify-center space-x-3 mt-6">
                    <button type="button" onclick="closeEventModal()" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        取消
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        保存
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 删除确认模态框 -->
    <div id="delete-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl w-full max-w-md p-6 fade-in">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa fa-trash text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold">确认删除</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2">确定要删除这个日程吗？此操作不可撤销。</p>
            </div>
            
            <form id="delete-form" method="post">
                <input type="hidden" name="action" value="delete_event">
                <input type="hidden" id="delete-id" name="id">
                
                <div class="flex justify-center space-x-3 mt-6">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        取消
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        删除
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 设置模态框 -->
    <div id="settings-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl w-full max-w-md p-6 fade-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">设置</h3>
                <button onclick="closeSettingsModal()" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            
            <form id="settings-form" method="post">
                <input type="hidden" name="action" value="save_settings">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">主题</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="dark_mode" value="0" <?php echo !$settings['dark_mode'] ? 'checked' : ''; ?> class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <span class="ml-2">浅色模式</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="dark_mode" value="1" <?php echo $settings['dark_mode'] ? 'checked' : ''; ?> class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <span class="ml-2">深色模式</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">语言</label>
                        <select name="language" class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50">
                            <option value="zh" <?php echo $settings['language'] == 'zh' ? 'selected' : ''; ?>>中文</option>
                            <option value="en" <?php echo $settings['language'] == 'en' ? 'selected' : ''; ?>>英文</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">星期开始于</label>
                        <select name="first_day_of_week" class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50">
                            <option value="0" <?php echo $settings['first_day_of_week'] == 0 ? 'selected' : ''; ?>>星期日</option>
                            <option value="1" <?php echo $settings['first_day_of_week'] == 1 ? 'selected' : ''; ?>>星期一</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="show_lunar" 
                            name="show_lunar" 
                            class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                            <?php echo $settings['show_lunar'] ? 'checked' : ''; ?>
                        >
                        <label for="show_lunar" class="text-sm">显示农历</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="show_holidays" 
                            name="show_holidays" 
                            class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                            <?php echo $settings['show_holidays'] ? 'checked' : ''; ?>
                        >
                        <label for="show_holidays" class="text-sm">显示节假日</label>
                    </div>
                </div>
                
                <div class="flex justify-center space-x-3 mt-6">
                    <button type="button" onclick="closeSettingsModal()" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        取消
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        保存设置
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 移动端搜索框 -->
    <div id="mobile-search-container" class="fixed inset-0 bg-black/50 flex items-center justify-center z-40 hidden">
        <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl w-full max-w-md p-4 fade-in">
            <div class="flex items-center">
                <input 
                    type="text" 
                    placeholder="搜索日程..." 
                    class="w-full px-4 py-2 rounded-l-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                    onchange="this.form.submit()"
                >
                <button onclick="toggleSearch()" class="px-4 py-2 bg-primary text-white rounded-r-lg hover:bg-primary/90 transition-colors">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // 模态框控制
        function openLoginModal() {
            document.getElementById('login-modal').classList.remove('hidden');
        }
        
        function closeLoginModal() {
            document.getElementById('login-modal').classList.add('hidden');
        }
        
        function openEventModal() {
            document.getElementById('event-modal').classList.remove('hidden');
            document.getElementById('event-modal-title').textContent = '添加日程';
            document.getElementById('event-id').value = '';
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('date').value = '';
            document.getElementById('time').value = '';
            document.getElementById('reminder').checked = false;
        }
        
        function closeEventModal() {
            document.getElementById('event-modal').classList.add('hidden');
        }
        
        function openDeleteModal(id) {
            document.getElementById('delete-modal').classList.remove('hidden');
            document.getElementById('delete-id').value = id;
        }
        
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
        }
        
        function openSettingsModal() {
            document.getElementById('settings-modal').classList.remove('hidden');
        }
        
        function closeSettingsModal() {
            document.getElementById('settings-modal').classList.add('hidden');
        }
        
        function toggleSearch() {
            const container = document.getElementById('mobile-search-container');
            container.classList.toggle('hidden');
        }
        
        // 编辑事件
        document.addEventListener('DOMContentLoaded', function() {
            // 编辑事件按钮
            document.querySelectorAll('.edit-event').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.closest('.event-item').dataset.id;
                    let event = null;
                    
                    // 查找事件数据
                    <?php foreach ($events as $event): ?>
                        if ('<?php echo $event['id']; ?>' === eventId) {
                            event = {
                                id: '<?php echo $event['id']; ?>',
                                title: '<?php echo addslashes($event['title']); ?>',
                                description: '<?php echo addslashes($event['description']); ?>',
                                date: '<?php echo $event['date']; ?>',
                                time: '<?php echo $event['time']; ?>',
                                reminder: '<?php echo $event['reminder']; ?>'
                            };
                        }
                    <?php endforeach; ?>
                    
                    if (event) {
                        document.getElementById('event-modal').classList.remove('hidden');
                        document.getElementById('event-modal-title').textContent = '编辑日程';
                        document.getElementById('event-id').value = event.id;
                        document.getElementById('title').value = event.title;
                        document.getElementById('description').value = event.description;
                        document.getElementById('date').value = event.date;
                        document.getElementById('time').value = event.time;
                        document.getElementById('reminder').checked = event.reminder === 'on';
                    }
                });
            });
            
            // 删除事件按钮
            document.querySelectorAll('.delete-event').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.closest('.event-item').dataset.id;
                    openDeleteModal(eventId);
                });
            });
            
            // 点击模态框外部关闭
            document.querySelectorAll('.fixed').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.add('hidden');
                    }
                });
            });
            
            // 添加淡入动画
            document.querySelectorAll('.fade-in').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                    el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                }, 10);
            });
        });
    </script>
</body>
</html>
