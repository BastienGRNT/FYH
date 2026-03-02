class Hackathon {
  final int id;
  final String nom;
  final String description;
  final DateTime dateEvent;
  final double prix;
  final String ville;
  final String? photoUrl;

  Hackathon({
    required this.id,
    required this.nom,
    required this.description,
    required this.dateEvent,
    required this.prix,
    required this.ville,
    this.photoUrl,
  });

  factory Hackathon.fromJson(Map<String, dynamic> json) {
    return Hackathon(
      id: json['id'],
      nom: json['nom'],
      description: json['description'],
      dateEvent: DateTime.parse(json['date_event']),
      prix: double.parse(json['prix'].toString()),
      ville: json['ville'],
      photoUrl: json['photo_url'],
    );
  }
}