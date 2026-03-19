import 'dart:convert';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:setu_printers/config/app_config.dart';
import 'package:setu_printers/config/theme/app_colors.dart';
import 'package:setu_printers/config/theme/app_text_style.dart';
import 'package:setu_printers/network/progress_indicator.dart';

class NetworkDio {
  static late Dio _dio;
    static Circle processIndicator = Circle();

  static Future<void> setDynamicHeader() async {
    final BaseOptions options = BaseOptions(
        receiveTimeout: const Duration(minutes: 3),
        connectTimeout: const Duration(minutes: 3),
        headers: {
          'Authorization': 'Bearer ${ApiPath.scanApiToken}',
        });

    _dio = Dio(options);
    if (kDebugMode) {
      _dio.interceptors.add(LogInterceptor(
          request: false,
          requestHeader: true,
          responseHeader: false,
          requestBody: true,
          responseBody: true));
    }
    Connectivity()
        .onConnectivityChanged
        .listen((List<ConnectivityResult> result) {
      for (var i = 0; i < result.length; i++) {
        if (ConnectionState.none.name == result[i].name) {}
      }
    });
  }

  static Future<bool> check() async {
    final List<ConnectivityResult> connectivityResult =
        await Connectivity().checkConnectivity();
    if (connectivityResult.contains(ConnectivityResult.mobile)) {
      return true;
    } else if (connectivityResult.contains(ConnectivityResult.wifi)) {
      return true;
    } else if (connectivityResult.contains(ConnectivityResult.none)) {
      return false;
    }
    return false;
  }

  static Future<Map<String, dynamic>?> getData({
    BuildContext? context,
    required String url,
  }) async {
    final bool internet = await check();
    if (internet) {
      try {
        final Response<dynamic> response = await _dio.get(url);
        Map<String, dynamic> responseBody = <String, dynamic>{};
        if (response.statusCode == 200) {
          try {
            final decoded = json.decode(response.toString());
            if (decoded is List) {
              responseBody = {'status': response.statusCode, 'data': decoded};
            } else {
              responseBody = decoded as Map<String, dynamic>;
            }
            return responseBody;
          } catch (e) {
            if (response.data is List) {
              responseBody = {'status': response.statusCode, 'data': response.data};
            } else {
              responseBody = response.data as Map<String, dynamic>;
            }
            return responseBody;
          }
        }
      } on DioException catch (e) {
        if (e.error == 'Http status error [404]') {
          Map<String, dynamic> responseBody = <String, dynamic>{};
          if (context != null && context.mounted) {
            showError(
              title: 'Error',
              errorMessage: "Http status error [404]",
              context: context,
            );
          }
          return responseBody;
        } else {
          Map<String, dynamic> responseBody = <String, dynamic>{};
          return responseBody;
        }
      }
    }
    return null;
  }

  static Future<Map<String, dynamic>?> postData({
    BuildContext? context,
    required String url,
    required dynamic data,
  }) async {
    final bool internet = await check();
    if (internet) {
      if (context != null && context.mounted) {
        processIndicator.show(context);
      }
      try {
        final Response<dynamic> response = await _dio.post(url, data: data);
        Map<String, dynamic> responseBody = <String, dynamic>{};
        if (response.statusCode == 200 || response.statusCode == 201) {
          try {
            responseBody = json.decode(response.toString());
            if (context != null && context.mounted) {
              processIndicator.hide(context);
            }
            return responseBody;
          } catch (e) {
            responseBody = response.data as Map<String, dynamic>;
            if (context != null && context.mounted) {
              processIndicator.hide(context);
            }
            return responseBody;
          }
        }
      } on DioException catch (e) {
        if (e.error == 'Http status error [404]') {
          Map<String, dynamic> responseBody = <String, dynamic>{};
          if (context != null && context.mounted) {
            showError(
              title: 'Error',
              errorMessage: "Http status error [404]",
              context: context,
            );
            processIndicator.hide(context);
          }
          return responseBody;
        } else {
          Map<String, dynamic> responseBody = <String, dynamic>{};
          if (context != null && context.mounted) {
            processIndicator.hide(context);
          }
          return responseBody;
        }
      }
    }
    if (context != null && context.mounted) {
      processIndicator.hide(context);
    }
    return null;
  }

  static void showError({
    required String title,
    required String errorMessage,
    BuildContext? context,
  }) {
    if (context != null) {
      dialogData(context: context, errorMessage: errorMessage, title: title);
      // // popupBottomSheet(context, errorMessage);
      // ScaffoldMessenger.of(context)
      //     .showSnackBar(SnackBars.errorSnackBar(title: errorMessage));
    }
  }
}

dialogData({
  required BuildContext context,
  required String title,
  required String errorMessage,
}) {
  return showDialog(
    context: context,
    barrierDismissible: false,
    builder: (context) {
      return StatefulBuilder(builder: (context, setstate) {
        return Dialog(
          insetPadding: const EdgeInsets.symmetric(horizontal: 15),
          shape: const RoundedRectangleBorder(
            borderRadius: BorderRadius.all(
              Radius.circular(10.0),
            ),
          ),
          child: Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(10),
              color: Colors.white,
            ),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  title,
                  style: AppTextStyle.semiBold20.copyWith(
                    decoration: TextDecoration.underline,
                  ),
                ),
                const SizedBox(
                  height: 10,
                ),
                const SizedBox(
                  height: 10,
                ),
                Text(errorMessage),
                const SizedBox(
                  height: 25,
                ),
                Align(
                  alignment: Alignment.center,
                  child: InkWell(
                    onTap: () {
                      Navigator.pop(context);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          vertical: 8, horizontal: 40),
                      decoration: BoxDecoration(
                        color: AppColors.appColors,
                        borderRadius: BorderRadius.circular(10.0),
                      ),
                      child: Text(
                        'OK',
                        textScaler: TextScaler.noScaling,
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      });
    },
  );
}
