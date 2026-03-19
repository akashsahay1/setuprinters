import 'package:flutter/material.dart';

class AppColors {
  static const Color whiteColor = Color(0xffFFFFFF);
  static const Color lightBlueColor = Color(0xffbbdefb);
  static const Color blueCOlor = Color(0xff1462be);
  static const Color blackColor = Color(0xff000000);
  static const Color primaryContainerColor = Color(0xff2F3F58);
  static const Color backgroundColor = Color(0xFFEBF4FB);
  static const Color secondaryBackgroundColor = Color(0xFF1C2833);
  static const Color btnBlueColor = Color(0xff1C2934);
  static const Color secounfLightOrangeColor = Color(0xffFFCFAC);
  static const Color secondaryContainerColor = Color(0xffFCB678);
  static const Color purpleColor = Color.fromARGB(255, 180, 41, 196);
  static const Color lightOrangeColor = Color(0xffEF994E);
  static const Color darkOrangeColor = Color(0xffFE982A);
  static const Color successColor = Color(0xff4BB543);
  static const Color parotColor = Color.fromARGB(255, 142, 227, 136);
  static const Color errorColor = Color(0xffFF0000);
  static const Color brownishColor = Color(0xff451010);
  static const Color textFieldLightColor = Color(0xffc27b46);
  static const Color textFieldLightColorBottomSheet = Color(0xffcd9267);
  static const Color greyColor = Color(0xffe5e8ee);
  static const Color textGreyColor = Color(0xff6D6D6D);
  static const Color darkgreyColor = Color(0xff403F3F);
  static const Color textGreyColors = Color(0xff909090);
  static const Color textFieldColor = Color(0xff3A2139);
  static const Color dashboardBgColor = Color(0xff1C2832);
  static const Color rewardExchange1 = Color(0xffffc85c);
  static const Color dropDownBg = Color(0xffe2a36d);
  static const Color deepDarkOrangeColor = Color(0xffff7f40);
  // static const Color blueColor = Color(0xff177082);
  static const Color blueColorLink = Color.fromARGB(255, 2, 64, 84);
  static const Color scaffoldColor = Color(0xff1F2C38);
  static const Color moreWithBounzContainerColor = Color(0xff219EBC);
  static const Color blue = Color(0xff45C1F4);
  static const Color textColorRed = Color(0xffFE2137);
  static const Color scaffoldOrangeColor = Color(0xffF29945);
  static const Color collectBgColor = Color(0xffFACA72);
  static const Color orangeGiftCardFilterColor = Color(0xffE4A470);

  //gradientCost
  static const LinearGradient gradientThemeColor = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [
      Color(0xffEF994E),
      Color(0xffFE982A),
      Color(0xffBB4F07),
    ],
  );

  static const Color gradientColor = Color(0xffffbf3c);
  static const Color gradientColorTwo = Color(0xfff94e3a);
  static const Color blueButtonColor = Color(0xff1C2934);
  static const Color darkBlueTextColor = Color(0xff0A1119);

  static const Color wheelBorderColor = Color(0xff405566);
  static const Color fortuneItemColor = Color(0xFFAE5915);
  static const Color buttonBorderColor = Color(0xFFB0BEC5);
  static const Color triangleColor = Color.fromARGB(255, 80, 77, 69);
  static const Color ratingIcon = Color(0xFFFFD700);
  static const Color appColors = Color(0xFF45C1F4);
  static const Color appDarkColors = Color.fromARGB(255, 11, 145, 235);
  static const Color secoundColors = Color(0xFFFFAE10);
  static const Color sucessGreen = Color(0xFF20a25c);
  static const Color borderColors = Color(0xFFCDCDCD);
  static const Color blueColor = Color(0xff1c1855);
  static const Color pinkColor = Color.fromARGB(255, 244, 132, 190);
  static const Color redColor = Color(0xffFE0000);
  static const Color orangeColor = Color(0xffFE7914);
  static const Color lightOrange = Color(0xffff826c);
  static const Color darkRedColor = Color(0xffFF3332);
  static const Color blueColorgr = Color(0xff00C9D1);
  static const Color darkBlueColor = Color(0xff000408);
  static const Color golderColor = Color.fromARGB(255, 244, 197, 57);
  static const Color yellowColor = Color.fromARGB(255, 239, 216, 43);
  static const Color containerbg = Color.fromARGB(255, 235, 244, 250);
  static const Color containerShaddowbg = Color.fromARGB(255, 130, 169, 218);

  static const Gradient circleGradient = LinearGradient(
    colors: [
      Color.fromARGB(255, 11, 145, 235),
      Color(0xFF45C1F4),
      Color(0xFF44C2F4),
      Color.fromARGB(255, 11, 145, 235),
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );

  static const Gradient pinkGradient = LinearGradient(
    colors: [
      AppColors.golderColor,
      AppColors.pinkColor,
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient yellowGradient = LinearGradient(
    colors: [
      AppColors.pinkColor,
      AppColors.golderColor,
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static Gradient whiteGradient = LinearGradient(
    colors: [
      AppColors.blue.withValues(alpha: 0.8),
      AppColors.whiteColor,
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient blackGradient = LinearGradient(
    colors: [
      Color.fromARGB(255, 229, 212, 220),
      Color.fromARGB(255, 55, 54, 52),
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient blueGradient = LinearGradient(
    colors: [
      AppColors.lightBlueColor,
      AppColors.darkBlueColor,
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient lightBlueGradient = LinearGradient(
    colors: [
      AppColors.lightBlueColor,
      Color.fromARGB(255, 135, 229, 232),
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient organeGradient = LinearGradient(
    colors: [
      AppColors.golderColor,
      AppColors.lightOrange,
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static Gradient bgBackground = LinearGradient(
    colors: [
      const Color.fromARGB(255, 230, 190, 13),
      const Color.fromARGB(255, 240, 118, 19).withValues(alpha: 0.1),
    ],
    tileMode: TileMode.clamp,
  );
  static const Gradient containerBackground = LinearGradient(
    colors: [
      AppColors.darkRedColor,
      AppColors.lightOrange,
    ],
    tileMode: TileMode.clamp,
  );
  static const Gradient harfcontainerBackground = LinearGradient(
    colors: [
      AppColors.darkRedColor,
      Color.fromARGB(255, 188, 113, 22),
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient blueCircleGradient = LinearGradient(
    colors: [
      AppColors.yellowColor,
      AppColors.blueColorgr,
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient blueCircleGradient2 = LinearGradient(
    colors: [
      AppColors.blueColorgr,
      AppColors.yellowColor,
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient pinkColorGradient = LinearGradient(
    colors: [
      AppColors.yellowColor,
      Color.fromARGB(255, 225, 130, 236),
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const Gradient pinkColor2Gradient = LinearGradient(
    colors: [
      Color.fromARGB(255, 225, 130, 236),
      AppColors.yellowColor,
    ],
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,

    // begin: Alignment.topRight,
    // end: Alignment.bottomLeft,
  );
  static Gradient lightPinkColorGradient = LinearGradient(
    colors: [
      const Color.fromARGB(255, 225, 130, 236),
      AppColors.yellowColor.withValues(alpha: 0.5),
    ],
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
  );
  static const textFormFieldGradient = LinearGradient(
    colors: [
      Color.fromRGBO(58, 33, 57, 0.23),
      Color.fromRGBO(22, 9, 19, 0.23),
    ],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
}
