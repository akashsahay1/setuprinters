import 'dart:developer';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:just_audio/just_audio.dart';
import 'package:qr_code_scanner_plus/qr_code_scanner_plus.dart';
import 'package:setu_printers/config/app_assets.dart';
import 'package:setu_printers/config/app_config.dart';
import 'package:setu_printers/config/theme/app_colors.dart';
import 'package:setu_printers/config/theme/app_text_style.dart';
import 'package:setu_printers/manager/global_singleton.dart';
import 'package:setu_printers/modules/apply_leave.dart';
import 'package:setu_printers/modules/auth/auth_screen.dart';
import 'package:setu_printers/modules/selfie/camera_screen.dart';
import 'package:setu_printers/network/network_dio.dart';
import 'package:camera/camera.dart';
import 'package:dio/dio.dart' as dio;
import 'package:image/image.dart' as img;

late List<CameraDescription> cameras;

class SelfieScreen extends StatefulWidget {
  const SelfieScreen({super.key});

  @override
  State<SelfieScreen> createState() => _SelfieScreenState();
}

class _SelfieScreenState extends State<SelfieScreen> {
  File? _image;

  final GlobalKey qrKey = GlobalKey(debugLabel: 'QR');
  QRViewController? _qrViewController;
  String? scannedData;
  final player = AudioPlayer();
  Uint8List? result;
  bool _isOnLeave = false;
  bool _isUploading = false;

  @override
  void initState() {
    super.initState();
    _checkLeaveToday();
  }

  Future<void> _checkLeaveToday() async {
    final staffId = GlobalSingleton.selectedUser?.userId;
    if (staffId == null) return;

    final response = await NetworkDio.getData(
      url: '${ApiPath.baseUrl}${ApiPath.checkLeaveToday}?staff_id=$staffId',
    );

    if (!mounted) return;

    if (response != null && response['on_leave'] == true) {
      setState(() {
        _isOnLeave = true;
      });
    }
  }

  @override
  void dispose() {
    player.dispose();
    super.dispose();
  }

  void _onQRViewCreated(QRViewController controller) {
    _qrViewController = controller;
    log("_qrViewController assign");
    _qrViewController!.resumeCamera();
    setState(() {});
    controller.scannedDataStream.listen((scanData) async {
      scannedData = scanData.code ?? "No data found";
      if (scannedData != null) {
        _qrViewController!.pauseCamera();
        await player.setAsset('assets/beep.mp3');
        await player.play();
      }
      if (mounted) setState(() {});
    });
  }

  static Uint8List _resizeInIsolate(Uint8List bytes) {
    img.Image? original = img.decodeImage(bytes);
    if (original == null) throw Exception('Failed to decode image.');
    img.Image resized = img.copyResize(original, width: 300);
    return Uint8List.fromList(img.encodeJpg(resized));
  }

  Future<File> resizeImage(File file) async {
    final bytes = await file.readAsBytes();
    final resizedBytes = await compute(_resizeInIsolate, bytes);
    return await file.writeAsBytes(resizedBytes);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        appBar: AppBar(),
        body: SafeArea(
          child: SizedBox(
            width: Get.width,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                if (_isOnLeave) ...[
                  const Spacer(),
                  Icon(
                    Icons.event_busy,
                    size: 80,
                    color: AppColors.appColors,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    "You are on approved leave today",
                    style: AppTextStyle.semiBold20,
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    "Attendance marking is not required",
                    style: AppTextStyle.regular16.copyWith(
                      color: AppColors.textGreyColor,
                    ),
                  ),
                  const Spacer(),
                ] else ...[
                  Text(
                    "Upload a Photo",
                    style: AppTextStyle.black20,
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  _image == null
                      ? Image.asset(
                          AppAssets.imagePicker,
                          height: 170,
                          width: 170,
                          fit: BoxFit.fill,
                        )
                      : ClipRRect(
                          borderRadius: BorderRadius.circular(999),
                          child: Image.file(
                            _image!,
                            height: 170,
                            width: 170,
                            fit: BoxFit.cover,
                          ),
                        ),
                  SizedBox(
                    height: 10,
                  ),
                  if (_image == null)
                    InkWell(
                      onTap: () async {
                        cameras = await availableCameras();
                        final imagePath = await Get.to(() => CameraScreen(
                            camera: cameras.reversed.toList().first));
                        log("----->>$imagePath");
                        Future.delayed(Duration(seconds: 1), () async {
                          if (imagePath != null) {
                            final croppedFile =
                                await resizeImage(File(imagePath));

                            setState(() {
                              _image = croppedFile;
                            });
                          }
                        });
                      },
                      child: Container(
                        margin: EdgeInsets.symmetric(vertical: 20),
                        padding:
                            EdgeInsets.symmetric(horizontal: 100, vertical: 12),
                        decoration: BoxDecoration(
                          color: AppColors.blueCOlor,
                          borderRadius: BorderRadius.circular(30),
                        ),
                        child: Text(
                          "Take Selfie",
                          style: AppTextStyle.semiBold20
                              .copyWith(color: AppColors.whiteColor),
                        ),
                      ),
                    ),
                  SizedBox(
                    height: 10,
                  ),
                  if (_image != null)
                    Text(
                      "Scan Your QR Code",
                      style: AppTextStyle.black20,
                    ),
                  if (_image != null)
                    Expanded(
                      child: Container(
                      width: Get.width,
                      padding: EdgeInsets.all(20),
                      child: QRView(
                        key: qrKey,
                        onQRViewCreated: _onQRViewCreated,
                        overlay: QrScannerOverlayShape(
                          borderColor: Colors.blue,
                          borderRadius: 10,
                          borderLength: 30,
                          borderWidth: 10,
                          cutOutSize: 250,
                        ),
                      ),
                    ),
                    ),
                  if (_image != null && scannedData != null)
                    InkWell(
                      onTap: _isUploading ? null : () {
                        if (scannedData != null) {
                          uploadSelfie();
                        } else {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text("Scan QR Code Again!"),
                            ),
                          );
                        }
                      },
                      child: Container(
                        margin: EdgeInsets.symmetric(vertical: 10),
                        padding:
                            EdgeInsets.symmetric(horizontal: 100, vertical: 12),
                        decoration: BoxDecoration(
                          color: AppColors.blueCOlor,
                          borderRadius: BorderRadius.circular(30),
                        ),
                        child: Text(
                          "Upload",
                          style: AppTextStyle.semiBold20
                              .copyWith(color: AppColors.whiteColor),
                        ),
                      ),
                    ),
                  SizedBox(
                    height: 10,
                  ),
                ],
              ],
            ),
          ),
        ),
        bottomNavigationBar: SizedBox(
          height: 70,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              Column(
                children: [
                  Icon(Icons.emoji_emotions_outlined),
                  Text("Clear Face")
                ],
              ),
              Column(
                children: [
                  Image.asset(
                    AppAssets.glass,
                    height: 20,
                    width: 20,
                  ),
                  Text("No Sunglasses")
                ],
              ),
              Column(
                children: [Icon(Icons.group), Text("No Group")],
              ),
              Column(
                children: [
                  InkWell(
                    child: Column(
                      children: [
                        Icon(Icons.check_circle_outline),
                        Text("Apply Leave")
                      ],
                    ),
                    onTap: () {
                      Get.off(() => ApplyLeave());
                    },
                  )
                  ],
              ),
            ],
          ),
        ));
  }

  Future<void> uploadSelfie() async {
    if (_isUploading) return;
    setState(() => _isUploading = true);

    // Free up resources by pausing QR camera during upload
    _qrViewController?.pauseCamera();

    final formData = dio.FormData.fromMap(
      {
        'userId': GlobalSingleton.selectedUser!.userId.toString(),
        'barcode': scannedData,
        'selfiePhoto': await dio.MultipartFile.fromFile(
          _image!.path,
          filename: 'selfie.${_image!.path.toLowerCase().split('.').last}',
        ),
      },
    );

    if (!mounted) return;

    Map<String, dynamic>? response = await NetworkDio.postData(
      url: ApiPath.baseUrl + ApiPath.scanCode,
      context: context,
      data: formData,
    );

    log(response.toString(), name: "Upload Response");

    if (response != null && response['status'] == true) {
      _image = null;
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppColors.successColor,
          content: Text("QR upload Successfully"),
        ),
      );
      Get.off(() => AuthScreen());
    } else {
      // Reset so user can retry
      if (mounted) setState(() => _isUploading = false);
    }
  }
}
