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

Cara sambungkan frontEnd

1. Pull
2. cd ke ABS_Frontend
3. ketik wsl di terminal
4. ketik
```
docker compose --rm app npm install
```
5. exit dari wsl ketik exit
6. cd .. lalu cd ke ABS_Backend
7. ubah .env lama ke env baru yg ada di .env.example
8. terminal ketik
```
composer intall
```

9. ketik
```
docker exec -it ABS_app php artisan tinker
````
10. ketik ini buat uji coba login sederhana
```
User::create(['name' => 'Admin', 'email' => 'admin@mail.com', 'password' => bcrypt('password123')]);
```
