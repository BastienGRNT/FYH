import 'package:flutter/material.dart';
import '../models/hackathon.dart';
import '../services/api_service.dart';
import 'details_screen.dart';
import 'login_screen.dart';
import 'add_hackathon_screen.dart';

// Variable globale pour stocker le token après connexion
String? sessionToken;

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  _HomeScreenState createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final ApiService _apiService = ApiService();
  final ScrollController _scrollController = ScrollController();
  final List<Hackathon> _hackathons = [];
  int _page = 1;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _fetchData();
    _scrollController.addListener(() {
      if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent * 0.8 && !_isLoading) {
        _fetchData();
      }
    });
  }

  Future<void> _fetchData() async {
    if (_isLoading) return;
    setState(() => _isLoading = true);
    try {
      final newData = await _apiService.getHackathons(_page, 5);
      setState(() {
        _hackathons.addAll(newData);
        _page++;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  // Fonction pour gérer la déconnexion
  void _logout() {
    setState(() {
      sessionToken = null;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Déconnexion réussie')),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Exploration'),
        actions: [
          // Bouton dynamique Login ou Logout
          if (sessionToken == null)
            TextButton.icon(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => LoginScreen()),
              ).then((_) => setState(() {})), // Rafraîchit l'UI au retour du login
              icon: const Icon(Icons.login),
              label: const Text('Se connecter'),
              style: TextButton.styleFrom(foregroundColor: const Color(0xFF2D3436)),
            )
          else
            IconButton(
              onPressed: _logout,
              icon: const Icon(Icons.logout),
              tooltip: 'Se déconnecter',
              color: const Color(0xFFD63031), // Couleur rouge pour le logout
            ),
        ],
      ),
      body: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        controller: _scrollController,
        itemCount: _hackathons.length + (_isLoading ? 1 : 0),
        separatorBuilder: (context, index) => const SizedBox(height: 16),
        itemBuilder: (context, index) {
          if (index == _hackathons.length) {
            return const Padding(
              padding: EdgeInsets.symmetric(vertical: 20),
              child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
            );
          }

          final h = _hackathons[index];
          return GestureDetector(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => DetailsScreen(hackathon: h)),
            ),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                    child: h.photoUrl != null
                        ? Image.network(
                      h.photoUrl!,
                      height: 160,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => Container(
                        height: 160,
                        color: const Color(0xFFDFE6E9),
                        child: const Icon(Icons.broken_image, color: Colors.grey),
                      ),
                    )
                        : Container(height: 160, color: const Color(0xFFDFE6E9)),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(h.nom, style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.location_on_outlined, size: 14, color: Color(0xFFB2BEC3)),
                            const SizedBox(width: 4),
                            Text(h.ville, style: const TextStyle(color: Color(0xFFB2BEC3))),
                            const Spacer(),
                            Text(
                              h.prix > 0 ? '${h.prix.toStringAsFixed(0)} €' : 'Gratuit',
                              style: const TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF2D3436)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
      floatingActionButton: sessionToken != null
          ? FloatingActionButton(
        backgroundColor: const Color(0xFF2D3436),
        onPressed: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => AddHackathonScreen()),
        ),
        child: const Icon(Icons.add, color: Colors.white),
      )
          : null,
    );
  }
}