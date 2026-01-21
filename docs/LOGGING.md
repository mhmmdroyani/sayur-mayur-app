# 📋 Logging System Documentation

## Overview

Sistem logging membantu track dan debug proses-proses penting dalam aplikasi, terutama proses checkout yang melibatkan transaksi finansial.

## Log Files Location

```
sayur_mayur_app/logs/
└── proses_checkout_error.log    # Checkout process logs
```

## Log Rotation & Management

Logs disimpan di folder `logs/` dan **tidak di-commit ke Git** (kecuali untuk documentation).

### Aturan Git (.gitignore)
```
# Logs tidak akan di-commit
/logs/*.log
/logs/*.txt
```

## Log Format

### Proses Checkout Log
File: `logs/proses_checkout_error.log`

**Format:**
```
[DD-Mon-YYYY HH:MM:SS Timezone] MESSAGE
```

**Example:**
```
[21-Jan-2026 08:44:35 Asia/Jakarta] === CHECKOUT REQUEST START ===
[21-Jan-2026 08:44:35 Asia/Jakarta] RAW DATA: {...json data...}
[21-Jan-2026 08:44:35 Asia/Jakarta] === CHECKOUT REQUEST END (SUCCESS) ===
```

## Logged Events

### 1. Checkout Process
Setiap checkout request di-log dengan detail:
- ✅ Request start/end
- ✅ Raw JSON data dari frontend
- ✅ Parsed parameters
- ✅ Shipping calculation
- ✅ Total calculation
- ✅ Success/Error status

### 2. Error Details
Jika ada error:
- PHP Fatal errors
- Stack trace lengkap
- File dan line number
- Function calls path

## Viewing Logs

### Real-time Monitoring
```bash
# Windows PowerShell
Get-Content logs/proses_checkout_error.log -Wait

# Linux/Mac
tail -f logs/proses_checkout_error.log
```

### Search Specific Error
```bash
# Windows PowerShell
Select-String "error" logs/proses_checkout_error.log

# Linux/Mac
grep "error" logs/proses_checkout_error.log
```

## Log Management Tips

### 1. Regular Cleanup
Logs dapat tumbuh besar seiring waktu. Cleanup secara berkala:

```bash
# Windows PowerShell - Clear old logs
Remove-Item logs/*.log

# Linux/Mac - Keep only last 30 days
find logs/ -name "*.log" -mtime +30 -delete
```

### 2. Archive Old Logs
```bash
# Backup logs before deleting
$date = Get-Date -Format "yyyy-MM-dd"
Rename-Item logs/proses_checkout_error.log "logs/proses_checkout_error_$date.log"
```

### 3. Monitor Log Size
```bash
# Check log file size
Get-Item logs/proses_checkout_error.log | Select-Object Length

# Clear if too large (> 10MB)
if ((Get-Item logs/proses_checkout_error.log).Length -gt 10MB) {
    Clear-Content logs/proses_checkout_error.log
}
```

## Debugging dengan Logs

### Checkout Error Examples

**1. Database Connection Error**
```
[DATE] === CHECKOUT REQUEST START ===
[DATE] PHP Fatal error: SQLSTATE[HY000]: General error...
```
→ Check `config/koneksi.php` database credentials

**2. Parameter Mismatch**
```
[DATE] PHP ArgumentCountError: The number of elements in type definition...
```
→ Check `proses_checkout.php` mysqli_stmt_bind_param parameters

**3. Calculation Error**
```
[DATE] DEBUG: subtotal=53000, diskon=5300, total mismatch
```
→ Check shipping calculation di `api/get_shipping.php`

## Production Best Practices

### ✅ Do's
- ✅ Keep logs enabled untuk production
- ✅ Monitor logs secara regular
- ✅ Backup logs sebelum delete
- ✅ Set up log rotation jika heavy traffic
- ✅ Use logs untuk audit trail transaksi

### ❌ Don'ts
- ❌ Commit log files ke Git
- ❌ Store sensitive data di logs (like credit cards)
- ❌ Let logs grow unlimited
- ❌ Delete logs tanpa backup

## Future Enhancement

Untuk production yang lebih robust, pertimbangkan:

1. **Log Rotation** - Automatic rotate logs based on size/date
2. **Log Monitoring** - Real-time alerts untuk errors
3. **Centralized Logging** - Store logs di database atau external service
4. **Log Analysis** - Dashboard untuk visualisasi logs
5. **Performance Logging** - Track response time & queries

## Related Files

- `proses_checkout.php` - Main checkout processor
- `config/koneksi.php` - Database connection
- `api/get_shipping.php` - Shipping calculation API
- `.gitignore` - Git ignore rules untuk logs

---

**Last Updated**: 2026-01-21  
**Timezone**: Asia/Jakarta
