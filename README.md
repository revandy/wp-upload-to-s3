# WP S3 Upload

WP S3 Upload adalah plugin WordPress yang memungkinkan Anda mengunggah gambar ke Amazon S3 secara otomatis saat mengunggah media ke WordPress.

## 📌 Fitur
✅ Mengunggah file gambar ke **Amazon S3** secara otomatis  
✅ **Kompatibel dengan WooCommerce** (gambar produk diunggah ke S3)  
✅ Menggunakan **struktur direktori berdasarkan tanggal** (`uploads/YYYY/MM/DD/`)  
✅ Mendukung berbagai ukuran thumbnail (150x150, 300x300, dll.)  
✅ Menyimpan URL file langsung ke metadata WordPress  
✅ Menghapus file dari S3 saat media dihapus dari WordPress  
✅ Mendukung pengaturan **Bucket, Access Key, Secret Key, dan Region**  

---

## 📥 Instalasi

### **1. Unggah Plugin ke WordPress**
1. **Download atau clone repository ini.**
2. **Upload folder `wp-s3-upload` ke direktori:**  
3. **Aktifkan plugin** melalui menu **Plugins** di WordPress Admin.

### **2. Instal Dependensi Composer**
1. Buka terminal dan masuk ke direktori plugin:
```bash
cd wp-content/plugins/wp-s3-upload
composer install


Setelah plugin diaktifkan, buka Settings → WP S3 Upload dan masukkan konfigurasi berikut:

Opsi	Deskripsi
S3 Bucket	Nama bucket S3 Anda (misalnya: my-wordpress-bucket)
AWS Access Key	Access Key ID dari akun AWS
AWS Secret Key	Secret Key dari akun AWS
AWS S3 Region	Wilayah bucket S3 (misalnya: ap-southeast-1 untuk Singapura)
Menemukan Access Key dan Secret Key
Masuk ke AWS IAM Console.
Pilih Users dan buat atau pilih pengguna dengan akses ke S3.
Tambahkan kebijakan akses ke S3.
Salin Access Key ID dan Secret Key dari pengguna tersebut.


🚀 Cara Kerja
Upload file ke Media Library WordPress
→ Plugin secara otomatis mengunggah file ke S3 dengan struktur /uploads/YYYY/MM/DD/
WordPress menyimpan URL file dari S3
→ Semua media diambil dari S3, bukan dari server lokal.
Saat file dihapus dari WordPress
→ Plugin juga menghapus file dari S3.


🛠️ Konfigurasi Bucket Policy
Jika ingin semua file diakses secara publik, tambahkan Bucket Policy berikut di AWS S3 Console:

{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::your-bucket-name/uploads/*"
        }
    ]
}


---

