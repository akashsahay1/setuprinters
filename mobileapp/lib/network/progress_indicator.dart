import 'dart:io';
import 'dart:async';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class Circle {
  factory Circle() => _instance;
  Circle.internal();
  static final Circle _instance = Circle.internal();

  static bool entry = false;
  static OverlayEntry viewEntry = OverlayEntry(builder: (BuildContext context) {
    return const Loader();
  });

  InternetError internetError = InternetError();

  Future<void> show(BuildContext context) async {
    return addOverlayEntry(context);
  }

  void hide(BuildContext context) => removeOverlay();

  bool get isShow => isShowNetworkOrCircle();

  bool isShowNetworkOrCircle() {
    return internetError.isShow || entry == true;
  }

  Future<void> addOverlayEntry(BuildContext context) async {
    if (entry == true) {
      return;
    }
    entry = true;
    return addOverlay(viewEntry, context);
  }

  Future<void> addOverlay(OverlayEntry entry, BuildContext context) async {
    try {
      return Overlay.of(context).insert(entry);
    } catch (e) {
      return Future.error(e);
    }
  }

  Future<void> removeOverlay() async {
    try {
      entry = false;
      viewEntry.remove();
    } catch (e) {
      return Future.error(e);
    }
  }
}

class ProcessIndicator extends StatelessWidget {
  const ProcessIndicator({super.key});

  @override
  Widget build(BuildContext context) {
    return const Material(
      color: Colors.transparent,
      child: Center(
        child: Material(
          color: Colors.transparent,
          child: CircularProgressIndicator(),
        ),
      ),
    );
  }
}

class Loader extends StatefulWidget {
  const Loader({super.key});

  @override
  State<Loader> createState() => _LoaderState();
}

class _LoaderState extends State<Loader> {
  @override
  Widget build(BuildContext context) {
    return const CupertinoActivityIndicator(
      radius: 16,
    );
  }
}

class InternetError {
  factory InternetError() => _instance;
  InternetError.internal();
  static final InternetError _instance = InternetError.internal();

  static OverlayEntry? entry;

  static void show(BuildContext context) => addOverlayEntry(context);
  void hide() => removeOverlay();

  bool get isShow => entry != null;

  static void addOverlayEntry(BuildContext context) {
    if (entry != null) {
      return;
    }
    entry = OverlayEntry(
      builder: (BuildContext buildContext) {
        return LayoutBuilder(
          builder: (_, BoxConstraints constraints) {
            return Material(
              color: Colors.white,
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 30),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: <Widget>[
                      const SizedBox(
                        height: 40,
                      ),
                      const Text(
                        'No Internet Connection',
                        style: TextStyle(
                          fontSize: 24,
                          letterSpacing: 1,
                          fontWeight: FontWeight.w700,
                          color: Colors.lightBlueAccent,
                        ),
                      ),
                      const SizedBox(
                        height: 10,
                      ),
                      const Text(
                        '''You are not connected with internet. make sure your Wi-fi is on, Airplane Mode off and try again.''',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.blueGrey,
                          fontWeight: FontWeight.w400,
                        ),
                      ),
                      const SizedBox(
                        height: 30,
                      ),
                      SizedBox(
                        height: 40,
                        width: 150,
                        child: TextButton(
                          style: TextButton.styleFrom(
                            backgroundColor: Colors.redAccent.shade200,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                          ),
                          onPressed: () async {
                            if (await hasNetwork()) {
                              removeOverlay();
                            }
                          },
                          child: const Text(
                            'Try again',
                            style: TextStyle(
                              fontSize: 16,
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  static Future<bool> hasNetwork() async {
    try {
      final List<InternetAddress> result =
          await InternetAddress.lookup('example.com');
      return result.isNotEmpty && result[0].rawAddress.isNotEmpty;
    } on SocketException catch (_) {
      return false;
    }
  }

  static void removeOverlay() {
    entry = null;
    entry?.remove();
  }
}
