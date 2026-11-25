<?php
require_once __DIR__ . '/load_env.php';

$SITE = [
  'name' => env('SITE_NAME', 'WEBmood'),
  'tagline' => env('SITE_TAGLINE', 'BUILD. CREATE. INNOVATE.'),
  'subTagline' => env('SITE_SUBTAGLINE', 'Modern Web Experiences for the Digital World.'),
  'title' => env('SITE_TITLE', 'WEBmood — Web Developer & Creative Developer'),
  'description' => env('SITE_DESCRIPTION', 'WEBmood is a professional Web Developer creating modern websites, web applications, e-commerce platforms and interactive 3D digital experiences.'),
  'year' => (int) env('SITE_YEAR', '2026'),
  'phone' => env('PHONE', '+998 91 708 09 92'),
  'phoneHref' => env('PHONE_HREF', 'tel:+998917080992'),
  'telegramUsername' => env('TELEGRAM_USERNAME', 'YOUR_USERNAME'),
  'email' => env('EMAIL', 'hello@nexdev.dev'),
  'url' => env('SITE_URL', 'http://localhost:8000'),
  'profileName' => env('PROFILE_NAME', 'WEBmood'),
  'profileRole' => env('PROFILE_ROLE', 'Web Developer'),
  'profileTg' => env('PROFILE_TG', env('TELEGRAM_USERNAME', 'YOUR_USERNAME')),
];

$TELEGRAM = [
  'botToken' => env('TELEGRAM_BOT_TOKEN', 'PUT_BOT_TOKEN_HERE'),
  'adminChatId' => env('TELEGRAM_ADMIN_CHAT_ID', 'PUT_ADMIN_CHAT_ID_HERE'),
  'botUsername' => env('TELEGRAM_BOT_USERNAME', 'WEBmood_OrdersBot'),
];

$SITE['telegramHref'] = 'https://t.me/' . $SITE['telegramUsername'];
$SITE['telegramHandle'] = '@' . $SITE['telegramUsername'];
$SITE['social'] = [
  'telegram' => env('SOCIAL_TELEGRAM', $SITE['telegramHref']),
  'instagram' => env('SOCIAL_INSTAGRAM', 'https://instagram.com/' . $SITE['telegramUsername']),
  'github' => env('SOCIAL_GITHUB', 'https://github.com/' . $SITE['telegramUsername']),
  'linkedin' => env('SOCIAL_LINKEDIN', 'https://linkedin.com/in/' . $SITE['telegramUsername']),
];

$NAV = [
  ['id' => 'home', 'label' => 'Home'],
  ['id' => 'about', 'label' => 'About'],
  ['id' => 'skills', 'label' => 'Skills'],
  ['id' => 'services', 'label' => 'Services'],
  ['id' => 'projects', 'label' => 'Projects'],
  ['id' => 'experience', 'label' => 'Experience'],
  ['id' => 'contact', 'label' => 'Contact'],
];

$STATS = [
  ['value' => 6, 'suffix' => '', 'label' => 'Projects'],
  ['value' => 10, 'suffix' => '+', 'label' => 'Clients'],
  ['value' => 3, 'suffix' => '+', 'label' => 'Years Experience'],
  ['value' => 100, 'suffix' => '%', 'label' => 'Passion'],
];

$SKILLS = [
  'Frontend' => ['HTML', 'CSS', 'JavaScript', 'TypeScript', 'React', 'Next.js'],
  'Backend' => ['Node.js', 'Express', 'Python', 'FastAPI', 'PHP'],
  'Database' => ['PostgreSQL', 'MySQL', 'MongoDB'],
  'Tools' => ['Git', 'GitHub', 'Figma', 'VS Code'],
  '3D' => ['Three.js', 'React Three Fiber', 'Blender'],
];

$SERVICES = [
  ['num' => '01', 'title' => 'Web Development', 'text' => 'Fast and responsive modern websites.'],
  ['num' => '02', 'title' => 'Frontend Development', 'text' => 'Interactive interfaces with React and JavaScript.'],
  ['num' => '03', 'title' => 'Backend Development', 'text' => 'Secure and scalable backend systems.'],
  ['num' => '04', 'title' => 'E-Commerce', 'text' => 'Modern online stores and business platforms.'],
  ['num' => '05', 'title' => '3D Web Experiences', 'text' => 'Interactive Three.js/WebGL experiences.'],
  ['num' => '06', 'title' => 'UI/UX Development', 'text' => 'Turning designs into functional interfaces.'],
];

$PROJECTS = [
  [
    'id' => 'wamsey', 'num' => '01', 'title' => 'WAMSEY', 'category' => 'WEB',
    'type' => 'Wamsey web sayt', 'tech' => ['HTML', 'CSS', 'JavaScript'],
    'overview' => 'Wamsey brendi uchun zamonaviy web sayt.',
    'challenge' => 'Brendni raqamli ko‘rinishga o‘tkazish.',
    'solution' => 'Toza interfeys, tez sahifalar va aniq CTA.',
    'features' => ['Responsive dizayn', 'Brend sahifalari', 'Kontakt'],
    'live' => '#', 'github' => '#', 'accent' => '#7c5cff',
  ],
  [
    'id' => 'olimov-meat', 'num' => '02', 'title' => 'OLIMOV MEAT', 'category' => 'E-COMMERCE',
    'type' => 'Go‘sht do‘koni web sayti', 'tech' => ['HTML', 'CSS', 'JavaScript'],
    'overview' => 'Go‘sht mahsulotlari uchun onlayn vitrina va buyurtma sayti.',
    'challenge' => 'Mahsulotlarni tushunarli va tez ko‘rsatish.',
    'solution' => 'Katalog + kontakt + mobilga mos savdo sayti.',
    'features' => ['Katalog', 'Mobil versiya', 'Buyurtma'],
    'live' => 'https://olimovmeat.uz/', 'github' => '#', 'accent' => '#ef4444',
  ],
  [
    'id' => 'ai-project', 'num' => '03', 'title' => 'AI LOYIHA', 'category' => 'WEB',
    'type' => 'AI asosidagi loyiha', 'tech' => ['PHP', 'JavaScript', 'API'],
    'overview' => 'AI yordamida kontent va yechimlar beradigan web loyiha.',
    'challenge' => 'AI javoblarini sodda interfeysga ulash.',
    'solution' => 'Chat/generator oqimi va admin nazorati.',
    'features' => ['AI javob', 'Prompt oqimi', 'Web interfeys'],
    'live' => '#', 'github' => '#', 'accent' => '#a855f7',
  ],
  [
    'id' => 'webmood', 'num' => '04', 'title' => 'WEBMOOD', 'category' => 'WEB',
    'type' => 'Personal developer portfolio', 'tech' => ['PHP', 'JavaScript', 'CSS'],
    'overview' => 'WEBmood personal brand va 3D portfolio.',
    'challenge' => 'Kod + dizayn + 3D ni bir sahifada jamlash.',
    'solution' => 'Glass UI, 3D fon va Telegram bot integratsiyasi.',
    'features' => ['3D fon', 'Ko‘p til', 'Telegram bot'],
    'live' => 'http://localhost:8000', 'github' => '#', 'accent' => '#818cf8',
  ],
  [
    'id' => 'qarz-pro', 'num' => '05', 'title' => 'QARZ PRO', 'category' => 'APP',
    'type' => 'Biznes qarz tizimi', 'tech' => ['PHP', 'MySQL'],
    'overview' => 'Mijoz qarzlarini hisoblovchi tizim.',
    'challenge' => 'Excel o‘rniga ishonchli panel.',
    'solution' => 'CRUD, hisobot va foydalanuvchi rollari.',
    'features' => ['Mijoz reestri', 'Hisobot', 'Eslatma'],
    'live' => '#', 'github' => '#', 'accent' => '#3b82f6',
  ],
  [
    'id' => 'tg-bot', 'num' => '06', 'title' => 'TELEGRAM BOT', 'category' => 'APP',
    'type' => 'Buyurtma va maslahat boti', 'tech' => ['PHP', 'Telegram API'],
    'overview' => 'WEBmood buyurtma va AI maslahat boti.',
    'challenge' => 'Sayt leadlarini botga ulash.',
    'solution' => 'Savol-javob oqimi + admin xabarlar.',
    'features' => ['Buyurtma', 'Maslahat', 'Statistika'],
    'live' => '#', 'github' => '#', 'accent' => '#22d3ee',
  ],
];

$JOURNEY = [
  ['year' => '2023', 'title' => 'Started Web Development', 'text' => 'Foundations in HTML, CSS, JavaScript and visual craft.'],
  ['year' => '2024', 'title' => 'Freelance Projects', 'text' => 'Shipped client work across business sites and product UIs.'],
  ['year' => '2025', 'title' => 'Advanced Full-Stack Development', 'text' => 'APIs, databases, auth and production architecture.'],
  ['year' => '2026', 'title' => 'NEXDEV', 'text' => 'Personal brand focused on code, design and 3D web experiences.'],
];

$GITHUB = ['repos' => 12, 'commits' => 420, 'projects' => 6, 'technologies' => 18];
