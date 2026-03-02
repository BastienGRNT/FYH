import 'package:flutter/material.dart';
import '../models/hackathon.dart';

class DetailsScreen extends StatelessWidget {
  final Hackathon hackathon;

  const DetailsScreen({super.key, required this.hackathon});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
      ),
      extendBodyBehindAppBar: true,
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                hackathon.photoUrl != null
                    ? Image.network(hackathon.photoUrl!, height: 350, width: double.infinity, fit: BoxFit.cover)
                    : Container(height: 350, color: const Color(0xFFDFE6E9)),
                Container(
                  height: 350,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [Colors.black.withOpacity(0.3), Colors.transparent],
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
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
                  const SizedBox(height: 12),
                  Text(hackathon.description, style: Theme.of(context).textTheme.bodyMedium),
                  const SizedBox(height: 40),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {},
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF2D3436),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 18),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      child: const Text('Participer', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
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