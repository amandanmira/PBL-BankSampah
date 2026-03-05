# PBL-BankSampah

Yg mau pakai docker

1. clone dulu
2. cd PBL-BankSampah
3. cd ABS_Backend
4. composer install
5. nyalakan docker / buka app docker desktop
6. docker compose up -d --build
7. docker exec -it ABS_app php artisan key:generate
8. buat .env trus copy .env.example
9. docker exec -it ABS_app php artisan migrate

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
