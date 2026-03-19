// ignore_for_file: library_private_types_in_public_api

import 'dart:developer';

import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:setu_printers/config/app_assets.dart';
import 'package:setu_printers/config/app_config.dart';
import 'package:setu_printers/config/theme/app_colors.dart';
import 'package:setu_printers/config/theme/app_text_style.dart';
import 'package:setu_printers/manager/global_singleton.dart';
import 'package:setu_printers/model/get_user_data_model.dart';
import 'package:setu_printers/modules/selfie/selfie_screen.dart';
import 'package:setu_printers/network/network_dio.dart';

class AuthScreen extends StatefulWidget {
  const AuthScreen({super.key});

  @override
  State<AuthScreen> createState() => _AuthScreenState();
}

class _AuthScreenState extends State<AuthScreen> {
  GetUserDataModel userList = GetUserDataModel();
  @override
  void initState() {
    getUserDataList();
    super.initState();
  }

  void getUserDataList() async {
    Map<String, dynamic>? response = await NetworkDio.getData(
        url: ApiPath.baseUrl + ApiPath.userList, context: context);

    log(response.toString());

    if (!mounted) return;

    if (response != null && response['status'] == 200) {
      userList = GetUserDataModel.fromJson(response);
      setState(() {});
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text("Failed to fetch users"),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        clipBehavior: Clip.none,
        children: [
          Container(
            height: Get.height / 2,
            width: Get.width,
            color: AppColors.lightBlueColor,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Image.asset(
                  AppAssets.user,
                  height: 200,
                  width: 200,
                ),
              ],
            ),
          ),
          Container(
            width: Get.width,
            height: Get.height / 1.8,
            margin: EdgeInsets.only(top: Get.height / 2.2),
            decoration: BoxDecoration(
                borderRadius: BorderRadius.only(
                    topRight: Radius.circular(40),
                    topLeft: Radius.circular(40)),
                color: AppColors.greyColor),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                SizedBox(
                  height: 20,
                ),
                Text(
                  "Select Your Id",
                  style: AppTextStyle.semiBold26,
                ),
                SizedBox(
                  height: 10,
                ),
                if (userList.data != null)
                  DropdownExample(userList: userList.data),
                SizedBox(
                  height: 30,
                ),
                InkWell(
                  onTap: () {
                    if (GlobalSingleton.selectedUser != null) {
                      Get.to(() => SelfieScreen());
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text("Please select Your ID"),
                        ),
                      );
                    }
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
                      "Next",
                      style: AppTextStyle.semiBold20
                          .copyWith(color: AppColors.whiteColor),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class DropdownExample extends StatefulWidget {
  final List<UserData>? userList;
  const DropdownExample({super.key, this.userList});

  @override
  _DropdownExampleState createState() => _DropdownExampleState();
}

class _DropdownExampleState extends State<DropdownExample> {
  UserData? _selectedValue;

  @override
  void initState() {
    super.initState();
    _restoreSavedUser();
  }

  void _restoreSavedUser() {
    final savedUser = GlobalSingleton.selectedUser;
    if (savedUser != null && widget.userList != null) {
      for (final user in widget.userList!) {
        if (user.userId == savedUser.userId) {
          _selectedValue = user;
          GlobalSingleton.selectedUser = user;
          break;
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: SizedBox(
          child: DropdownButtonHideUnderline(
        child: DropdownButtonFormField<UserData>(
          focusColor: AppColors.appColors,
          decoration: const InputDecoration(
              contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 0),
              border: OutlineInputBorder(
                borderSide: BorderSide(color: AppColors.appColors, width: 1.0),
              ),
              enabledBorder: OutlineInputBorder(
                borderSide: BorderSide(color: AppColors.appColors, width: 1.0),
              )),
          hint: const Text(
            'Select Your ID',
            textScaler: TextScaler.noScaling,
          ),
          initialValue: _selectedValue,
          isExpanded: true,
          onChanged: (val) {
            if (val != null) {
              GlobalSingleton.saveUser(val);
            }
            setState(() {
              _selectedValue = val;
            });
          },
          validator: (value) => value == null ? 'This field required' : null,
          icon: const Icon(
            Icons.arrow_drop_down,
            color: AppColors.appColors,
            size: 24,
          ),
          iconSize: 24,
          items: widget.userList!
              .map<DropdownMenuItem<UserData>>((UserData value) {
                return DropdownMenuItem<UserData>(
                  value: value,
                  child: Text(
                    "${value.fullName} - ${value.userId}",
                    textScaler: TextScaler.noScaling,
                    style: AppTextStyle.regular16,
                  ),
                );
              })
              .toSet()
              .toList(),
        ),
      )),
    );
  }
}
