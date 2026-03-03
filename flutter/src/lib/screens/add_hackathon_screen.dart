import 'dart:io';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';
import 'home_screen.dart';

class AddHackathonScreen extends StatefulWidget {
  const AddHackathonScreen({super.key});

  @override
  _AddHackathonScreenState createState() => _AddHackathonScreenState();
}

class _AddHackathonScreenState extends State<AddHackathonScreen> {
  final _nomController = TextEditingController();
  final _descController = TextEditingController();
  final _villeController = TextEditingController();
  final _prixController = TextEditingController();
  final _emailController = TextEditingController();

  double? _lat;
  double? _lng;
  XFile? _image;

  bool _isLocating = false;
  bool _isSubmitting = false;

  Future<void> _getPosition() async {
    setState(() => _isLocating = true);

    try {
      LocationPermission permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return;

      Position position = await Geolocator.getCurrentPosition();
      setState(() {
        _lat = position.latitude;
        _lng = position.longitude;
      });
    } finally {
      setState(() => _isLocating = false);
    }
  }

  Future<void> _takePhoto() async {
    final ImagePicker picker = ImagePicker();
    final XFile? photo = await picker.pickImage(source: ImageSource.camera);
    if (photo != null) {
      setState(() => _image = photo);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Nouvel Évènement")),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Column(
                children: [
                  if (_image != null)
                    Container(
                      margin: const EdgeInsets.only(bottom: 16),
                      height: 100,
                      width: 100,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey.shade300),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.file(File(_image!.path), fit: BoxFit.cover),
                      ),
                    ),
                  ElevatedButton.icon(
                    onPressed: _takePhoto,
                    icon: const Icon(Icons.camera_alt, size: 20),
                    label: const Text("Prendre une photo"),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            TextField(controller: _nomController, decoration: const InputDecoration(labelText: "Nom du hackathon")),
            const SizedBox(height: 12),
            TextField(controller: _descController, decoration: const InputDecoration(labelText: "Description"), maxLines: 3),
            const SizedBox(height: 12),
            TextField(controller: _villeController, decoration: const InputDecoration(labelText: "Ville")),
            const SizedBox(height: 12),
            TextField(controller: _emailController, decoration: const InputDecoration(labelText: "Email de l'organisateur")),
            const SizedBox(height: 12),
            TextField(controller: _prixController, decoration: const InputDecoration(labelText: "Prix"), keyboardType: TextInputType.number),

            const SizedBox(height: 32),

            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  ElevatedButton.icon(
                    onPressed: _isLocating ? null : _getPosition,
                    icon: _isLocating
                        ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                        : const Icon(Icons.location_on),
                    label: Text(_isLocating ? "Recherche..." : "GPS"),
                  ),
                  Expanded(
                    child: Text(
                      _lat != null ? "✓ Coordonnées OK" : "Position manquante",
                      textAlign: TextAlign.right,
                      style: TextStyle(
                          color: _lat != null ? Colors.green : Colors.red,
                          fontWeight: FontWeight.w500
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 40),

            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2D3436),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 18)
                ),
                onPressed: _isSubmitting ? null : () async {
                  if (sessionToken == null) return;

                  if (_lat == null || _lng == null) {
                    ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text("Veuillez récupérer la position GPS d'abord"))
                    );
                    return;
                  }

                  setState(() => _isSubmitting = true);

                  try {
                    String? base64Image;
                    if (_image != null) {
                      final bytes = await File(_image!.path).readAsBytes();
                      final extension = _image!.path.split('.').last.toLowerCase();
                      final mimeType = extension == 'png' ? 'png' : 'jpeg';
                      base64Image = "data:image/$mimeType;base64,${base64Encode(bytes)}";
                    }

                    final Map<String, dynamic> data = {
                      'nom': _nomController.text,
                      'description': _descController.text,
                      'date_event': DateTime.now().toIso8601String(),
                      'prix': double.tryParse(_prixController.text) ?? 0,
                      'latitude': _lat,
                      'longitude': _lng,
                      'ville': _villeController.text,
                      'email_organisateur': _emailController.text,
                      'photo_base64': base64Image,
                    };

                    bool success = await ApiService().createHackathon(data, sessionToken!);

                    if (!mounted) return;

                    if (success) {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text("Hackathon créé avec succès !"))
                      );
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text("Erreur lors de la création"))
                      );
                    }
                  } finally {
                    if (mounted) {
                      setState(() => _isSubmitting = false);
                    }
                  }
                },
                child: _isSubmitting
                    ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)
                )
                    : const Text("Créer le Hackathon", style: TextStyle(fontSize: 16)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}