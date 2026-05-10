# PROJECT_MAP.md

# Project Structure Map

fb-comment-bot/

├── app/
│
│ ├── config/
│ │ ├── config.php
│ │ ├── database.php
│ │ └── app.php
│
│ ├── controllers/
│ │ ├── AuthController.php
│ │ ├── DashboardController.php
│ │ ├── CommentController.php
│ │ ├── TargetController.php
│ │ ├── SettingController.php
│ │ ├── LogController.php
│ │ └── BotController.php
│
│ ├── models/
│ │ ├── Admin.php
│ │ ├── Comment.php
│ │ ├── TargetGroup.php
│ │ ├── Post.php
│ │ ├── Log.php
│ │ ├── Setting.php
│ │ ├── TelegramLog.php
│ │ └── BotStatus.php
│
│ ├── services/
│ │ ├── FacebookService.php
│ │ ├── TelegramService.php
│ │ ├── CommentService.php
│ │ ├── LoggerService.php
│ │ ├── CookieService.php
│ │ └── RandomizerService.php
│
│ ├── helpers/
│ │ ├── CurlHelper.php
│ │ ├── AuthHelper.php
│ │ ├── LoggerHelper.php
│ │ ├── ResponseHelper.php
│ │ ├── SessionHelper.php
│ │ └── ValidatorHelper.php
│
│ └── views/
│ ├── layouts/
│ ├── auth/
│ ├── dashboard/
│ ├── comments/
│ ├── targets/
│ ├── settings/
│ ├── logs/
│ └── bot/
│
├── public/
│ ├── assets/
│ │ ├── css/
│ │ ├── js/
│ │ ├── img/
│ │ └── vendor/
│ │
│ ├── uploads/
│ └── index.php
│
├── routes/
│ └── web.php
│
├── cron/
│ ├── run_bot.php
│ ├── fetch_posts.php
│ └── send_comments.php
│
├── storage/
│ ├── logs/
│ ├── cache/
│ └── cookies/
│
├── vendor/
│
├── .env
├── .env.example
├── composer.json
├── database.sql
├── README.md
├── ARCHITECTURE.md
├── AI_CONTRACT.md
└── PROJECT_MAP.md
