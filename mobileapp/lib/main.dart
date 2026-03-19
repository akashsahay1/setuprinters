import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:setu_printers/manager/global_singleton.dart';
import 'package:setu_printers/modules/setu_printers.dart';
import 'package:setu_printers/network/network_dio.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await NetworkDio.setDynamicHeader();
  await GlobalSingleton.loadUser();
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
  ]);
  runApp(const SetuPrintersApp());
}
