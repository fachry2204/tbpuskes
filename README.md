# Monitoring Pasien TB

Aplikasi Laravel 12 dengan frontend Vue 3 dalam satu root proyek. Sumber Vue berada di `frontend`, sedangkan hasil build-nya disajikan Laravel dari `public`.

## Menjalankan lokal

1. Pastikan Apache/MySQL XAMPP aktif dan database `monitoring_tb` tersedia.
2. Dari folder root ini, jalankan `D:\xampp\php\php.exe artisan migrate` bila diperlukan.
3. Bangun frontend sekali dengan `cd frontend; npm install; npm run build`.
4. Jalankan aplikasi dari folder root dengan `D:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8010`.

Untuk Apache XAMPP, atur document root/virtual host ke `D:\xampp\htdocs\tbPuskes\public`.

## Data pengguna

Data pengguna, password ter-hash, role, dan token login tersimpan di MySQL. Aplikasi tidak menyediakan kredensial atau data pengguna hardcode di frontend maupun seeder.

## API yang tersedia

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`
- `GET /api/v1/patients`, `POST /api/v1/patients`
- `POST /api/v1/me/medication/reports`
- `GET /api/v1/me/control-schedules`
- `GET /api/v1/dashboard/summary`

Saat membuat rencana obat, frekuensi `1` dapat dikirim tanpa jam; sistem menyimpannya sebagai slot `Sekali sehari`. Untuk frekuensi lebih dari satu, kirim `periods: ["Pagi", "Siang", "Malam"]` atau `schedule_times`. Presetnya adalah Pagi 07:00, Siang 13:00, dan Malam 19:00.

Gunakan header `Authorization: Bearer <token>` untuk endpoint terlindungi.
