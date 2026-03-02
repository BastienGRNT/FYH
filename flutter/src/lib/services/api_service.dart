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
}