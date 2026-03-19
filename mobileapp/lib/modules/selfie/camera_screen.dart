import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:path/path.dart' as path;
import 'package:path_provider/path_provider.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:setu_printers/config/theme/app_colors.dart';

// class CameraScreen extends StatefulWidget {
//   String? path;
//   CameraScreen({super.key, this.path});

//   @override
//   State<CameraScreen> createState() => _CameraScreenState();
// }

// class _CameraScreenState extends State<CameraScreen> {
//   CameraController? _controller;
//   late List<CameraDescription> _cameras;
//   bool _isCameraInitialized = false;

//   @override
//   void initState() {
//     super.initState();
//     initCamera();
//   }

//   Future<void> initCamera() async {
//     _cameras = await availableCameras();
//     // Use front camera

//     final frontCamera = _cameras.firstWhere(
//       (camera) => camera.lensDirection == CameraLensDirection.front,
//     );
//     _controller = CameraController(frontCamera, ResolutionPreset.high);
//     await _controller!.initialize();
//     if (mounted) setState(() => _isCameraInitialized = true);
//   }

//   @override
//   void dispose() {
//     _controller?.dispose();
//     super.dispose();
//   }

//   Future<void> _takePictureAndUpload() async {
//     if (_controller == null) return;
//     if (!_controller!.value.isInitialized) return;

//     final XFile picture = await _controller!.takePicture();

//     print(picture.path);
//     widget.path = picture.path;

//     Get.back();
//   }

//   @override
//   Widget build(BuildContext context) {
//     return _isCameraInitialized
//         ? Stack(
//             children: [
//               if (_controller != null) CameraPreview(_controller!),
//               Positioned(
//                 bottom: 50,
//                 left: 0,
//                 right: 0,
//                 child: InkWell(
//                   onTap: () {
//                     _takePictureAndUpload();
//                   },
//                   child: Container(
//                     height: 60,
//                     width: 60,
//                     decoration: BoxDecoration(
//                         shape: BoxShape.circle, color: AppColors.greyColor),
//                   ),
//                 ),
//               )
//             ],
//           )
//         : Center(child: CircularProgressIndicator());
//   }
// }

class CameraScreen extends StatefulWidget {
  final CameraDescription camera;

  const CameraScreen({super.key, required this.camera});

  @override
  State<CameraScreen> createState() => _CameraScreenState();
}

class _CameraScreenState extends State<CameraScreen> {
  late CameraController _controller;
  late Future<void> _initializeControllerFuture;

  @override
  void initState() {
    super.initState();
    _controller = CameraController(
      widget.camera,
      ResolutionPreset.medium,
      enableAudio: false,
    );
    _initializeControllerFuture = _initCamera();
  }

  Future<void> _initCamera() async {
    final status = await Permission.camera.request();
    if (!status.isGranted) {
      throw Exception('Camera permission denied');
    }
    await _controller.initialize();
  }

  @override
  void dispose() {
    _controller.dispose();
    debugPrint("controller is dispose");
    super.dispose();
  }

  Future<void> _takePicture() async {
    try {
      await _initializeControllerFuture;

      final image = await _controller.takePicture();

      final directory = await getApplicationDocumentsDirectory();
      final imagePath = path.join(directory.path, '${DateTime.now()}.jpg');

      await image.saveTo(imagePath);

      if (mounted) {
        Navigator.pop(context, image.path);
        // ScaffoldMessenger.of(context).showSnackBar(
        //   SnackBar(content: Text('Saved to $imagePath')),
        // );
      }
    } catch (e) {
      debugPrint('Error taking picture: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        appBar: AppBar(title: const Text('Take Selfie')),
        body: FutureBuilder<void>(
          future: _initializeControllerFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.done) {
              if (snapshot.hasError) {
                return Center(
                  child: Text(
                    'Camera permission denied.\nPlease enable it in app settings.',
                    textAlign: TextAlign.center,
                  ),
                );
              }
              return SizedBox.expand(
                  child: FittedBox(
                    fit: BoxFit.cover,
                    child: SizedBox(
                      width: _controller.value.previewSize!.height,
                      height: _controller.value.previewSize!.width,
                      child: Transform.flip(
                        flipX: widget.camera.lensDirection == CameraLensDirection.front,
                        child: CameraPreview(_controller),
                      ),
                    ),
                  ));
            } else {
              return const Center(child: CircularProgressIndicator());
            }
          },
        ),
        bottomNavigationBar: SizedBox(
          width: Get.width,
          height: 100,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Center(
                child: InkWell(
                  onTap: () {
                    _takePicture();
                  },
                  child: Container(
                    height: 60,
                    width: 60,
                    decoration: BoxDecoration(
                        shape: BoxShape.circle, color: AppColors.greyColor),
                    child: Icon(Icons.camera),
                  ),
                ),
              ),
            ],
          ),
        ));
  }
}
