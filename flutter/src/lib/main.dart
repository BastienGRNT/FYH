import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'screens/home_screen.dart';

void main() {
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
    statusBarColor: Colors.transparent,
    statusBarIconBrightness: Brightness.dark,
  ));
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Find Your Hackathon',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF2D3436),
          primary: const Color(0xFF2D3436),
          surface: Colors.white,
        ),
        scaffoldBackgroundColor: const Color(0xFFF8F9FA), // Fond très clair
        textTheme: const TextTheme(
          headlineMedium: TextStyle(color: Color(0xFF2D3436), fontWeight: FontWeight.w700),
          titleLarge: TextStyle(color: Color(0xFF2D3436), fontWeight: FontWeight.w600),
          bodyMedium: TextStyle(color: Color(0xFF636E72), height: 1.5),
        ),
        appBarTheme: const AppBarTheme(
          elevation: 0,
          backgroundColor: Colors.white,
          foregroundColor: Color(0xFF2D3436),
          centerTitle: false,
        ),
      ),
      home: const HomeScreen(),
    );
  }
}