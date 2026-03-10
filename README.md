# PBL-BankSampah

## A. Clone Repoitori

1. clone repositori
```
git clone https://github.com/amandanmira/PBL-BankSampah.git
```
2. Pindah ke direktori `PBL-BankSampah`
```
cd PBL-BankSampah
```

## B. Setup Backend

### - Setup Memakai Docker

1. Pindah ke direktori `ABS_Backend`
```
cd ABS_Backend
```
2. Install dependensi laravel dengan composer
```
composer install
```
3. Nyalakan docker / buka app docker desktop
4. Build container docker
```
docker compose up -d --build
```
5. Buat file `.env` lalu copy isi file `.env.example` ke `.env`
```
cp .env.example .env
```
6. Buka bash container laravel
```
docker exec -it ABS_app bash
```
7. Generate key laravel
```
php artisan key:generate
```
8. Migrate database
```
php artisan migrate
```

### - Setup Memakai Laragon

1. Pindah ke direktori `ABS_Backend`
```
cd ABS_Backend
```
2. Install dependensi laravel dengan composer
```
composer install
```
3. Buat file `.env` lalu copy isi file `.env.example` ke `.env`
```
cp .env.example .env
```
4. Generate key laravel
```
php artisan key:generate
```
5. Migrate database
```
php artisan migrate
```

## C. Setup Frontend

1. Pindah ke direktori `ABS_Frontend`
```
cd ABS_Frontend
```
2. Ketik `wsl` di terminal
3. Ketik
```
docker compose --rm app npm install
```
4. Exit dari wsl ketik `exit`
5. Ketik `cd ../ABS_Backend`
6. Ubah `.env` lama ke env baru yg ada di `.env.example`
7. Terminal ketik
```
composer intall
```
8. Ketik
```
docker exec -it ABS_app php artisan tinker
````
9. Ketik ini buat uji coba login sederhana
```
User::create(['name' => 'Admin', 'email' => 'admin@mail.com', 'password' => bcrypt('password123')]);
```
10. Di chrome ketik `localhost:5173`
