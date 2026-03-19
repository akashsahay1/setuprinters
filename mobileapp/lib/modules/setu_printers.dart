import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:setu_printers/modules/splash_screen.dart';

class SetuPrintersApp extends StatefulWidget {
  const SetuPrintersApp({super.key});

  @override
  State<SetuPrintersApp> createState() => _SetuPrintersAppState();
}

class _SetuPrintersAppState extends State<SetuPrintersApp> {
  @override
  Widget build(BuildContext context) {
    return GetMaterialApp(
      debugShowCheckedModeBanner: false,
      home: SplashScreen(),
    );
  }
}
