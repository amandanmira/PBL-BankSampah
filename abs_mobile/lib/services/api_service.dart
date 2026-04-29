import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiException implements Exception {
  final String message;
  ApiException(this.message);
  @override
  String toString() => 'ApiException: $message';
}

class ApiService {
  /// Default baseUrl — ganti sesuai lingkungan kamu.
  /// Untuk Android emulator gunakan `http://10.0.2.2:8000/api`.
  final String baseUrl;

  ApiService({String? baseUrl})
    : baseUrl = baseUrl ?? 'http://192.168.1.33:8000/api';

  /// Login — mengembalikan response JSON jika berhasil.
  Future<Map<String, dynamic>> login(String email, String password) async {
    final url = Uri.parse('$baseUrl/login');

    try {
      final response = await http
          .post(
            url,
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: jsonEncode({'email': email, 'password': password}),
          )
          .timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        return data;
      } else if (response.statusCode == 401) {
        throw ApiException('Email atau password salah (401)');
      } else if (response.statusCode == 403) {
        final data = jsonDecode(response.body);
        throw ApiException(data['message'] ?? 'Akses ditolak (403)');
      } else {
        throw ApiException('Server error: ${response.statusCode}');
      }
    } on SocketException catch (e) {
      throw ApiException(
        'Tidak dapat terhubung ke server (${e.message}). Pastikan server berjalan, IP/port benar, server mendengarkan pada 0.0.0.0, dan perangkat terhubung ke jaringan yang sama.',
      );
    } on http.ClientException catch (e) {
      throw ApiException('Client error: ${e.message}');
    } on TimeoutException {
      throw ApiException('Permintaan time out. Periksa koneksi jaringan.');
    } catch (e) {
      throw ApiException('Kesalahan tak terduga: $e');
    }
  }

  // --- FUNGSI BARU UNTUK MENGAMBIL DATA DASHBOARD ---

  // 1. Ambil Penjemputan (Pending/Proses)
  Future<List<dynamic>> getPenjemputan(String token) async {
    final url = Uri.parse('$baseUrl/petugas/penjemputan');
    try {
      final response = await http.get(
        url,
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      if (response.statusCode == 200)
        return jsonDecode(response.body)['data'] ?? [];
    } catch (e) {
      debugPrint('Error getPenjemputan: $e');
    }
    return [];
  }

  // 2. Ambil Riwayat Penjemputan
  Future<List<dynamic>> getRiwayatPenjemputan(String token) async {
    final url = Uri.parse('$baseUrl/petugas/riwayat-penjemputan');
    try {
      final response = await http.get(
        url,
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      if (response.statusCode == 200)
        return jsonDecode(response.body)['data'] ?? [];
    } catch (e) {
      debugPrint('Error getRiwayatPenjemputan: $e');
    }
    return [];
  }

  // 3. Ambil Riwayat Penarikan
  Future<List<dynamic>> getRiwayatPenarikan(String token) async {
    final url = Uri.parse('$baseUrl/petugas/riwayat-penarikan');
    try {
      final response = await http.get(
        url,
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      if (response.statusCode == 200)
        return jsonDecode(response.body)['data'] ?? [];
    } catch (e) {
      debugPrint('Error getRiwayatPenarikan: $e');
    }
    return [];
  }
}
