import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiService {
  // Pastikan IP ini sesuai dengan IP laptop/localhost kamu
  static const String baseUrl = 'http://192.168.1.11:8000/api'; 

  Future<Map<String, dynamic>?> login(String email, String password) async {
    final url = Uri.parse('$baseUrl/login');

    try {
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200) {
        debugPrint('✅ Login Berhasil!');
        return data;
      } else {
        debugPrint('❌ Gagal: ${data['message']}');
        return data; 
      }
    } catch (e) {
      debugPrint('❌ Terjadi kesalahan jaringan: $e');
      return null;
    }
  }
}