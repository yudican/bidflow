# API Authentication Documentation

Base URL: `http://your-domain.test/api/auth`

---

## 📌 Table of Contents
1. [Login](#1-login)
2. [Logout](#2-logout)
3. [Get User Info](#3-get-user-info)
4. [Refresh Token](#4-refresh-token)
5. [Revoke All Tokens](#5-revoke-all-tokens)

---

## 1. Login

**Endpoint:** `POST /api/auth/login`  
**Auth Required:** No  
**Description:** Authenticate user and get access token

### Request

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

### Success Response (200)

```json
{
  "error": false,
  "status_code": 200,
  "message": "Login berhasil",
  "data": {
    "access_token": "1|abcdefghijklmnopqrstuvwxyz123456789",
    "token_type": "Bearer",
    "user": {
      "id": "uuid-here",
      "name": "John Doe",
      "email": "user@example.com",
      "profile_photo_url": "https://example.com/photo.jpg",
      "telepon": "08123456789",
      "gender": "male",
      "role": "admin",
      "account_id": 1,
      "bod": "1990-01-01"
    }
  }
}
```

### Error Responses

#### Validation Error (422)
```json
{
  "error": true,
  "status_code": 422,
  "message": "Validasi gagal",
  "data": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

#### Email Not Found (404)
```json
{
  "error": true,
  "status_code": 404,
  "message": "Email tidak terdaftar",
  "data": null
}
```

#### Wrong Password (401)
```json
{
  "error": true,
  "status_code": 401,
  "message": "Password yang Anda masukkan salah",
  "data": null
}
```

---

## 2. Logout

**Endpoint:** `POST /api/auth/logout`  
**Auth Required:** Yes (Bearer Token)  
**Description:** Logout and revoke current access token

### Request Headers

```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200)

```json
{
  "error": false,
  "status_code": 200,
  "message": "Logout berhasil",
  "data": null
}
```

### Error Response (401)

```json
{
  "message": "Unauthenticated."
}
```

---

## 3. Get User Info

**Endpoint:** `GET /api/auth/me`  
**Auth Required:** Yes (Bearer Token)  
**Description:** Get authenticated user information

### Request Headers

```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200)

```json
{
  "error": false,
  "status_code": 200,
  "message": "Data user berhasil diambil",
  "data": {
    "id": "uuid-here",
    "name": "John Doe",
    "email": "user@example.com",
    "profile_photo_url": "https://example.com/photo.jpg",
    "telepon": "08123456789",
    "gender": "male",
    "role": "admin",
    "account_id": 1,
    "bod": "1990-01-01"
  }
}
```

---

## 4. Refresh Token

**Endpoint:** `POST /api/auth/refresh`  
**Auth Required:** Yes (Bearer Token)  
**Description:** Delete old token and get new access token

### Request Headers

```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200)

```json
{
  "error": false,
  "status_code": 200,
  "message": "Token berhasil di-refresh",
  "data": {
    "access_token": "2|newabcdefghijklmnopqrstuvwxyz123456789",
    "token_type": "Bearer",
    "user": {
      "id": "uuid-here",
      "name": "John Doe",
      "email": "user@example.com",
      "profile_photo_url": "https://example.com/photo.jpg",
      "telepon": "08123456789",
      "gender": "male",
      "role": "admin",
      "account_id": 1,
      "bod": "1990-01-01"
    }
  }
}
```

**Note:** After refreshing, the old token will be invalid. Use the new token for subsequent requests.

---

## 5. Revoke All Tokens

**Endpoint:** `POST /api/auth/revoke-all`  
**Auth Required:** Yes (Bearer Token)  
**Description:** Revoke all access tokens for the authenticated user (logout from all devices)

### Request Headers

```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200)

```json
{
  "error": false,
  "status_code": 200,
  "message": "Semua token berhasil dihapus",
  "data": null
}
```

**Note:** This will invalidate ALL tokens including the current one. User must login again.

---

## 📝 Usage Examples

### JavaScript (Fetch API)

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('http://your-domain.test/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.error === false) {
    // Save token
    localStorage.setItem('access_token', data.data.access_token);
    return data.data;
  }
  
  throw new Error(data.message);
};

// Logout
const logout = async () => {
  const token = localStorage.getItem('access_token');
  
  const response = await fetch('http://your-domain.test/api/auth/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  localStorage.removeItem('access_token');
  return data;
};

// Get User Info
const getUserInfo = async () => {
  const token = localStorage.getItem('access_token');
  
  const response = await fetch('http://your-domain.test/api/auth/me', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  return await response.json();
};

// Refresh Token
const refreshToken = async () => {
  const token = localStorage.getItem('access_token');
  
  const response = await fetch('http://your-domain.test/api/auth/refresh', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  
  if (data.error === false) {
    // Update token
    localStorage.setItem('access_token', data.data.access_token);
    return data.data;
  }
  
  throw new Error(data.message);
};
```

### Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://your-domain.test/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add token to requests automatically
api.interceptors.request.use(config => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 errors (token expired)
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('access_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Login
export const login = async (email, password) => {
  const { data } = await api.post('/auth/login', { email, password });
  if (data.error === false) {
    localStorage.setItem('access_token', data.data.access_token);
  }
  return data;
};

// Logout
export const logout = async () => {
  const { data } = await api.post('/auth/logout');
  localStorage.removeItem('access_token');
  return data;
};

// Get User Info
export const getUserInfo = async () => {
  const { data } = await api.get('/auth/me');
  return data;
};

// Refresh Token
export const refreshToken = async () => {
  const { data } = await api.post('/auth/refresh');
  if (data.error === false) {
    localStorage.setItem('access_token', data.data.access_token);
  }
  return data;
};

// Revoke All
export const revokeAll = async () => {
  const { data } = await api.post('/auth/revoke-all');
  localStorage.removeItem('access_token');
  return data;
};
```

### cURL Examples

```bash
# Login
curl -X POST http://your-domain.test/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Logout
curl -X POST http://your-domain.test/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Get User Info
curl -X GET http://your-domain.test/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Refresh Token
curl -X POST http://your-domain.test/api/auth/refresh \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Revoke All Tokens
curl -X POST http://your-domain.test/api/auth/revoke-all \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 🔒 Security Best Practices

1. **HTTPS Only**: Always use HTTPS in production
2. **Token Storage**: Store tokens securely (avoid localStorage for sensitive apps)
3. **Token Expiration**: Configure token expiration in `config/sanctum.php`
4. **Rate Limiting**: Implement rate limiting on auth endpoints
5. **CORS**: Configure CORS properly in `config/cors.php`
6. **Input Validation**: Always validate user input
7. **Password Policy**: Enforce strong password requirements
8. **2FA**: Consider implementing Two-Factor Authentication
9. **Audit Log**: Log authentication attempts
10. **Account Lockout**: Lock accounts after multiple failed attempts

---

## 🧪 Testing

### Postman Collection

Create a Postman collection with these requests:

1. **Login** - Save token to environment variable
2. **Get User Info** - Use token from login
3. **Refresh Token** - Update token variable
4. **Logout** - Clear token

### Environment Variables

```json
{
  "base_url": "http://your-domain.test/api",
  "access_token": "will_be_set_after_login"
}
```

---

## ⚙️ Configuration

### Sanctum Configuration

File: `config/sanctum.php`

```php
'expiration' => null, // Token never expires (set to minutes for expiration)
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
```

### CORS Configuration

File: `config/cors.php`

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'], // Configure for production
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

---

## 🚀 Quick Start

1. Login to get access token
2. Save token in localStorage/sessionStorage
3. Include token in Authorization header for protected endpoints
4. Refresh token before expiration (if configured)
5. Logout to revoke token when done

---

## 📞 Support

For issues or questions about the API, please contact the development team.
