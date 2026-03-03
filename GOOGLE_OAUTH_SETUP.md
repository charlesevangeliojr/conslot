# Google OAuth Setup Instructions

To enable Google OAuth authentication in ConSlot, follow these steps:

## 1. Create Google OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable Google+ API and Google OAuth2 API
4. Create OAuth 2.0 Client ID credentials
5. Add authorized redirect URI: `https://yourdomain.com/auth/google-callback.php`

## 2. Configure the Application

1. Copy `config/google_oauth.php.example` to `config/google_oauth.php`
2. Update the following constants with your credentials:

```php
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/google-callback.php');
```

## 3. Database Setup

Ensure your users table has the following columns:
- `google_id` (VARCHAR, nullable) - Store Google user ID
- `avatar` (VARCHAR, nullable) - Store Google profile picture URL

## 4. Security Notes

- Never commit actual OAuth credentials to version control
- Use environment variables in production
- Regularly rotate your client secrets
- Monitor OAuth usage for suspicious activity

## 5. Testing

Test the OAuth flow with:
1. New user registration via Google
2. Existing user login via Google
3. Email conflict handling

## Files Involved

- `config/google_oauth.php` - OAuth configuration
- `auth/google-callback.php` - OAuth callback handler
- `auth/login.php` - Login with Google integration
- `auth/register.php` - Registration with Google integration
