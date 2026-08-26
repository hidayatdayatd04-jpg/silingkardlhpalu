fn main() {
    // Daftarkan perintah aplikasi sendiri ke ACL Tauri. Tanpa manifest ini,
    // halaman remote tidak pernah dapat menggunakan kontrol title bar, bahkan
    // ketika domainnya sudah dibatasi dalam capability.
    tauri_build::try_build(tauri_build::Attributes::new().app_manifest(
        tauri_build::AppManifest::new().commands(&[
            "check_connection",
            "admin_target",
            "desktop_window_control",
        ]),
    ))
    .expect("gagal membangun ACL aplikasi desktop");
}
