# API Login Documentation (Updated)

## Endpoint: Login dengan Email atau Telepon

**URL:** `/api/auth/login`  
**Method:** `POST`  
**Auth Required:** No  
**Content-Type:** `application/json`

---

## 🆕 Fitur Terbaru

✅ **Login Fleksibel**: User bisa login menggunakan:
- Email (contoh: `user@example.com`)
- Nomor Telepon (contoh: `08123456789`)

API akan otomatis mendeteksi apakah input adalah email atau nomor telepon.

---

## Request Body

```json
{
  "email_telepon": "user@example.com",
  "password": "password123"
}
```

atau

```json
{
  "email_telepon": "08123456789",
  "password": "password123"
}
```

### Parameters

| Parameter      | Type   | Required | Description                                      |
|----------------|--------|----------|--------------------------------------------------|
| email_telepon  | string | Yes      | Email address atau nomor telepon user            |
| password       | string | Yes      | User password                                    |

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
    "email_telepon": [
      "The email telepon field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

### Email/Telepon Not Found

**Code:** `404 Not Found`

```json
{
  "error": true,
  "status_code": 404,
  "message": "Email/Telepon tidak terdaftar",
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
  "message": "Email/Telepon atau password salah",
  "data": null
}
```

---

## 🔍 How It Works

1. **Input Validation**: API menerima `email_telepon` sebagai string
2. **User Search**: Mencari user di database berdasarkan:
   - Field `email` ATAU
   - Field `telepon`
3. **Auto Detection**: API otomatis mendeteksi apakah input adalah:
   - **Email** (jika mengandung format email valid)
   - **Telepon** (jika bukan format email)
4. **Authentication**: Laravel Auth menggunakan field yang sesuai
5. **Token Generation**: Jika berhasil, generate Sanctum token

---

## Usage Example

### cURL - Login dengan Email

```bash
curl -X POST http://crm-dorskin.test/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email_telepon": "user@example.com",
    "password": "password123"
  }'
```

### cURL - Login dengan Telepon

```bash
curl -X POST http://crm-dorskin.test/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email_telepon": "08123456789",
    "password": "password123"
  }'
```

### JavaScript (Fetch) - Email

```javascript
const loginWithEmail = async (email, password) => {
  const response = await fetch('http://crm-dorskin.test/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      email_telepon: email,
      password: password
    })
  });

  const data = await response.json();
  
  if (data.error === false) {
    localStorage.setItem('access_token', data.data.access_token);
    console.log('Login successful:', data.data.user);
    return data.data;
  } else {
    console.error('Login failed:', data.message);
    throw new Error(data.message);
  }
};

// Usage
await loginWithEmail('user@example.com', 'password123');
```

### JavaScript (Fetch) - Telepon

```javascript
const loginWithPhone = async (phone, password) => {
  const response = await fetch('http://crm-dorskin.test/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      email_telepon: phone,
      password: password
    })
  });

  const data = await response.json();
  
  if (data.error === false) {
    localStorage.setItem('access_token', data.data.access_token);
    console.log('Login successful:', data.data.user);
    return data.data;
  } else {
    console.error('Login failed:', data.message);
    throw new Error(data.message);
  }
};

// Usage
await loginWithPhone('08123456789', 'password123');
```

### Axios - Fleksibel (Email atau Telepon)

```javascript
import axios from 'axios';

const login = async (emailOrPhone, password) => {
  try {
    const response = await axios.post('http://crm-dorskin.test/api/auth/login', {
      email_telepon: emailOrPhone,
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

// Usage - Email
await login('user@example.com', 'password123');

// Usage - Phone
await login('08123456789', 'password123');
```

### React Login Component

```javascript
import React, { useState } from 'react';
import axios from 'axios';

const LoginForm = () => {
  const [emailTelepon, setEmailTelepon] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await axios.post('http://crm-dorskin.test/api/auth/login', {
        email_telepon: emailTelepon,
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
        
        // Redirect or update state
        console.log('Login successful:', response.data.data.user);
        // window.location.href = '/dashboard';
      }
    } catch (err) {
      if (err.response) {
        setError(err.response.data.message);
      } else {
        setError('Terjadi kesalahan. Silakan coba lagi.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleLogin}>
      <div>
        <label>Email atau Telepon:</label>
        <input
          type="text"
          value={emailTelepon}
          onChange={(e) => setEmailTelepon(e.target.value)}
          placeholder="user@example.com atau 08123456789"
          required
        />
      </div>
      
      <div>
        <label>Password:</label>
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          placeholder="Password"
          required
        />
      </div>

      {error && <div style={{ color: 'red' }}>{error}</div>}

      <button type="submit" disabled={loading}>
        {loading ? 'Loading...' : 'Login'}
      </button>
    </form>
  );
};

export default LoginForm;
```

---

## 🧪 Testing

### Postman

1. **Set Method**: POST
2. **Set URL**: `http://crm-dorskin.test/api/auth/login`
3. **Headers**:
   - `Content-Type`: application/json
   - `Accept`: application/json
4. **Body** (raw JSON):

**Test dengan Email:**
```json
{
  "email_telepon": "user@example.com",
  "password": "password123"
}
```

**Test dengan Telepon:**
```json
{
  "email_telepon": "08123456789",
  "password": "password123"
}
```

---

## 💡 Best Practices

1. **Input Flexibility**: User tidak perlu memilih apakah login dengan email atau telepon
2. **Error Handling**: Handle semua error cases dengan baik
3. **Token Security**: Simpan token dengan aman
4. **Auto-Detection**: API otomatis detect tipe input
5. **Validation**: Input tervalidasi sebelum proses authentication

---

## 🔒 Security Notes

1. Gunakan HTTPS di production
2. Implement rate limiting untuk prevent brute force
3. Log failed login attempts
4. Consider account lockout after multiple failed attempts
5. Validate phone number format jika perlu
6. Sanitize input untuk prevent SQL injection
7. Use strong password policy

---

## ⚙️ Backend Logic

```php
// 1. Validate input
$validate = Validator::make($request->all(), [
    'email_telepon' => 'required|string',
    'password' => 'required',
]);

// 2. Search user by email OR phone
$user = User::where('email', $request->email_telepon)
    ->orWhere('telepon', $request->email_telepon)
    ->first();

// 3. Check password
if (!Hash::check($request->password, $user->password)) {
    // Return error
}

// 4. Auto-detect login field
$loginField = filter_var($request->email_telepon, FILTER_VALIDATE_EMAIL) 
    ? 'email' 
    : 'telepon';

// 5. Attempt authentication
$credentials = [
    $loginField => $request->email_telepon,
    'password' => $request->password
];

if (!Auth::attempt($credentials)) {
    // Return error
}

// 6. Generate token
$tokenResult = $user->createToken('auth-token')->plainTextToken;
```

---

## 📞 Support

Jika ada pertanyaan atau issues, silakan hubungi tim development.

---

## ✅ Changelog

**v2.0** - Updated
- ✅ Support login dengan email ATAU telepon
- ✅ Auto-detection tipe input
- ✅ Flexible validation
- ✅ Improved error messages
- ✅ Updated documentation

**v1.0** - Initial
- Login dengan email only
