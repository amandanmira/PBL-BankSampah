# PBL-BankSampah

Yg mau pakai docker

1. clone dulu
2. pindah sampai directory ABS_Backend
```
cd PBL-BankSampah
```
3. pindah sampai directory ABS_Backend
```
cd ABS_Backend
```
5. Install Composer
```
composer install
```
7. nyalakan docker / buka app docker desktop
8. docker compose up -d --build
9. buat .env trus copy .env.example

10. copy .env.example .env

11. docker exec -it ABS_app bash
12. php artisan key:generate
13. php artisan migrate

yg mau pakai laragon

1. clone dulu
2. cd PBL-BankSampah
3. cd ABS_Backend
4. composer install
5. php artisan key:generate
6. buat .env trus copy .env.example
7.
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ABS
DB_USERNAME=root
DB_PASSWORD=
9. php artisan migrate
