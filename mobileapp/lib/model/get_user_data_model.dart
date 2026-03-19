class GetUserDataModel {
  int? status;
  List<UserData>? data;

  GetUserDataModel({this.status, this.data});

  GetUserDataModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    if (json['data'] != null) {
      data = <UserData>[];
      json['data'].forEach((v) {
        data!.add(UserData.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['status'] = status;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class UserData {
  String? userId;
  int? employeeId;
  String? fullName;
  String? phoneNumber;
  String? phoneNumber2;
  String? email;
  String? address;
  String? profilePhoto;
  bool? isDeleted;
  String? createdAt;
  String? updatedAt;

  UserData(
      {this.userId,
      this.employeeId,
      this.fullName,
      this.phoneNumber,
      this.phoneNumber2,
      this.email,
      this.address,
      this.profilePhoto,
      this.isDeleted,
      this.createdAt,
      this.updatedAt});

  UserData.fromJson(Map<String, dynamic> json) {
    userId = json['id']?.toString();
    employeeId = json['employee_id'];
    fullName = json['full_name'];
    phoneNumber = json['phone_number'];
    phoneNumber2 = json['phone_number_2'];
    email = json['email'];
    address = json['address'];
    profilePhoto = json['profile_photo'];
    isDeleted = json['is_deleted'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = userId;
    data['employee_id'] = employeeId;
    data['full_name'] = fullName;
    data['phone_number'] = phoneNumber;
    data['phone_number_2'] = phoneNumber2;
    data['email'] = email;
    data['address'] = address;
    data['profile_photo'] = profilePhoto;
    data['is_deleted'] = isDeleted;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}
