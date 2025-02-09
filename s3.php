<?php
/**
 * Plugin Name: WP S3 Upload
 * Description: Mengunggah gambar ke Amazon S3 saat diunggah ke media library.
 * Version: 1.1
 * Author: REVANDY SATRIA
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
use Aws\S3\S3Client; // ✅ Pindahkan ke sini
use Ramsey\Uuid\Uuid; // ✅ Tambahkan ini agar PHP mengenali Uuid

// Tambahkan halaman pengaturan
function wp_s3_upload_menu() {
    add_options_page('WP S3 Upload', 'WP S3 Upload', 'manage_options', 'wp-s3-upload', 'wp_s3_upload_settings_page');
}
add_action('admin_menu', 'wp_s3_upload_menu');

function wp_s3_upload_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP S3 Upload Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_s3_upload_options_group');
            do_settings_sections('wp_s3_upload');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function wp_s3_upload_register_settings() {
    register_setting('wp_s3_upload_options_group', 'wp_s3_upload_bucket');
    register_setting('wp_s3_upload_options_group', 'wp_s3_upload_access_key');
    register_setting('wp_s3_upload_options_group', 'wp_s3_upload_secret_key');
    register_setting('wp_s3_upload_options_group', 'wp_s3_upload_region'); // ✅ Tambahkan opsi region

    add_settings_section('wp_s3_upload_section', 'S3 Configuration', null, 'wp_s3_upload');

    add_settings_field('wp_s3_upload_bucket', 'S3 Bucket', 'wp_s3_upload_bucket_callback', 'wp_s3_upload', 'wp_s3_upload_section');
    add_settings_field('wp_s3_upload_access_key', 'AWS Access Key', 'wp_s3_upload_access_key_callback', 'wp_s3_upload', 'wp_s3_upload_section');
    add_settings_field('wp_s3_upload_secret_key', 'AWS Secret Key', 'wp_s3_upload_secret_key_callback', 'wp_s3_upload', 'wp_s3_upload_section');
    add_settings_field('wp_s3_upload_region', 'AWS S3 Region', 'wp_s3_upload_region_callback', 'wp_s3_upload', 'wp_s3_upload_section'); // ✅ Tambahkan region
}
add_action('admin_init', 'wp_s3_upload_register_settings');

function wp_s3_upload_bucket_callback() {
    $value = get_option('wp_s3_upload_bucket');
    echo "<input type='text' name='wp_s3_upload_bucket' value='$value' />";
}

function wp_s3_upload_access_key_callback() {
    $value = get_option('wp_s3_upload_access_key');
    echo "<input type='text' name='wp_s3_upload_access_key' value='$value' />";
}

function wp_s3_upload_secret_key_callback() {
    $value = get_option('wp_s3_upload_secret_key');
    echo "<input type='password' name='wp_s3_upload_secret_key' value='$value' />";
}

function wp_s3_upload_region_callback() { // ✅ Fungsi untuk menampilkan input region
    $value = get_option('wp_s3_upload_region', 'ap-southeast-1'); // Default ke 'us-east-1' jika kosong
    echo "<input type='text' name='wp_s3_upload_region' value='$value' />";
}

// Fungsi upload ke S3
// Fungsi upload ke S3
function wp_s3_upload_to_s3($metadata, $attachment_id) {
    error_log('Trigger upload to S3 for attachment ID: ' . $attachment_id);

    $bucket = get_option('wp_s3_upload_bucket');
    $accessKey = get_option('wp_s3_upload_access_key');
    $secretKey = get_option('wp_s3_upload_secret_key');
    $region = get_option('wp_s3_upload_region');

    if (!$bucket || !$accessKey || !$secretKey) {
        error_log('S3 configuration missing!');
        return $metadata;
    }

    try {
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => $region,
            'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ],
        ]);

        // 1. Dapatkan file utama
        $file_path = get_attached_file($attachment_id);
        if (!$file_path || !file_exists($file_path)) {
            error_log('File does not exist: ' . $file_path);
            return $metadata;
        }

        // 2. Buat nama file random & struktur folder berdasarkan tanggal
        $date_path = date('Y/m/d');
        $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $random_name = Uuid::uuid4()->toString() . '.' . $file_ext;
        $s3_key = "uploads/{$date_path}/{$random_name}";
        

        error_log('Uploading original file to S3: ' . $file_path);

        // 3. Upload file utama ke S3
        $s3->putObject([
            'Bucket'     => $bucket,
            'Key'        => $s3_key,
            'SourceFile' => $file_path,
            'ContentType' => get_post_mime_type($attachment_id),
        ]);

        // 4. Simpan URL ke metadata
        $s3_url = "https://$bucket.s3.$region.amazonaws.com/$s3_key";
        update_post_meta($attachment_id, '_s3_url', $s3_url);
        update_post_meta($attachment_id, '_s3_key', $s3_key); // Simpan path S3 untuk penghapusan

        // 5. Upload semua thumbnail ke S3
        if (!empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $data) {
                $thumb_path = dirname($file_path) . '/' . $data['file'];
                if (file_exists($thumb_path)) {
                    // ❌ Gunakan nama asli thumbnail dari WordPress (bukan UUID)
                    $thumb_s3_key = "uploads/{$date_path}/{$data['file']}";
        
                    error_log('Uploading thumbnail: ' . $data['file'] . ' as ' . $data['file']);
        
                    $s3->putObject([
                        'Bucket'     => $bucket,
                        'Key'        => $thumb_s3_key,
                        'SourceFile' => $thumb_path,
                        'ContentType' => $data['mime-type']
                    ]);
        
                    // Simpan URL thumbnail di metadata
                    update_post_meta($attachment_id, '_s3_thumb_' . $size, $thumb_s3_key);
                    $metadata['sizes'][$size]['s3_url'] = "https://$bucket.s3.$region.amazonaws.com/" . $thumb_s3_key;
                }
            }
        }

        // 6. Hapus file lokal setelah sukses diunggah ke S3
        unlink($file_path);
        error_log('Local file deleted: ' . $file_path);

    } catch (Exception $e) {
        error_log('S3 Upload Error: ' . $e->getMessage());
    }

    return $metadata;
}


add_filter('wp_generate_attachment_metadata', 'wp_s3_upload_to_s3', 10, 2);

// Pastikan WordPress menggunakan URL S3 untuk gambar
function replace_media_with_s3_url($url, $post_id) {
    $s3_url = get_post_meta($post_id, '_s3_url', true);
    return !empty($s3_url) ? $s3_url : $url;
}
add_filter('wp_get_attachment_url', 'replace_media_with_s3_url', 10, 2);
// Pastikan WordPress menggunakan URL S3 untuk thumbnail
function wp_s3_replace_thumbnail_urls($data, $attachment_id) {
    $region = get_option('wp_s3_upload_region');
    if (!empty($data['sizes'])) {
        foreach ($data['sizes'] as $size => $info) {
            $s3_url = get_post_meta($attachment_id, '_s3_thumb_' . $size, true);
            if (!empty($s3_url)) {
                // Paksa WordPress mengganti URL thumbnail dengan path di S3
                $data['sizes'][$size]['url'] = "https://japanwholesaleid.s3.$region.amazonaws.com/" . $s3_url;
            }
        }
    }
    return $data;
}
add_filter('wp_get_attachment_metadata', 'wp_s3_replace_thumbnail_urls', 10, 2);

// Fungsi untuk menghapus file dari S3 saat dihapus dari WordPress
function wp_s3_delete_from_s3($post_id) {
    $bucket = get_option('wp_s3_upload_bucket');
    $accessKey = get_option('wp_s3_upload_access_key');
    $secretKey = get_option('wp_s3_upload_secret_key');
    $region = get_option('wp_s3_upload_region');

    if (!$bucket || !$accessKey || !$secretKey) {
        return;
    }

    try {
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => $region,
            'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ],
        ]);

        // 1. Hapus file utama dari S3
        $s3_key = get_post_meta($post_id, '_s3_key', true);
        if (!empty($s3_key)) {
            $s3->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $s3_key,
            ]);
            delete_post_meta($post_id, '_s3_key');
            delete_post_meta($post_id, '_s3_url');
            error_log("Deleted from S3: $s3_key");
        }

        // 2. Hapus semua thumbnail
        $metadata = wp_get_attachment_metadata($post_id);
        if (!empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $info) {
                $thumb_s3_key = get_post_meta($post_id, '_s3_thumb_' . $size, true);
                if (!empty($thumb_s3_key)) {
                    $s3->deleteObject([
                        'Bucket' => $bucket,
                        'Key'    => $thumb_s3_key,
                    ]);
                    delete_post_meta($post_id, '_s3_thumb_' . $size);
                    error_log("Deleted thumbnail from S3: $thumb_s3_key");
                }
            }
        }

    } catch (Exception $e) {
        error_log('S3 Delete Error: ' . $e->getMessage());
    }
}

add_action('delete_attachment', 'wp_s3_delete_from_s3');