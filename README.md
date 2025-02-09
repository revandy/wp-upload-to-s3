# WP S3 Upload 🚀

![WP S3 Upload](https://raw.githubusercontent.com/revandy/wp-upload-to-s3/refs/heads/main/plugin.JPG)

**WP S3 Upload** adalah plugin WordPress yang secara otomatis mengunggah gambar ke **Amazon S3** saat media diunggah ke WordPress.  
Plugin ini **kompatibel dengan WooCommerce**, mendukung berbagai ukuran gambar, dan memastikan media tidak tersimpan di server lokal.

## 📌 Fitur Utama
✅ **Otomatis unggah gambar ke Amazon S3**  
✅ **Kompatibel dengan WooCommerce (gambar produk dan galeri)**  
✅ **Struktur direktori berdasarkan tanggal** (`uploads/YYYY/MM/DD/`)  
✅ **Menggunakan URL S3 untuk semua media (bukan URL lokal)**  
✅ **Otomatis menghapus file dari S3 saat dihapus dari WordPress**  
✅ **Dukungan pengaturan Bucket, Access Key, Secret Key, dan Region**  

---

## 🛒 Kompatibilitas dengan WooCommerce

![WooCommerce Compatibility](https://raw.githubusercontent.com/revandy/wp-upload-to-s3/refs/heads/main/woocommerce.png)

WP S3 Upload kompatibel dengan **WooCommerce**, dan akan bekerja dengan:
- **Gambar utama produk** (`Product Image`).
- **Gambar galeri produk** (`Product Gallery`).
- **Gambar kategori WooCommerce**.
- **Gambar thumbnail yang digunakan di halaman produk dan katalog**.

### **Cara Menggunakan dengan WooCommerce**
1. **Upload gambar produk seperti biasa di WooCommerce.**
2. **Plugin akan otomatis mengunggah gambar ke S3 dan menyimpan URL di metadata.**
3. **WooCommerce akan menggunakan URL gambar dari S3 untuk menampilkan produk di toko.**

✅ **Semua gambar produk akan diambil langsung dari S3, bukan dari server lokal.**  

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

