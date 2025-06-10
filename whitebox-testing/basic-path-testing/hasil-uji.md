| Bagian | Path | Code | Jalur |
|--------|------|------|-------|
| A1 | Username tidak ditemukan | ![A1]() | 1. Cek `$result->num_rows > 0`<br>2. Jika `true`, lanjutkan cek password <br>3. Jika `false`, tampilkan error|
| A2 | Username ditemukan, password salah | ![A2]() | 1. Cek `password_verify($password, $row['password'])`<br>2. Jika `true`, lanjutkan cek verifikasi email <br>3. Jika `false`, tampilkan error |
| A3 | Username & password benar, belum diverifikasi | ![A3]() | 1. Cek `$row['is_verified']`<br>2. Jika `false`, tampilkan error |
| A4 | Username & password benar, sudah diverifikasi | ![A4]() | 1. Cek `$row['is_verified']`<br>2. Jika `true`, set session dan redirect ke `dashboard.php` |
