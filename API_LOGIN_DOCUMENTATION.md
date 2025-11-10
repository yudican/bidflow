# API Login Documentation

## Endpoint: Login

**URL:** `/api/auth/login`  
**Method:** `POST`  
**Auth Required:** No  
**Content-Type:** `application/json`

---

## Request Body

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

### Parameters

| Parameter | Type   | Required | Description                    |
|-----------|--------|----------|--------------------------------|
| email     | string | Yes      | User email address (valid email format) |
| password  | string | Yes      | User password                  |

---

## Success Response

**Code:** `200 OK`

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

---

## Error Responses

### Validation Error

**Code:** `422 Unprocessable Entity`

```json
{
  "error": true,
  "status_code": 422,
  "message": "Validasi gagal",
  "data": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

### Email Not Found

**Code:** `404 Not Found`

```json
{
  "error": true,
  "status_code": 404,
  "message": "Email tidak terdaftar",
  "data": null
}
```

### Wrong Password

**Code:** `401 Unauthorized`

```json
{
  "error": true,
  "status_code": 401,
  "message": "Password yang Anda masukkan salah",
  "data": null
}
```

### Authentication Failed

**Code:** `401 Unauthorized`

```json
{
  "error": true,
  "status_code": 401,
  "message": "Email atau password salah",
  "data": null
}
```

---

## Usage Example

### cURL

```bash
curl -X POST http://your-domain.test/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

### JavaScript (Fetch)

```javascript
const login = async () => {
  const response = await fetch('http://your-domain.test/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      email: 'user@example.com',
      password: 'password123'
    })
  });

  const data = await response.json();
  
  if (data.error === false) {
    // Save token
    localStorage.setItem('access_token', data.data.access_token);
    console.log('Login successful:', data.data.user);
  } else {
    console.error('Login failed:', data.message);
  }
};
```

### Axios

```javascript
import axios from 'axios';

const login = async (email, password) => {
  try {
    const response = await axios.post('http://your-domain.test/api/auth/login', {
      email: email,
      password: password
    }, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    if (response.data.error === false) {
      // Save token
      localStorage.setItem('access_token', response.data.data.access_token);
      return response.data.data.user;
    }
  } catch (error) {
    if (error.response) {
      console.error('Login error:', error.response.data.message);
      throw error.response.data;
    }
    throw error;
  }
};
```

---

## Using the Access Token

Once you have the access token, include it in the Authorization header for protected API endpoints:

```bash
curl -X GET http://your-domain.test/api/protected-endpoint \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### JavaScript Example

```javascript
const fetchProtectedData = async () => {
  const token = localStorage.getItem('access_token');
  
  const response = await fetch('http://your-domain.test/api/protected-endpoint', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });

  const data = await response.json();
  return data;
};
```

---

## Notes

1. **Token Storage**: Store the access token securely (e.g., localStorage, sessionStorage, or secure cookie)
2. **Token Type**: Always use "Bearer" prefix when sending the token in Authorization header
3. **Token Expiration**: Laravel Sanctum tokens don't expire by default, but you can configure expiration in `config/sanctum.php`
4. **CORS**: Make sure CORS is properly configured in `config/cors.php` for API access from different origins
5. **Rate Limiting**: Consider implementing rate limiting for login endpoint to prevent brute force attacks

---

## Security Recommendations

1. Always use HTTPS in production
2. Implement rate limiting on login endpoint
3. Add 2FA (Two-Factor Authentication) for sensitive accounts
4. Log failed login attempts
5. Implement account lockout after multiple failed attempts
6. Use strong password policies
7. Regularly rotate API tokens
8. Validate email format properly
9. Use password hashing (already implemented with Laravel Hash)
10. Consider adding CAPTCHA for login form

---

## Testing

You can test the API using tools like:
- Postman
- Insomnia
- Thunder Client (VS Code extension)
- cURL
- Browser console with Fetch/Axios

Remember to set the `Accept: application/json` header to ensure you get JSON responses instead of HTML redirects.
