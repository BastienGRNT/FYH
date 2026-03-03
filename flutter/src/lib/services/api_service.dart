import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/hackathon.dart';

class ApiService {
  static const String baseUrl = "https://fyh.bastiengrnt.fr/api";

  Future<List<Hackathon>> getHackathons(int page, int limit) async {
    final response = await http.get(
      Uri.parse('$baseUrl/hackathons?page=$page&limit=$limit'),
    );

    if (response.statusCode == 200) {
      List jsonResponse = json.decode(response.body);
      return jsonResponse.map((data) => Hackathon.fromJson(data)).toList();
    } else {
      throw Exception('Erreur de chargement des données');
    }
  }

  Future<String> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['token'];
    } else {
      throw Exception('Identifiants incorrects');
    }
  }

  Future<bool> createHackathon(Map<String, dynamic> data, String token) async {
    final response = await http.post(
      Uri.parse('$baseUrl/hackathons'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json', // Ajoute ceci pour être sûr
        'Authorization': 'Bearer ${token.trim()}', // trim() pour éviter les espaces invisibles
      },
      body: jsonEncode(data),
    );

    print(token);
    print("Status: ${response.statusCode}");
    print("Body: ${response.body}");

    return response.statusCode == 201;
  }
}