<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
     */
    'api_path' => 'api',

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * matcher, so when implementing your own, make sure you use this config if needed.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info' => [
        /*
         * API version.
         */
        'version' => env('API_VERSION', '1.0.0'),

        /*
         * Define the title of the documentation's website. 
         * Dipindahkan ke sini agar terbaca oleh standar OpenAPI & Scalar.
         */
        'title' => 'API Bank Sampah (ABS)',

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => "
Selamat datang di Dokumentasi API **Aplikasi Bank Sampah (ABS)**. 🌍♻️

API ini menyediakan berbagai endpoint untuk mengelola sistem Bank Sampah secara terintegrasi, meliputi:
* **Nasabah**: Melakukan request penjemputan sampah & penarikan saldo.
* **Petugas**: Mengonfirmasi penjemputan, memproses penimbangan sampah, & mengelola berita.
* **Pengepul**: Melakukan request pembelian sampah dari gudang.
* **Admin/Manager**: Mengelola master data, akun pengguna, gudang, dan konfigurasi web.

> **Catatan Autentikasi:** Sebagian besar endpoint pada sistem ini membutuhkan `Bearer Token` (Sanctum). Silakan gunakan endpoint `POST /login` terlebih dahulu untuk mendapatkan token Anda, lalu masukkan token tersebut ke tombol **Authorize** di kanan halaman ini.
        ",
    ],

    /*
     * Menggunakan Scalar sebagai UI dokumentasi
     * Scalar jauh lebih baik dalam menangani input multipart/form-data untuk array of files
     */
    'ui' => [
        'title' => 'API Bank Sampah (ABS)',
        'theme' => 'system',
        'hide_try_it' => false,
        'logo' => '',
    ],

    /*
     * The list of servers of the API. By default, when `null`, server URL will be created from
     * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
     * will need to specify the local server URL manually (if needed).
     */
    'servers' => null,

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     * Available options:
     * - 'description' – Case descriptions are stored as the enum schema's description using table formatting.
     * - 'extension' – Case descriptions are stored in the `x-enumDescriptions` enum schema extension.
     *
     * @see https://redocly.com/docs-legacy/api-reference-docs/specification-extensions/x-enum-descriptions
     * - false - Case descriptions are ignored.
     */
    'enum_cases_description_strategy' => 'description',

    /**
     * Determines how Scramble stores the names of enum cases.
     * Available options:
     * - 'names' – Case names are stored in the `x-enumNames` enum schema extension.
     * - 'varnames' - Case names are stored in the `x-enum-varnames` enum schema extension.
     * - false - Case names are not stored.
     */
    'enum_cases_names_strategy' => false,

    /**
     * When Scramble encounters deep objects in query parameters, it flattens the parameters so the generated
     * OpenAPI document correctly describes the API. Flattening deep query parameters is relevant until
     * OpenAPI 3.2 is released and query string structure can be described properly.
     */
    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],
];