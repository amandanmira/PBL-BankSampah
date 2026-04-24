import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiService {
  // Pastikan IP ini sesuai dengan IP laptop/localhost kamu
  // static const String baseUrl = 'http://192.168.1.11:8000/api'; //untuk hp
  static const String baseUrl = 'http://127.0.0.1:8000/api'; // untuk browser

  Future<Map<String, dynamic>?> login(String email, String password) async {
    final url = Uri.parse('$baseUrl/login');
    try {
      final response = await http.post(
        url,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'email': email, 'password': password}),
      );
      return jsonDecode(response.body);
    } catch (e) {
      debugPrint('❌ Terjadi kesalahan jaringan: $e');
      return null;
    }
  }

  // --- FUNGSI BARU UNTUK MENGAMBIL DATA DASHBOARD ---
  
  // 1. Ambil Penjemputan (Pending/Proses)
  Future<List<dynamic>> getPenjemputan(String token) async {
    final url = Uri.parse('$baseUrl/petugas/penjemputan');
    try {
      final response = await http.get(url, headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'});
      if (response.statusCode == 200) return jsonDecode(response.body)['data'] ?? [];
    } catch (e) { debugPrint('Error getPenjemputan: $e'); }
    return [];
  }

  // 2. Ambil Riwayat Penjemputan
  Future<List<dynamic>> getRiwayatPenjemputan(String token) async {
    final url = Uri.parse('$baseUrl/petugas/riwayat-penjemputan');
    try {
      final response = await http.get(url, headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'});
      if (response.statusCode == 200) return jsonDecode(response.body)['data'] ?? [];
    } catch (e) { debugPrint('Error getRiwayatPenjemputan: $e'); }
    return [];
  }

  // 3. Ambil Riwayat Penarikan
  Future<List<dynamic>> getRiwayatPenarikan(String token) async {
    final url = Uri.parse('$baseUrl/petugas/riwayat-penarikan');
    try {
      final response = await http.get(url, headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'});
      if (response.statusCode == 200) return jsonDecode(response.body)['data'] ?? [];
    } catch (e) { debugPrint('Error getRiwayatPenarikan: $e'); }
    return [];
  }
}