# Security Guidelines for Web-Mathdosman

## Database Connection Preflight Checks

Preflight checks ensure the application fails fast when MySQL is unavailable, preventing the browser from hanging on long timeouts.

### Where Preflight Checks Are Used

1. **`config/db.php` (Global Connection)**
   - Runs `app_db_tcp_preflight()` before establishing PDO connection
   - Timeout: 5 seconds (configurable via `connect_timeout`)
   - Catches MySQL connection failures before PDO attempts connection

2. **`login.php` (Admin Login)**
   - Runs `fsockopen()` check on port 3306 (before full DB connection)
   - Timeout: 1.5 seconds
   - Provides quick feedback if MySQL is down

3. **`siswa/login.php` (Student Login)**
   - Also includes preflight check for consistency
   - Ensures both login pathways have consistent behavior

### Why This Matters

Without preflight checks, PDO can hang indefinitely if:
- MySQL process crashed but port remains open
- Firewall delays or drops connection attempts
- Network is unstable

Result: Users see blank/loading page instead of error message.

### Implementation Details

**TCP Preflight Function** (`config/db.php`):
```php
function app_db_tcp_preflight(string $host, int $port, float $timeoutSeconds): ?string
```

- Attempts TCP connection to MySQL port
- Reads 1 byte from stream (MySQL sends greeting immediately)
- Returns error string if connection fails or times out
- Returns null if successful

**For Windows/XAMPP**:
- Converts `localhost` to `127.0.0.1` (avoids named-pipe behavior)
- Uses TCP explicitly instead of named pipes

### Best Practices When Adding New DB-Heavy Pages

If you create new admin pages that access database on page load:

1. **Recommended**: Just use the existing global `$pdo` connection (already preflight-checked)
2. **Not needed**: Don't add separate preflight checks for every page
3. **If adding custom DB logic**: Consider using the `app_db_tcp_preflight()` function if you want early failure

### Log Location

Database connection errors are logged to `/logs/app.log` (if logging enabled).

---

## Credentials & Environment Variables

### Using .env Files (Recommended for Production)

Instead of hardcoding credentials in `config/config.php`, use environment variables:

**Set environment variables:**
```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=web-mathdosman
export DB_USER=dbuser
export DB_PASS=secure_password_here
```

**Or in `.env` file** (not tracked by git):
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=web-mathdosman
DB_USER=dbuser
DB_PASS=secure_password_here
```

**Load .env file in web server config or application bootstrap:**
```bash
# In Apache .htaccess or nginx config
SetEnv DB_HOST 127.0.0.1
```

Or in PHP before bootstrap:
```php
if (file_exists('.env')) {
    $env = parse_ini_file('.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}
```

### Session Timeouts Configuration

Session timeouts can be customized via environment variables or config:

**In `config/config.php`:**
```php
// Admin absolute timeout (default: 24 hours)
define('ADMIN_SESSION_TIMEOUT_SECONDS', (int)(getenv('ADMIN_SESSION_TIMEOUT_SECONDS') ?: (24 * 60 * 60)));

// Student absolute timeout (default: 3 hours)  
define('STUDENT_SESSION_TIMEOUT_SECONDS', (int)(getenv('STUDENT_SESSION_TIMEOUT_SECONDS') ?: (3 * 60 * 60)));
```

**Set custom timeouts:**
```bash
export ADMIN_SESSION_TIMEOUT_SECONDS=43200   # 12 hours
export STUDENT_SESSION_TIMEOUT_SECONDS=7200  # 2 hours
```

---

## Sessions & Authentication

### Admin Authentication Flow

1. User logs in at `/login.php`
2. Username/password checked against `users` table
3. Session token generated and stored in `$_SESSION`
4. Session ID regenerated via `session_regenerate_id(true)`
5. Admin login timestamp recorded: `$_SESSION['admin_login_at']`
6. Session expires after `ADMIN_SESSION_TIMEOUT_SECONDS` (24 hours default)

### Student Authentication Flow

1. Student logs in at `/siswa/login.php`
2. NISN/password checked against `students` table
3. Session token generated randomly
4. If DB has `session_token` column: token stored in DB (enforces single-session per student)
5. Session ID regenerated via `session_regenerate_id(true)`
6. Student login timestamp recorded: `$_SESSION['student_login_at']`
7. Session expires after `STUDENT_SESSION_TIMEOUT_SECONDS` (3 hours default)

### Logout Behavior

**Admin logout** (`/logout.php`):
- Clears `$_SESSION['user']` and `$_SESSION['admin_login_at']`
- Regenerates session ID
- Redirects to login page

**Student logout** (`/siswa/logout.php`):
- Clears `$_SESSION['student']`, `$_SESSION['student_login_at']`, `$_SESSION['student_session_token']`
- Regenerates session ID
- Redirects to home page

### Session Security Headers

All sessions configured globally in `includes/session.php`:

- **HttpOnly**: Prevents JavaScript access to session cookie
- **Secure**: Cookie only sent over HTTPS
- **SameSite=Lax**: Prevents CSRF attacks (compatible with normal navigation)
- **Domain**: Set based on `$base_url` configuration
- **Lifetime**: 0 (session cookie, deleted when browser closes)

---

## CSRF Protection

### Where CSRF Tokens Are Used

CSRF tokens are generated automatically in `includes/session.php`:

```php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

### Admin POST Actions

All admin POST endpoints require CSRF validation:

1. Include hidden input in form:
```html
<form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <!-- other form fields -->
</form>
```

2. Call `require_csrf_valid()` at top of POST handler:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();
    // Process form
}
```

### AJAX Requests

CSRF token can be sent via header for AJAX:

```javascript
fetch('/admin/api.php', {
    method: 'POST',
    headers: {
        'X-CSRF-Token': document.querySelector('input[name="csrf_token"]').value
    },
    body: JSON.stringify({...})
});
```

### Special Cases

- Student assignment submission: Uses `require_csrf_valid()` 
- Public comment submission: Uses `require_csrf_valid()` in `comment_submit.php`
- Public pages without login: CSRF check still recommended for safety

---

## Logging & Monitoring

### What Gets Logged

Sensitive information that **IS logged**:
- Login success/failure events
- User ID (numeric, not username)
- Session ID hash
- IP address
- HTTP headers (X-Forwarded-Proto, User-Agent)

Sensitive information that **IS NOT logged**:
- Passwords (automatically filtered by key name)
- Usernames (removed from logs for privacy)
- Email addresses (if used in future)

### Log File Location

- Path: `/logs/app.log`
- Format: `[TIMESTAMP] [LEVEL] [REQUEST_ID] message | context`
- Protected: `.htaccess` restricts web access to log files

### Viewing Logs

```bash
# View recent logs (last 20 lines)
tail -20 /var/www/html/web-mathdosman/logs/app.log

# View specific event
grep "login_success" /var/www/html/web-mathdosman/logs/app.log

# Live monitoring
tail -f /var/www/html/web-mathdosman/logs/app.log
```

---

## Password Hashing

### Current Implementation

- Algorithm: `PASSWORD_DEFAULT` (bcrypt)
- Cost: Default (automatic, increases with time)
- Validation: Using `password_verify()`

### Example Usage

**Hashing a password:**
```php
$hash = password_hash($plaintext_password, PASSWORD_DEFAULT);
```

**Verifying a password:**
```php
if (password_verify($plaintext_password, $stored_hash)) {
    // Password correct
}
```

---

## SQL Injection Prevention

### Current Implementation

All database queries use **prepared statements with named parameters**:

```php
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u LIMIT 1');
$stmt->execute([':u' => $username]);
$user = $stmt->fetch();
```

**DO NOT use:**
```php
// DANGEROUS - SQL injection vulnerability
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = '$username'");
```

---

## File Upload Security

### Uploaded Files Location

- Gambar editor: `/gambar/` (configured in `admin/uploadeditor.php`)
- Student files: `/siswa/uploads/` (if applicable)

### Best Practices

1. Validate file type (check MIME, not just extension)
2. Rename files to random names
3. Store outside web root if possible
4. Set appropriate permissions (644 for files, 755 for directories)
5. Never execute uploaded files

---

## Deployment Checklist

- [ ] Environment variables configured (DB credentials via `.env` or system env)
- [ ] `.gitignore` is in place (excludes `config/config.php`)
- [ ] HTTPS enabled (for Secure cookie flag)
- [ ] Session directory has correct permissions (for session.save_path)
- [ ] `/logs/` directory writable by web server
- [ ] `/gambar/` directory writable by web server
- [ ] Database preflight checks working (fast failure if MySQL down)
- [ ] CSRF tokens included in all POST forms
- [ ] Password fields use `password_verify()`
- [ ] Logs monitored for security events
- [ ] Database backups automated
