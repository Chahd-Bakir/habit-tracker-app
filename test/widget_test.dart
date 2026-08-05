// Basic smoke test for the auth bootstrapping.

import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:khotwa/main.dart';

void main() {
  testWidgets('App boots to login screen when not authenticated',
      (WidgetTester tester) async {
    const storage = MethodChannel('plugins.it_nomads.com/flutter_secure_storage');
    TestWidgetsFlutterBinding.ensureInitialized().defaultBinaryMessenger
        .setMockMethodCallHandler(storage, (call) async {
      return null; // read() returns no stored token.
    });

    await tester.pumpWidget(const KhotwaApp());
    await tester.pumpAndSettle();

    expect(find.text('Se connecter'), findsOneWidget);
  });
}
