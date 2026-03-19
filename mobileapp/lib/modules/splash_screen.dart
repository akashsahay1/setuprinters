import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:setu_printers/config/app_assets.dart';
import 'package:setu_printers/modules/auth/auth_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    Future.delayed(const Duration(seconds: 3), () {
      Get.to(() => const AuthScreen());
    });
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        body: Center(
      child: InkWell(
        onTap: () {
          Get.to(() => const AuthScreen());
        },
        child: Image.asset(
          AppAssets.logo,
          height: 100,
          width: 100,
          fit: BoxFit.fill,
        ),
      ),
    ));
  }
}
