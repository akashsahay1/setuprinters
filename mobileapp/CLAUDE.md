# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build & Development Commands

```bash
flutter pub get              # Install dependencies
flutter run                  # Run the app (debug mode)
flutter run -d <device>      # Run on specific device (chrome, windows, android, etc.)
flutter analyze              # Run static analysis / linting
flutter test                 # Run all widget tests
flutter test test/widget_test.dart  # Run a single test file
flutter build apk            # Build Android APK
flutter build ios            # Build iOS
```

**Dart SDK:** ^3.6.0
**Linting:** Uses `flutter_lints` (see `analysis_options.yaml`)

## Architecture

This is a Flutter mobile app for user authentication via selfie capture and QR code scanning, communicating with a backend at `https://setu.stime.in/api/`.

### App Flow

SplashScreen → AuthScreen (user selection dropdown) → SelfieScreen (photo capture + QR scan) → uploads to API → back to AuthScreen

### Directory Layout (`lib/`)

- **`main.dart`** — Entry point. Sets portrait-only orientation, launches `SetuPrinters` root widget.
- **`modules/`** — Screens organized by feature (`auth/`, `selfie/`, `splash_screen.dart`). Each screen is a StatefulWidget.
- **`network/`** — `NetworkDio` wraps Dio HTTP client with connectivity checking, loading indicators, and error dialogs. `Circle` and `InternetError` are singleton overlay managers.
- **`model/`** — API response models with `fromJson` factory constructors.
- **`config/`** — `AppConfig` (API base URL, endpoints), `AppAssets` (asset paths), `AppColors` (color palette + gradients), `AppTextStyle` (typography by weight/size).
- **`manager/`** — `GlobalSingleton` holds global state (currently selected user).

### Key Patterns

- **Navigation:** GetX (`Get.to()`, `Get.off()`, `Get.back()`) — no named routes
- **State:** `GlobalSingleton` for cross-screen state, `setState()` for local widget state
- **HTTP:** All API calls go through `NetworkDio` static methods (`getDioHttpMethod`, `postDioHttpMethod`). Multipart uploads use `FormData`.
- **API endpoints:** `/user/list` (GET, 200), `/user/scan-code` (POST multipart, 201)
- **Image processing:** Camera captures are center-cropped to square and resized to 300x300 before upload

### Assets

Located in `/assets/`. Registered in `pubspec.yaml`. Referenced via `AppAssets` constants. Includes `beep.mp3` for QR scan audio feedback.
