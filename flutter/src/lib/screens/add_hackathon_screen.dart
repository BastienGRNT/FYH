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

  _getPosition() async {
    LocationPermission permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied) return;

    Position position = await Geolocator.getCurrentPosition();
    setState(() {
      _lat = position.latitude;
      _lng = position.longitude;
    });
  }

  _takePhoto() async {
    final ImagePicker picker = ImagePicker();
    final XFile? photo = await picker.pickImage(source: ImageSource.camera);
    setState(() => _image = photo);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Nouvel Évènement")),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            if (_image != null)
              Container(
                margin: const EdgeInsets.only(bottom: 20),
                height: 200,
                width: double.infinity,
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.file(File(_image!.path), fit: BoxFit.cover),
                ),
              ),

            TextField(controller: _nomController, decoration: const InputDecoration(labelText: "Nom du hackathon")),
            TextField(controller: _descController, decoration: const InputDecoration(labelText: "Description"), maxLines: 3),
            TextField(controller: _villeController, decoration: const InputDecoration(labelText: "Ville")),
            TextField(controller: _emailController, decoration: const InputDecoration(labelText: "Email de l'organisateur")),
            TextField(controller: _prixController, decoration: const InputDecoration(labelText: "Prix"), keyboardType: TextInputType.number),

            const SizedBox(height: 20),
            Row(
              children: [
                ElevatedButton.icon(
                    onPressed: _getPosition,
                    icon: const Icon(Icons.location_on),
                    label: const Text("GPS")
                ),
                const SizedBox(width: 10),
                Text(_lat != null ? "Coordonnées OK" : "Position manquante",
                    style: TextStyle(color: _lat != null ? Colors.green : Colors.red)),
              ],
            ),

            const SizedBox(height: 10),
            ElevatedButton.icon(
                onPressed: _takePhoto,
                icon: const Icon(Icons.camera_alt),
                label: const Text("Prendre une photo")
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
                onPressed: () async {
                  if (sessionToken == null) return;

                  if (_lat == null || _lng == null) {
                    ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text("Veuillez récupérer la position GPS d'abord"))
                    );
                    return;
                  }

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
                },
                child: const Text("Créer le Hackathon"),
              ),
            ),
          ],
        ),
      ),
    );
  }
}