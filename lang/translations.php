<?php
$translations = [
    'en' => [
        // Navigation
        'home' => 'Home',
        'about' => 'About',
        'products' => 'Products',
        'past_events' => 'Past Events',
        'contact' => 'Contact',
        'share_image' => 'Share Image',
        
        // Common
        'read_more' => 'Read More',
        'submit' => 'Submit',
        'back' => 'Back',
        
        // Home Page
        'welcome_title' => 'Welcome to MY WEB',
        'latest_products' => 'Latest Products',
        'featured_events' => 'Featured Events',
        
        // About Page
        'about_title' => 'About Us',
        'company_history' => 'Company History',
        
        // Products
        'product_details' => 'Product Details',
        'product_number' => 'Product Number',
        'price' => 'Price',
        
        // Events
        'event_details' => 'Event Details',
        'event_gallery' => 'Event Gallery',
        'no_events' => 'No events have been added yet.',
        
        // Contact
        'contact_title' => 'Contact Us',
        'name' => 'Name',
        'email' => 'Email',
        'message' => 'Message',
        'send_message' => 'Send Message',
        
        // Upload
        'upload_title' => 'Share Your Image',
        'image_title' => 'Image Title',
        'description' => 'Description',
        'select_image' => 'Select Image',
        'upload_image' => 'Upload Image'
    ],
    
    'zh-cn' => [
        // Navigation
        'home' => '首页',
        'about' => '关于我们',
        'products' => '产品',
        'past_events' => '活动回顾',
        'contact' => '联系我们',
        'share_image' => '分享图片',
        
        // Common
        'read_more' => '查看更多',
        'submit' => '提交',
        'back' => '返回',
        
        // Home Page
        'welcome_title' => '欢迎来到MY WEB',
        'latest_products' => '最新产品',
        'featured_events' => '精选活动',
        
        // About Page
        'about_title' => '关于我们',
        'company_history' => '公司历史',
        
        // Products
        'product_details' => '产品详情',
        'product_number' => '产品编号',
        'price' => '价格',
        
        // Events
        'event_details' => '活动详情',
        'event_gallery' => '活动图库',
        'no_events' => '暂无活动。',
        
        // Contact
        'contact_title' => '联系我们',
        'name' => '姓名',
        'email' => '电子邮箱',
        'message' => '留言',
        'send_message' => '发送消息',
        
        // Upload
        'upload_title' => '分享图片',
        'image_title' => '图片标题',
        'description' => '描述',
        'select_image' => '选择图片',
        'upload_image' => '上传图片'
    ],
    
    'zh-hk' => [
        // Navigation
        'home' => '首頁',
        'about' => '關於我們',
        'products' => '產品',
        'past_events' => '活動回顧',
        'contact' => '聯絡我們',
        'share_image' => '分享圖片',
        
        // Common
        'read_more' => '查看更多',
        'submit' => '提交',
        'back' => '返回',
        
        // Home Page
        'welcome_title' => '歡迎來到MY WEB',
        'latest_products' => '最新產品',
        'featured_events' => '精選活動',
        
        // About Page
        'about_title' => '關於我們',
        'company_history' => '公司歷史',
        
        // Products
        'product_details' => '產品詳情',
        'product_number' => '產品編號',
        'price' => '價格',
        
        // Events
        'event_details' => '活動詳情',
        'event_gallery' => '活動圖庫',
        'no_events' => '暫無活動。',
        
        // Contact
        'contact_title' => '聯絡我們',
        'name' => '姓名',
        'email' => '電郵地址',
        'message' => '留言',
        'send_message' => '發送訊息',
        
        // Upload
        'upload_title' => '分享圖片',
        'image_title' => '圖片標題',
        'description' => '描述',
        'select_image' => '選擇圖片',
        'upload_image' => '上傳圖片'
    ]
];

function t($key) {
    global $translations;
    $lang = $_SESSION['lang'] ?? 'en';
    return $translations[$lang][$key] ?? $key;
} 