import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../models/hackathon.dart';

class DetailsScreen extends StatelessWidget {
  final Hackathon hackathon;

  const DetailsScreen({super.key, required this.hackathon});

  @override
  Widget build(BuildContext context) {
    final LatLng eventLocation = LatLng(
        hackathon.latitude ?? 48.8566,
        hackathon.longitude ?? 2.3522
    );

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
      ),
      extendBodyBehindAppBar: true,
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                hackathon.photoUrl != null && hackathon.photoUrl!.isNotEmpty
                    ? Image.network(
                  hackathon.photoUrl!.startsWith('http')
                      ? hackathon.photoUrl!
                      : 'https://fyh.bastiengrnt.fr${hackathon.photoUrl}',
                  height: 300,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) => Container(
                    height: 300,
                    color: const Color(0xFFDFE6E9),
                    child: const Icon(Icons.broken_image, color: Colors.grey, size: 50),
                  ),
                )
                    : Container(height: 300, color: const Color(0xFFDFE6E9)),
                Container(
                  height: 300,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [Colors.black.withOpacity(0.4), Colors.transparent],
                    ),
                  ),
                ),
              ],
            ),

            Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(hackathon.nom, style: Theme.of(context).textTheme.headlineMedium),
                  const SizedBox(height: 12),

                  Row(
                    children: [
                      _infoChip(Icons.location_on_outlined, hackathon.ville),
                      const SizedBox(width: 16),
                      _infoChip(Icons.calendar_today_outlined,
                          '${hackathon.dateEvent.day}/${hackathon.dateEvent.month}/${hackathon.dateEvent.year}'),
                    ],
                  ),

                  const SizedBox(height: 32),
                  const Text('À propos', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(hackathon.description, style: Theme.of(context).textTheme.bodyMedium),

                  const SizedBox(height: 32),
                  const Text('Localisation', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 16),

                  Container(
                    height: 200,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10)
                      ],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: FlutterMap(
                        options: MapOptions(
                          initialCenter: eventLocation,
                          initialZoom: 13.0,
                        ),
                        children: [
                          TileLayer(
                            urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                            userAgentPackageName: 'fr.bastien.fyh.app',
                          ),
                          MarkerLayer(
                            markers: [
                              Marker(
                                point: eventLocation,
                                width: 40,
                                height: 40,
                                child: const Icon(
                                  Icons.location_pin,
                                  color: Color(0xFFD63031),
                                  size: 40,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoChip(IconData icon, String label) {
    return Row(
      children: [
        Icon(icon, size: 16, color: const Color(0xFF636E72)),
        const SizedBox(width: 6),
        Text(label, style: const TextStyle(color: Color(0xFF636E72), fontSize: 14)),
      ],
    );
  }
}