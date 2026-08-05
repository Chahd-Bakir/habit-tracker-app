import 'package:flutter/foundation.dart';

import '../services/api_service.dart';

/// État global de la session d'authentification.
enum AuthStatus { unknown, authenticated, unauthenticated }

/// Gère l'authentification de l'utilisateur (login, register, logout)
/// et expose l'état de connexion au reste de l'application.
class AuthProvider extends ChangeNotifier {
  AuthProvider({ApiService? api}) : _api = api ?? ApiService();

  final ApiService _api;

  AuthStatus _status = AuthStatus.unknown;
  bool _isLoading = false;
  String? _error;

  AuthStatus get status => _status;
  bool get isLoggedIn => _status == AuthStatus.authenticated;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Restaure la session au démarrage à partir du token stocké.
  Future<void> restoreSession() async {
    try {
      final token = await _api.getToken();
      _status = (token == null || token.isEmpty)
          ? AuthStatus.unauthenticated
          : AuthStatus.authenticated;
    } catch (_) {
      _status = AuthStatus.unauthenticated;
    }
    notifyListeners();
  }

  /// Tente une connexion. Retourne `true` si la session est établie.
  Future<bool> login({
    required String email,
    required String password,
  }) {
    return _authenticate(
      () => _api.login(email: email, password: password),
    );
  }

  /// Tente une inscription. Retourne `true` si la session est établie.
  Future<bool> register({
    required String email,
    required String password,
  }) {
    return _authenticate(
      () => _api.register(email: email, password: password),
    );
  }

  /// Déconnecte l'utilisateur (suppression locale du token).
  Future<void> logout() async {
    await _api.deleteToken();
    _status = AuthStatus.unauthenticated;
    _error = null;
    notifyListeners();
  }

  Future<bool> _authenticate(Future<String> Function() request) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final token = await request();
      await _api.saveToken(token);
      _status = AuthStatus.authenticated;
      return true;
    } catch (e) {
      _error = e is ApiException
          ? e.message
          : 'Une erreur est survenue. Veuillez réessayer.';
      _status = AuthStatus.unauthenticated;
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
