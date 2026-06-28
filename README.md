# 🎵 MP3 Stream Title

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-777bb3.svg?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-Apache%202.0-green.svg)
[![Stand with Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg)](https://stand-with-ukraine.pp.ua)
[![Made in Ukraine](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)](https://stand-with-ukraine.pp.ua)

A lightweight PHP library to fetch the **currently playing track** from any online radio stream.

## ✨ Features
- ⚡ Lightweight
- 📦 No dependencies
- 🧩 Easy to use
- 🌐 Optional: PHP cURL support for better stream handling

## ⚡ Requirements
- PHP >= 8.2
- PHP cURL recommended but not required

## 📖 Usage
```php
<?php

require_once 'Mp3StreamTitle/src/Mp3StreamTitle.php';

use Mp3StreamTitle\Mp3StreamTitle;

$client = new Mp3StreamTitle();

try {
    // Replace with a direct radio stream link
    echo $client->fetchStreamTitle('http://example.com');
} catch (Throwable $e) {
    echo $e->getMessage();
}
```

## 👨‍💻 Author
- [Oleh Kovalenko](https://github.com/oleh-exe) — Owner & Maintainer

## 📜 License
[Apache 2.0](LICENSE)  