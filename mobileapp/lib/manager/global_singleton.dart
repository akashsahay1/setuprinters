import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';
import 'package:setu_printers/model/get_user_data_model.dart';

class GlobalSingleton {
    factory GlobalSingleton() {
        return _instance;
    }
    GlobalSingleton._internal();
    static final GlobalSingleton _instance = GlobalSingleton._internal();

    static UserData? selectedUser;

    static const String _selectedUserKey = 'selected_user';

    static Future<void> saveUser(UserData user) async {
        final prefs = await SharedPreferences.getInstance();
        final jsonString = jsonEncode(user.toJson());
        await prefs.setString(_selectedUserKey, jsonString);
        selectedUser = user;
    }

    static Future<void> loadUser() async {
        final prefs = await SharedPreferences.getInstance();
        final jsonString = prefs.getString(_selectedUserKey);
        if (jsonString != null) {
            final Map<String, dynamic> json = jsonDecode(jsonString);
            selectedUser = UserData.fromJson(json);
        }
    }
}
