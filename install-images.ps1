# Скачивает картинки товаров и брендов прямо из репозитория на GitHub.
# Запускать из корня проекта:  .\install-images.ps1
# Если PowerShell не даёт запустить скрипт, выполни один раз:
#   Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass

$base = "https://raw.githubusercontent.com/strashilka2006/pharmacy-ai-assistant/main/public/uploads"

New-Item -ItemType Directory -Force -Path "storage\app\public\products" | Out-Null
New-Item -ItemType Directory -Force -Path "storage\app\public\brands"   | Out-Null
New-Item -ItemType Directory -Force -Path "public\images"                 | Out-Null

Write-Host "Картинки товаров..." -ForegroundColor Cyan
$products = @(
    "1765814670_56b1224bb6f2e72b.jpg",
    "1766085923_b5da451beec95ea8.png",
    "1766179167_6945c15f3ad36.png",
    "1766179434_1766179167_6945c15f3ad36.png",
    "1766180148_6945c5348f2ee.png",
    "1777289807_82a6f605937b730b.png",
    "1777371371_5uwezceyd1umckzl9ns0we60hf38i5f4.png",
    "1777371379_5uwezceyd1umckzl9ns0we60hf38i5f4.png",
    "1777373207_69f0901759ed3.webp",
    "1777380636_0ac5273df9404313.webp",
    "1777393341_69f0debde757f.webp",
    "1777394614_69f0e3b6cd5a1.webp",
    "1777397338_69f0ee5a1acea.webp",
    "1777458180_1e2625fc5a25b1cc.png",
    "1777461804_69f1ea2c51349.webp",
    "1778093042_4aa8c21e3feb5fd2.png",
    "evalar.png",
    "her1o.jpg",
    "hero.jpg",
    "hero.png",
    "larocheposay.jpg",
    "male-multiple-copy.png.webp",
    "no-photo.jpg",
    "solgar.png"
)
foreach ($f in $products) {
    curl.exe -sL -o "storage\app\public\products\$f" "$base/$f"
}

Write-Host "Логотипы и баннеры брендов..." -ForegroundColor Cyan
$brands = @(
    "1764763212_6930264cc660c.jpg",
    "1764763663_6930280f20c73.jpg",
    "1764763688_693028282a830.jpg",
    "1764763950_6930292e5194d.jpg",
    "1764764001_6930296172b61.jpg",
    "1764764040_693029880a362.jpg",
    "1764764148_693029f436e15.jpg",
    "1764764158_693029fe721b6.jpg",
    "1764764649_69302be94a764.jpg",
    "1764764738_69302c429c55d.jpg",
    "1764764771_69302c63f0a4c.jpg",
    "1766087585_69445ba1be405.png",
    "1777372621_69f08dcd828ff.png",
    "1777461281_69f1e82157bb3.png",
    "banner_1777459120_69f1dfb039b39.webp",
    "banner_1777459311_69f1e06f8443a.webp",
    "banner_1777461281_69f1e82157d23.png",
    "larocheposay.jpg"
)
foreach ($f in $brands) {
    curl.exe -sL -o "storage\app\public\brands\$f" "$base/brands/$f"
}

Write-Host "Картинки главной страницы..." -ForegroundColor Cyan
curl.exe -sL -o "public\images\hero.jpg"      "$base/hero.jpg"
curl.exe -sL -o "public\images\no-photo.jpg"  "$base/no-photo.jpg"
curl.exe -sL -o "public\images\no-avatar.jpg" "$base/no-photo.jpg"

$n = (Get-ChildItem "storage\app\public\products").Count + (Get-ChildItem "storage\app\public\brands").Count
Write-Host "Готово. Файлов скачано: $n" -ForegroundColor Green
