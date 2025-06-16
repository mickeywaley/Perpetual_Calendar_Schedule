<?php
// 启用会话
session_start();

// 配置时区
date_default_timezone_set('Asia/Shanghai');

// 数据库配置
define('DB_FILE', __DIR__ . '/calendar_data.json');

// 确保数据文件存在
if (!file_exists(DB_FILE)) {
    file_put_contents(DB_FILE, json_encode(['events' => [], 'settings' => []]));
}

// 加载数据
$data = json_decode(file_get_contents(DB_FILE), true);
$events = $data['events'] ?? [];
$settings = $data['settings'] ?? [];

// 默认设置
$defaultSettings = [
    'dark_mode' => false,
    'language' => 'zh',
    'first_day_of_week' => 0, // 0=周日, 1=周一
    'show_lunar' => true,
    'show_holidays' => true
];

// 合并默认设置和用户设置
$settings = array_merge($defaultSettings, $settings);

// 语言包
$languages = [
    'zh' => [
        'January' => '一月',
        'February' => '二月',
        'March' => '三月',
        'April' => '四月',
        'May' => '五月',
        'June' => '六月',
        'July' => '七月',
        'August' => '八月',
        'September' => '九月',
        'October' => '十月',
        'November' => '十一月',
        'December' => '十二月',
        'Sunday' => '周日',
        'Monday' => '周一',
        'Tuesday' => '周二',
        'Wednesday' => '周三',
        'Thursday' => '周四',
        'Friday' => '周五',
        'Saturday' => '周六',
        'Add Event' => '添加日程',
        'Edit Event' => '编辑日程',
        'Delete Event' => '删除日程',
        'Title' => '标题',
        'Description' => '描述',
        'Date' => '日期',
        'Time' => '时间',
        'Reminder' => '提醒',
        'Save' => '保存',
        'Cancel' => '取消',
        'Confirm' => '确认',
        'Are you sure you want to delete this event?' => '确定要删除此日程吗？',
        'Previous Month' => '上月',
        'Next Month' => '下月',
        'Today' => '今天',
        'No events' => '无日程',
        'Dark Mode' => '深色模式',
        'Light Mode' => '浅色模式',
        'Language' => '语言',
        'Settings' => '设置',
        'Search' => '搜索',
        'Lunar Calendar' => '农历',
        'Holidays' => '节假日',
        'Search Events' => '搜索日程',
        'All Events' => '所有日程',
        'Upcoming Events' => '即将到来的日程',
        'Past Events' => '过去的日程',
        'Filter' => '筛选',
        'Clear' => '清除',
        'Close' => '关闭',
        'Settings Saved' => '设置已保存',
        'Event Added' => '日程已添加',
        'Event Updated' => '日程已更新',
        'Event Deleted' => '日程已删除',
        'No events found' => '未找到日程',
        'Week' => '周',
        'Day' => '日',
        'Month' => '月',
        'Year' => '年'
    ],
    'en' => [
        'January' => 'January',
        'February' => 'February',
        'March' => 'March',
        'April' => 'April',
        'May' => 'May',
        'June' => 'June',
        'July' => 'July',
        'August' => 'August',
        'September' => 'September',
        'October' => 'October',
        'November' => 'November',
        'December' => 'December',
        'Sunday' => 'Sunday',
        'Monday' => 'Monday',
        'Tuesday' => 'Tuesday',
        'Wednesday' => 'Wednesday',
        'Thursday' => 'Thursday',
        'Friday' => 'Friday',
        'Saturday' => 'Saturday',
        'Add Event' => 'Add Event',
        'Edit Event' => 'Edit Event',
        'Delete Event' => 'Delete Event',
        'Title' => 'Title',
        'Description' => 'Description',
        'Date' => 'Date',
        'Time' => 'Time',
        'Reminder' => 'Reminder',
        'Save' => 'Save',
        'Cancel' => 'Cancel',
        'Confirm' => 'Confirm',
        'Are you sure you want to delete this event?' => 'Are you sure you want to delete this event?',
        'Previous Month' => 'Previous Month',
        'Next Month' => 'Next Month',
        'Today' => 'Today',
        'No events' => 'No events',
        'Dark Mode' => 'Dark Mode',
        'Light Mode' => 'Light Mode',
        'Language' => 'Language',
        'Settings' => 'Settings',
        'Search' => 'Search',
        'Lunar Calendar' => 'Lunar Calendar',
        'Holidays' => 'Holidays',
        'Search Events' => 'Search Events',
        'All Events' => 'All Events',
        'Upcoming Events' => 'Upcoming Events',
        'Past Events' => 'Past Events',
        'Filter' => 'Filter',
        'Clear' => 'Clear',
        'Close' => 'Close',
        'Settings Saved' => 'Settings Saved',
        'Event Added' => 'Event Added',
        'Event Updated' => 'Event Updated',
        'Event Deleted' => 'Event Deleted',
        'No events found' => 'No events found',
        'Week' => 'Week',
        'Day' => 'Day',
        'Month' => 'Month',
        'Year' => 'Year'
    ]
];

// 获取当前语言
$lang = $languages[$settings['language']];

// 农历转换函数（简化版）
function solar2lunar($year, $month, $day) {
    // 简化版农历转换，实际应用中应使用更精确的算法
    // 这里仅为演示，返回随机的农历日期
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
    // 中国法定节假日（简化版）
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

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 添加/编辑日程
    if (isset($_POST['action']) && $_POST['action'] === 'save_event') {
        $event = [
            'id' => isset($_POST['id']) ? $_POST['id'] : uniqid(),
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'date' => $_POST['date'],
            'time' => $_POST['time'],
            'reminder' => $_POST['reminder']
        ];
        
        // 更新或添加事件
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
        
        // 保存数据
        $data['events'] = $events;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        
        // 设置成功消息
        $_SESSION['message'] = $lang['Event Added'];
        if ($found) {
            $_SESSION['message'] = $lang['Event Updated'];
        }
        
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
        
        // 保存数据
        $data['events'] = $events;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        
        // 设置成功消息
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
        
        // 保存设置
        $data['settings'] = $settings;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        
        // 设置成功消息
        $_SESSION['message'] = $lang['Settings Saved'];
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// 获取当前年月
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$currentDay = date('j');

// 获取当月第一天是星期几
$firstDayOfMonth = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$firstDayOfWeek = date('w', $firstDayOfMonth);

// 调整星期开始日
if ($settings['first_day_of_week'] == 1) { // 周一开始
    $firstDayOfWeek = ($firstDayOfWeek == 0) ? 6 : $firstDayOfWeek - 1;
}

// 获取当月天数
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

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

// 获取今日提醒
$todayReminders = [];
foreach ($events as $event) {
    $eventDate = date('Y-m-d', strtotime($event['date']));
    $today = date('Y-m-d');
    
    if ($eventDate == $today && $event['reminder'] == 'on') {
        $todayReminders[] = $event;
    }
}
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
                min-height: 80px;
            }
            .event-badge {
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
    </style>
    
    <style>
        /* 动画效果 */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* 日历单元格悬停效果 */
        .calendar-cell:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }
        
        /* 滚动条样式 */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* 深色模式滚动条 */
        .dark ::-webkit-scrollbar-track {
            background: #1e1e1e;
        }
        
        .dark ::-webkit-scrollbar-thumb {
            background: #3a3a3a;
        }
        
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #5a5a5a;
        }
    </style>
</head>
<body class="font-inter <?php echo $settings['dark_mode'] ? 'dark bg-dark-bg text-dark-text' : 'bg-gray-50 text-gray-900'; ?> transition-colors duration-300">
    <!-- 导航栏 -->
    <header class="sticky top-0 z-50 <?php echo $settings['dark_mode'] ? 'bg-dark-card shadow-lg' : 'bg-white shadow-md'; ?> transition-all duration-300">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa fa-calendar text-primary text-2xl"></i>
                <h1 class="text-xl font-bold"><?php echo $lang['Calendar']; ?></h1>
            </div>
            
            <div class="flex items-center space-x-3">
                <!-- 搜索框 -->
                <div class="relative hidden md:block">
                    <input 
                        type="text" 
                        placeholder="<?php echo $lang['Search Events']; ?>" 
                        class="pl-10 pr-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50 w-64"
                        value="<?php echo htmlspecialchars($searchTerm); ?>"
                        onchange="this.form.submit()"
                    >
                    <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                
                <!-- 移动端搜索按钮 -->
                <button class="md:hidden p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" onclick="toggleSearch()">
                    <i class="fa fa-search"></i>
                </button>
                
                <!-- 移动端搜索框 -->
                <div id="mobile-search" class="md:hidden absolute top-full left-0 right-0 bg-white dark:bg-dark-card shadow-lg p-4 hidden">
                    <form action="" method="get">
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search"
                                placeholder="<?php echo $lang['Search Events']; ?>" 
                                class="w-full pl-10 pr-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                                value="<?php echo htmlspecialchars($searchTerm); ?>"
                            >
                            <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </form>
                </div>
                
                <!-- 深色/浅色模式切换 -->
                <button 
                    class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                    onclick="toggleDarkMode()"
                >
                    <i class="fa <?php echo $settings['dark_mode'] ? 'fa-sun-o' : 'fa-moon-o'; ?>"></i>
                </button>
                
                <!-- 设置按钮 -->
                <button 
                    class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                    onclick="toggleSettingsModal()"
                >
                    <i class="fa fa-cog"></i>
                </button>
            </div>
        </div>
    </header>
    
    <!-- 主要内容 -->
    <main class="container mx-auto px-4 py-6">
        <!-- 成功消息 -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="fixed top-20 right-4 z-50 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg fade-in">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
            <script>
                setTimeout(() => {
                    document.querySelector('.fade-in').style.opacity = '0';
                    setTimeout(() => {
                        document.querySelector('.fade-in').style.display = 'none';
                    }, 300);
                }, 3000);
            </script>
        <?php endif; ?>
        
        <!-- 提醒 -->
        <?php if (!empty($todayReminders)): ?>
            <div class="mb-6 p-4 rounded-lg bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200">
                <h3 class="font-bold mb-2 flex items-center">
                    <i class="fa fa-bell mr-2"></i> <?php echo count($todayReminders); ?> <?php echo $lang['Upcoming Events']; ?>
                </h3>
                <ul class="space-y-2">
                    <?php foreach ($todayReminders as $reminder): ?>
                        <li class="flex items-start">
                            <i class="fa fa-circle text-xs mt-2 mr-2 text-amber-500"></i>
                            <div>
                                <span class="font-medium"><?php echo htmlspecialchars($reminder['title']); ?></span>
                                <span class="ml-2 text-sm"><?php echo htmlspecialchars($reminder['time']); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- 日历视图 -->
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
                        
                        // 调整星期开始日
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
                        <!-- 上个月的日期 -->
                        <?php for ($i = 0; $i < $firstDayOfWeek; $i++): ?>
                            <div class="calendar-cell p-2 rounded-lg text-center <?php echo $settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-gray-100'; ?> text-gray-400">
                                <div class="text-sm"><?php echo cal_days_in_month(CAL_GREGORIAN, $currentMonth - 1 ?: 12, $currentYear - ($currentMonth == 1 ? 1 : 0)) - $firstDayOfWeek + $i + 1; ?></div>
                            </div>
                        <?php endfor; ?>
                        
                        <!-- 当月的日期 -->
                        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                            <?php
                            $currentDate = $currentYear . '-' . $currentMonth . '-' . $day;
                            $isToday = ($day == $currentDay && $currentMonth == date('n') && $currentYear == date('Y'));
                            $hasEvents = isset($monthEvents[$day]);
                            $isHoliday = isset($holidays[$currentDate]);
                            $lunarDate = $settings['show_lunar'] ? solar2lunar($currentYear, $currentMonth, $day) : '';
                            ?>
                            
                            <div class="calendar-cell p-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg/50' : 'bg-white'; ?> border border-gray-100 dark:border-gray-800 hover:shadow-md transition-all duration-200 cursor-pointer <?php echo $isToday ? 'ring-2 ring-primary' : ''; ?>">
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
                                                +<?php echo count($monthEvents[$day]) - 2; ?> <?php echo $lang['more']; ?>
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
                        
                        <!-- 下个月的日期 -->
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
                        <button 
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            onclick="openEventModal()"
                        >
                            <i class="fa fa-plus"></i>
                        </button>
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
                    
                    <!-- 清空搜索 -->
                    <?php if (!empty($searchTerm)): ?>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                "<?php echo htmlspecialchars($searchTerm); ?>"
                            </span>
                            <button 
                                class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?year=<?php echo $currentYear; ?>&month=<?php echo $currentMonth; ?>&filter=<?php echo $filter; ?>'"
                            >
                                <?php echo $lang['Clear']; ?>
                            </button>
                        </div>
                    <?php endif; ?>
                    
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
    </main>
    
    <!-- 页脚 -->
    <footer class="mt-10 py-6 <?php echo $settings['dark_mode'] ? 'bg-dark-card' : 'bg-white'; ?> border-t border-gray-200 dark:border-gray-800">
        <div class="container mx-auto px-4 text-center text-gray-500 dark:text-gray-400 text-sm">
            <p>© <?php echo date('Y'); ?> 万年历日程安排 | 一个功能丰富的PHP单文件应用</p>
        </div>
    </footer>
    
    <!-- 添加/编辑日程模态框 -->
    <div id="event-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl w-full max-w-md p-6 fade-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold" id="modal-title"><?php echo $lang['Add Event']; ?></h3>
                <button onclick="closeEventModal()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            
            <form id="event-form" method="post">
                <input type="hidden" name="action" value="save_event">
                <input type="hidden" name="id" id="event-id">
                
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium mb-1"><?php echo $lang['Title']; ?></label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                            required
                        >
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium mb-1"><?php echo $lang['Description']; ?></label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="3"
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                        ></textarea>
                    </div>
                    
                    <div>
                        <label for="date" class="block text-sm font-medium mb-1"><?php echo $lang['Date']; ?></label>
                        <input 
                            type="date" 
                            id="date" 
                            name="date" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                            value="<?php echo date('Y-m-d'); ?>"
                            required
                        >
                    </div>
                    
                    <div>
                        <label for="time" class="block text-sm font-medium mb-1"><?php echo $lang['Time']; ?></label>
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
                            class="w-4 h-4 text-primary focus:ring-primary/50 border-gray-300 rounded"
                        >
                        <label for="reminder" class="ml-2 text-sm"><?php echo $lang['Reminder']; ?></label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEventModal()" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <?php echo $lang['Cancel']; ?>
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <?php echo $lang['Save']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 删除确认模态框 -->
    <div id="delete-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl w-full max-w-md p-6 fade-in">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa fa-exclamation-triangle text-2xl text-red-500"></i>
                </div>
                <h3 class="text-xl font-bold"><?php echo $lang['Confirm']; ?></h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2">
                    <?php echo $lang['Are you sure you want to delete this event?']; ?>
                </p>
            </div>
            
            <form id="delete-form" method="post">
                <input type="hidden" name="action" value="delete_event">
                <input type="hidden" name="id" id="delete-id">
                
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <?php echo $lang['Cancel']; ?>
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        <?php echo $lang['Delete Event']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 设置模态框 -->
    <div id="settings-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-dark-card rounded-xl shadow-2xl w-full max-w-md p-6 fade-in">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold"><?php echo $lang['Settings']; ?></h3>
                <button onclick="toggleSettingsModal()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            
            <form id="settings-form" method="post">
                <input type="hidden" name="action" value="save_settings">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1"><?php echo $lang['Language']; ?></label>
                        <select 
                            name="language" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                        >
                            <option value="zh" <?php echo $settings['language'] == 'zh' ? 'selected' : ''; ?>>中文</option>
                            <option value="en" <?php echo $settings['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1"><?php echo $lang['First Day of Week']; ?></label>
                        <select 
                            name="first_day_of_week" 
                            class="w-full px-4 py-2 rounded-lg <?php echo $settings['dark_mode'] ? 'bg-dark-bg border border-gray-700 text-dark-text' : 'bg-gray-100'; ?> focus:outline-none focus:ring-2 focus:ring-primary/50"
                        >
                            <option value="0" <?php echo $settings['first_day_of_week'] == 0 ? 'selected' : ''; ?>><?php echo $lang['Sunday']; ?></option>
                            <option value="1" <?php echo $settings['first_day_of_week'] == 1 ? 'selected' : ''; ?>><?php echo $lang['Monday']; ?></option>
                        </select>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium"><?php echo $lang['Dark Mode']; ?></label>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input 
                                type="checkbox" 
                                name="dark_mode" 
                                id="dark-mode-toggle" 
                                class="sr-only"
                                <?php echo $settings['dark_mode'] ? 'checked' : ''; ?>
                            >
                            <label for="dark-mode-toggle" class="block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium"><?php echo $lang['Lunar Calendar']; ?></label>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input 
                                type="checkbox" 
                                name="show_lunar" 
                                id="show-lunar-toggle" 
                                class="sr-only"
                                <?php echo $settings['show_lunar'] ? 'checked' : ''; ?>
                            >
                            <label for="show-lunar-toggle" class="block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium"><?php echo $lang['Holidays']; ?></label>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input 
                                type="checkbox" 
                                name="show_holidays" 
                                id="show-holidays-toggle" 
                                class="sr-only"
                                <?php echo $settings['show_holidays'] ? 'checked' : ''; ?>
                            >
                            <label for="show-holidays-toggle" class="block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="toggleSettingsModal()" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <?php echo $lang['Cancel']; ?>
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <?php echo $lang['Save']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // 深色模式切换
        function toggleDarkMode() {
            const isDarkMode = document.documentElement.classList.toggle('dark');
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            if (darkModeToggle) {
                darkModeToggle.checked = isDarkMode;
            }
            
            // 发送请求保存设置
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=save_settings&dark_mode=' + (isDarkMode ? '1' : '0'),
            });
        }
        
        // 事件模态框控制
        function openEventModal(eventId = null) {
            const modal = document.getElementById('event-modal');
            const modalTitle = document.getElementById('modal-title');
            const eventIdInput = document.getElementById('event-id');
            const titleInput = document.getElementById('title');
            const descriptionInput = document.getElementById('description');
            const dateInput = document.getElementById('date');
            const timeInput = document.getElementById('time');
            const reminderInput = document.getElementById('reminder');
            
            // 重置表单
            document.getElementById('event-form').reset();
            
            if (eventId) {
                // 编辑现有事件
                modalTitle.textContent = '<?php echo $lang['Edit Event']; ?>';
                eventIdInput.value = eventId;
                
                // 查找事件数据
                <?php foreach ($events as $event): ?>
                    if ('<?php echo $event['id']; ?>' === eventId) {
                        titleInput.value = '<?php echo htmlspecialchars($event['title']); ?>';
                        descriptionInput.value = '<?php echo htmlspecialchars($event['description']); ?>';
                        dateInput.value = '<?php echo $event['date']; ?>';
                        timeInput.value = '<?php echo $event['time']; ?>';
                        reminderInput.checked = '<?php echo $event['reminder']; ?>' === 'on';
                    }
                <?php endforeach; ?>
            } else {
                // 添加新事件
                modalTitle.textContent = '<?php echo $lang['Add Event']; ?>';
                eventIdInput.value = '';
                dateInput.value = '<?php echo date('Y-m-d'); ?>';
            }
            
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        
        function closeEventModal() {
            const modal = document.getElementById('event-modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        
        // 删除模态框控制
        function openDeleteModal(eventId) {
            const deleteIdInput = document.getElementById('delete-id');
            deleteIdInput.value = eventId;
            
            document.getElementById('delete-modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        
        // 设置模态框控制
        function toggleSettingsModal() {
            const modal = document.getElementById('settings-modal');
            modal.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }
        
        // 移动端搜索框控制
        function toggleSearch() {
            const mobileSearch = document.getElementById('mobile-search');
            mobileSearch.classList.toggle('hidden');
        }
        
        // 为所有编辑按钮添加事件监听器
        document.addEventListener('DOMContentLoaded', function() {
            // 深色模式切换
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', toggleDarkMode);
            }
            
            // 编辑按钮事件
            document.querySelectorAll('.edit-event').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.closest('.event-item').dataset.id;
                    openEventModal(eventId);
                });
            });
            
            // 删除按钮事件
            document.querySelectorAll('.delete-event').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.closest('.event-item').dataset.id;
                    openDeleteModal(eventId);
                });
            });
            
            // 日历单元格点击事件
            document.querySelectorAll('.calendar-cell').forEach(cell => {
                cell.addEventListener('click', function() {
                    // 获取日期
                    const dayElement = this.querySelector('span.font-medium');
                    if (dayElement) {
                        const day = dayElement.textContent;
                        document.getElementById('date').value = '<?php echo $currentYear; ?>-<?php echo str_pad($currentMonth, 2, '0', STR_PAD_LEFT); ?>-' + day.padStart(2, '0');
                        openEventModal();
                    }
                });
            });
            
            // 设置切换开关样式
            function setupToggle(toggleId, color = 'bg-primary') {
                const toggle = document.getElementById(toggleId);
                if (toggle) {
                    const label = toggle.nextElementSibling;
                    if (label) {
                        toggle.addEventListener('change', function() {
                            if (this.checked) {
                                label.classList.add(color);
                                label.classList.remove('bg-gray-300');
                            } else {
                                label.classList.remove(color);
                                label.classList.add('bg-gray-300');
                            }
                        });
                        
                        // 初始化样式
                        if (toggle.checked) {
                            label.classList.add(color);
                            label.classList.remove('bg-gray-300');
                        }
                    }
                }
            }
            
            setupToggle('dark-mode-toggle');
            setupToggle('show-lunar-toggle');
            setupToggle('show-holidays-toggle');
        });
    </script>
</body>
</html>
