import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Erreur métier levée par l'API (message compréhensible pour l'utilisateur).
class ApiException implements Exception {
  const ApiException(this.message);

  final String message;

  @override
  String toString() => message;
}

/// Client HTTP central pour le backend Laravel (Khotwa/Ninja).
///
/// L'URL de base est configurable au build via `--dart-define=API_BASE_URL=...`.
/// Par défaut, elle pointe vers l'hôte local (10.0.2.2 = localhost depuis
/// l'émulateur Android).
class ApiService {
  ApiService({Dio? dio, FlutterSecureStorage? storage})
      : _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: baseUrl,
                connectTimeout: const Duration(seconds: 15),
                receiveTimeout: const Duration(seconds: 15),
                headers: {'Accept': 'application/json'},
              ),
            ),
        _storage = storage ?? const FlutterSecureStorage();

  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000',
  );

  static const String _tokenKey = 'auth_token';

  final Dio _dio;
  final FlutterSecureStorage _storage;

  /// Envoie une requête de connexion et retourne le token Sanctum.
  Future<String> login({
    required String email,
    required String password,
  }) async {
    final data = await _submit(
      '/api/login',
      {'email': email, 'password': password},
    );
    return _extractToken(data);
  }

  /// Crée un compte et retourne le token Sanctum.
  Future<String> register({
    required String email,
    required String password,
  }) async {
    final data = await _submit(
      '/api/register',
      {'email': email, 'password': password},
    );
    return _extractToken(data);
  }

  Future<Map<String, dynamic>> _submit(
    String path,
    Map<String, dynamic> fields,
  ) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        path,
        data: FormData.fromMap(fields),
      );
      return response.data ?? const {};
    } on DioException catch (e) {
      throw ApiException(_errorMessage(e));
    }
  }

  /// Le token peut être à la racine (`token`) ou imbriqué dans `data.token`.
  String _extractToken(Map<String, dynamic> data) {
    final raw = data['data'];
    final root = data['token'];
    final nested = raw is Map ? raw['token'] : null;
    final token = root is String ? root : (nested is String ? nested : null);
    if (token == null || token.isEmpty) {
      throw const ApiException('Aucun token reçu du serveur.');
    }
    return token;
  }

  String _errorMessage(DioException e) {
    final data = e.response?.data;
    if (data is Map) {
      final message = data['message'];
      if (message is String && message.isNotEmpty) {
        return message;
      }
      final errors = data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final firstValues = errors.values.whereType<List>().toList();
        if (firstValues.isNotEmpty && firstValues.first.isNotEmpty) {
          return firstValues.first.first.toString();
        }
      }
    }
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
      case DioExceptionType.transformTimeout:
        return 'Le serveur met trop de temps à répondre.';
      case DioExceptionType.connectionError:
        return 'Impossible de joindre le serveur.';
      case DioExceptionType.badCertificate:
        return 'Certificat serveur invalide.';
      case DioExceptionType.badResponse:
        return 'Erreur serveur (${e.response?.statusCode}).';
      case DioExceptionType.cancel:
        return 'Requête annulée.';
      case DioExceptionType.unknown:
        return 'Une erreur réseau est survenue.';
    }
  }

  // ---------------------------------------------------------------------------
  // Stockage sécurisé du token
  // ---------------------------------------------------------------------------

  Future<void> saveToken(String token) =>
      _storage.write(key: _tokenKey, value: token);

  Future<String?> getToken() => _storage.read(key: _tokenKey);

  Future<void> deleteToken() => _storage.delete(key: _tokenKey);
}
