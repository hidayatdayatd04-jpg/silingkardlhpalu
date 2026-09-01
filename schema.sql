CREATE SCHEMA "public";
CREATE TABLE "activity_log" (
	"id" bigserial PRIMARY KEY,
	"user_id" bigint,
	"user_name" varchar(255),
	"event" varchar(255) NOT NULL,
	"auditable_type" varchar(255),
	"auditable_id" bigint,
	"subject_label" varchar(255),
	"module" varchar(255),
	"properties" json,
	"ip_address" varchar(45),
	"user_agent" text,
	"created_at" timestamp
);
CREATE TABLE "ai_provider" (
	"id" bigserial PRIMARY KEY,
	"name" varchar(255) NOT NULL,
	"base_url" varchar(255) NOT NULL,
	"api_key" text NOT NULL,
	"model" varchar(255) NOT NULL,
	"priority" integer DEFAULT 1 NOT NULL,
	"is_active" boolean DEFAULT true NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp,
	"type" varchar(255) DEFAULT 'custom' NOT NULL
);
CREATE TABLE "artikel" (
	"id" bigserial PRIMARY KEY,
	"judul" varchar(255),
	"slug" varchar(255) CONSTRAINT "artikel_slug_unique" UNIQUE,
	"thumbnail" varchar(255),
	"konten" text NOT NULL,
	"tanggal_publish" date,
	"status" varchar(255) DEFAULT 'draft' NOT NULL,
	"user_id" bigint,
	"created_at" timestamp,
	"updated_at" timestamp,
	"komentar_enabled" boolean DEFAULT true NOT NULL
);
CREATE TABLE "artikel_komentar" (
	"id" bigserial PRIMARY KEY,
	"artikel_id" bigint NOT NULL,
	"parent_id" bigint,
	"user_id" bigint,
	"nama" varchar(120),
	"email" varchar(255),
	"body" text NOT NULL,
	"is_hidden" boolean DEFAULT false NOT NULL,
	"is_pinned" boolean DEFAULT false NOT NULL,
	"is_admin" boolean DEFAULT false NOT NULL,
	"ip_address" varchar(45),
	"user_agent" varchar(512),
	"created_at" timestamp,
	"updated_at" timestamp,
	"deleted_at" timestamp
);
CREATE TABLE "artikel_komentar_reaction" (
	"id" bigserial PRIMARY KEY,
	"komentar_id" bigint NOT NULL,
	"type" varchar(10) NOT NULL,
	"fingerprint" varchar(64) NOT NULL,
	"user_id" bigint,
	"ip_address" varchar(45),
	"created_at" timestamp,
	"updated_at" timestamp,
	CONSTRAINT "artikel_komentar_reaction_komentar_id_type_fingerprint_unique" UNIQUE("komentar_id","type","fingerprint")
);
CREATE TABLE "cache" (
	"key" varchar(255) PRIMARY KEY,
	"value" text NOT NULL,
	"expiration" integer NOT NULL
);
CREATE TABLE "cache_lock" (
	"key" varchar(255) PRIMARY KEY,
	"owner" varchar(255) NOT NULL,
	"expiration" integer NOT NULL
);
CREATE TABLE "chat_message" (
	"id" bigserial PRIMARY KEY,
	"conversation_id" varchar(36) NOT NULL,
	"sender_type" varchar(20) NOT NULL,
	"sender_name" varchar(100),
	"message" text NOT NULL,
	"created_at" timestamp
);
CREATE TABLE "data_tanam_pohon" (
	"id" bigserial PRIMARY KEY,
	"nama_penanggung_jawab" varchar(255) NOT NULL,
	"jumlah_pohon" integer NOT NULL,
	"jenis_pohon" varchar(255) NOT NULL,
	"latitude" numeric(10, 8) NOT NULL,
	"longitude" numeric(11, 8) NOT NULL,
	"foto_dokumentasi" json,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "failed_job" (
	"id" bigserial PRIMARY KEY,
	"uuid" varchar(255) NOT NULL CONSTRAINT "failed_job_uuid_unique" UNIQUE,
	"connection" text NOT NULL,
	"queue" text NOT NULL,
	"payload" text NOT NULL,
	"exception" text NOT NULL,
	"failed_at" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
);
CREATE TABLE "gis_data_layer" (
	"id" bigserial PRIMARY KEY,
	"bidang" varchar(255) NOT NULL,
	"nama_layer" varchar(255) NOT NULL,
	"deskripsi" varchar(255),
	"jenis_geometri" varchar(255) DEFAULT 'point' NOT NULL,
	"geojson_features" json NOT NULL,
	"metadata" json,
	"is_visible" boolean DEFAULT true NOT NULL,
	"z_index" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp,
	"deleted_at" timestamp,
	"is_public" boolean DEFAULT true NOT NULL,
	"parent_id" bigint,
	"show_in_filter" boolean DEFAULT true NOT NULL
);
CREATE TABLE "gps_vehicle_cache" (
	"imei" varchar(50) PRIMARY KEY,
	"title" varchar(100) NOT NULL,
	"veh_type" smallint NOT NULL,
	"latitude" numeric(10, 8) NOT NULL,
	"longitude" numeric(11, 8) NOT NULL,
	"speed" integer DEFAULT 0 NOT NULL,
	"angle" integer DEFAULT 0 NOT NULL,
	"acc" smallint NOT NULL,
	"server_time" timestamp NOT NULL,
	"raw_data" json,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "jadwal_armada" (
	"id" bigserial PRIMARY KEY,
	"nama_rute" varchar(255) NOT NULL,
	"hari" varchar(255) NOT NULL,
	"jam" varchar(255) NOT NULL,
	"wilayah_dilalui" text,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "job_batches" (
	"id" varchar(255) PRIMARY KEY,
	"name" varchar(255) NOT NULL,
	"total_jobs" integer NOT NULL,
	"pending_jobs" integer NOT NULL,
	"failed_jobs" integer NOT NULL,
	"failed_job_ids" text NOT NULL,
	"options" text,
	"cancelled_at" integer,
	"created_at" integer NOT NULL,
	"finished_at" integer
);
CREATE TABLE "jobs" (
	"id" bigserial PRIMARY KEY,
	"queue" varchar(255) NOT NULL,
	"payload" text NOT NULL,
	"attempts" smallint NOT NULL,
	"reserved_at" integer,
	"available_at" integer NOT NULL,
	"created_at" integer NOT NULL
);
CREATE TABLE "migration" (
	"id" serial PRIMARY KEY,
	"migration" varchar(255) NOT NULL,
	"batch" integer NOT NULL
);
CREATE TABLE "model_has_permission" (
	"permission_id" bigint,
	"model_type" varchar(255),
	"model_id" bigint,
	CONSTRAINT "model_has_permission_pkey" PRIMARY KEY("permission_id","model_id","model_type")
);
CREATE TABLE "model_has_role" (
	"role_id" bigint,
	"model_type" varchar(255),
	"model_id" bigint,
	CONSTRAINT "model_has_role_pkey" PRIMARY KEY("role_id","model_id","model_type")
);
CREATE TABLE "notification" (
	"id" uuid PRIMARY KEY,
	"type" varchar(255) NOT NULL,
	"notifiable_type" varchar(255) NOT NULL,
	"notifiable_id" bigint NOT NULL,
	"data" text NOT NULL,
	"read_at" timestamp,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "objek_pengawasan" (
	"id" bigserial PRIMARY KEY,
	"nama_perusahaan" varchar(255) NOT NULL,
	"nama_penanggung_jawab" varchar(255) NOT NULL,
	"jenis_usaha" varchar(255),
	"alamat" text NOT NULL,
	"latitude" numeric(10, 7),
	"longitude" numeric(10, 7),
	"no_hp" varchar(255),
	"email" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "objek_pengawasans_dokumen" (
	"id" bigserial PRIMARY KEY,
	"objek_pengawasan_id" bigint NOT NULL,
	"jenis_dokumen" varchar(255) NOT NULL,
	"status_dokumen" varchar(255) DEFAULT 'tidak_ada' NOT NULL,
	"tanggal_berlaku" date,
	"tanggal_kadaluarsa" date,
	"file_path" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp,
	CONSTRAINT "objek_dokumen_unique" UNIQUE("objek_pengawasan_id","jenis_dokumen")
);
CREATE TABLE "pelanggaran_media" (
	"id" bigserial PRIMARY KEY,
	"pelanggaran_id" bigint NOT NULL,
	"path" varchar(255) NOT NULL,
	"tipe" varchar(255) DEFAULT 'foto' NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pelanggarans" (
	"id" bigserial PRIMARY KEY,
	"sidak_id" bigint,
	"jenis_pelanggaran" varchar(255) NOT NULL,
	"pasal_dilanggar" varchar(255),
	"keterangan" text,
	"created_at" timestamp,
	"updated_at" timestamp,
	"sidak_manual" text
);
CREATE TABLE "pengaduan_pengendalian" (
	"id" bigserial PRIMARY KEY,
	"nomor_tiket" varchar(255) NOT NULL CONSTRAINT "pengaduan_pengendalian_nomor_tiket_unique" UNIQUE,
	"nama_pelapor" varchar(255),
	"nomor_hp" varchar(255) NOT NULL,
	"jenis_pengaduan" varchar(255),
	"deskripsi" text NOT NULL,
	"alamat" text,
	"latitude" numeric(10, 8),
	"longitude" numeric(11, 8),
	"status" varchar(255) DEFAULT 'Belum Ditindaklanjuti' NOT NULL,
	"alasan_penolakan" text,
	"catatan_admin" text,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pengaduan_pengendalian_foto" (
	"id" bigserial PRIMARY KEY,
	"pengaduan_pengendalian_id" bigint NOT NULL,
	"path_foto" varchar(255),
	"status" varchar(255) DEFAULT 'pending' NOT NULL,
	"error_message" text,
	"staging_path" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pengaduan_rth" (
	"id" bigserial PRIMARY KEY,
	"nomor_tiket" varchar(255) NOT NULL CONSTRAINT "pengaduan_rth_nomor_tiket_unique" UNIQUE,
	"nama_pelapor" varchar(255),
	"nomor_hp" varchar(255) NOT NULL,
	"jenis_pengaduan" varchar(255),
	"deskripsi" text NOT NULL,
	"alamat" text,
	"latitude" numeric(10, 8),
	"longitude" numeric(11, 8),
	"status" varchar(255) DEFAULT 'Belum Ditinjau' NOT NULL,
	"alasan_penolakan" text,
	"catatan_admin" text,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pengaduan_rth_foto" (
	"id" bigserial PRIMARY KEY,
	"pengaduan_rth_id" bigint NOT NULL,
	"path_foto" varchar(255),
	"status" varchar(255) DEFAULT 'pending' NOT NULL,
	"error_message" text,
	"staging_path" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pengaduan_sampah" (
	"id" bigserial PRIMARY KEY,
	"nomor_tiket" varchar(255) NOT NULL CONSTRAINT "pengaduan_sampah_nomor_tiket_unique" UNIQUE,
	"nama_pelapor" varchar(255),
	"nomor_hp" varchar(255) NOT NULL,
	"jenis_pengaduan" varchar(255),
	"deskripsi" text NOT NULL,
	"alamat" text,
	"latitude" numeric(10, 8),
	"longitude" numeric(11, 8),
	"status" varchar(255) DEFAULT 'Belum Ditindaklanjuti' NOT NULL,
	"alasan_penolakan" text,
	"catatan_admin" text,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pengaduan_sampah_foto" (
	"id" bigserial PRIMARY KEY,
	"pengaduan_sampah_id" bigint NOT NULL,
	"path_foto" varchar(255),
	"status" varchar(255) DEFAULT 'pending' NOT NULL,
	"error_message" text,
	"staging_path" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pengaduan_tata_penataan" (
	"id" bigserial PRIMARY KEY,
	"nomor_tiket" varchar(255) NOT NULL CONSTRAINT "pengaduan_tata_penataan_nomor_tiket_unique" UNIQUE,
	"nama_pelapor" varchar(255) NOT NULL,
	"nomor_hp" varchar(255) NOT NULL,
	"jenis_pengaduan" varchar(255) NOT NULL,
	"nama_terlapor" varchar(255),
	"nama_perusahaan_terlapor" varchar(255),
	"alamat" text NOT NULL,
	"latitude" numeric(10, 7),
	"longitude" numeric(10, 7),
	"deskripsi" text NOT NULL,
	"status" varchar(255) DEFAULT 'menunggu' NOT NULL,
	"catatan_admin" text,
	"assigned_user_id" bigint,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pengaduan_tata_penataan_foto" (
	"id" bigserial PRIMARY KEY,
	"pengaduan_tata_penataan_id" bigint NOT NULL,
	"path_foto" varchar(255),
	"status" varchar(255) DEFAULT 'pending' NOT NULL,
	"error_message" text,
	"staging_path" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "pengajuan_rintek_pertek" (
	"id" bigserial PRIMARY KEY,
	"nomor_pengajuan" varchar(255) NOT NULL CONSTRAINT "pengajuan_rintek_pertek_nomor_pengajuan_unique" UNIQUE,
	"registrasi_usaha_lb3_id" bigint,
	"nama_perusahaan" varchar(255) NOT NULL,
	"surat_permohonan" varchar(255) NOT NULL,
	"dplh_ukl_upl" varchar(255) NOT NULL,
	"nib" varchar(255) NOT NULL,
	"sppl" varchar(255) NOT NULL,
	"denah_tps_lb3" varchar(255) NOT NULL,
	"sop_tanggap_darurat" varchar(255) NOT NULL,
	"status" varchar(255) DEFAULT 'Diajukan' NOT NULL,
	"catatan_verifikasi" text,
	"created_at" timestamp,
	"updated_at" timestamp,
	"nama_penanggung_jawab" varchar(255) DEFAULT '' NOT NULL,
	"nomor_nib" varchar(255) DEFAULT '' NOT NULL,
	"npwp" varchar(30),
	"jenis_usaha" varchar(255) DEFAULT '' NOT NULL,
	"alamat_lengkap" text,
	"nomor_telepon" varchar(20) DEFAULT '' NOT NULL,
	"jenis_pengajuan" varchar(255) DEFAULT '' NOT NULL,
	"keterangan_tambahan" text
);
CREATE TABLE "permission" (
	"id" bigserial PRIMARY KEY,
	"name" varchar(255) NOT NULL,
	"guard_name" varchar(255) NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp,
	CONSTRAINT "permission_name_guard_name_unique" UNIQUE("name","guard_name")
);
CREATE TABLE "permohonan_dokumen" (
	"id" bigserial PRIMARY KEY,
	"permohonan_rekomendasi_id" bigint NOT NULL,
	"path_dokumen" varchar(255) NOT NULL,
	"nama_dokumen" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "permohonan_pinjam_taman" (
	"id" bigserial PRIMARY KEY,
	"nomor_tiket" varchar(20) NOT NULL CONSTRAINT "permohonan_pinjam_taman_nomor_tiket_unique" UNIQUE,
	"nama_pemohon" varchar(255) NOT NULL,
	"nama_kegiatan" varchar(255) NOT NULL,
	"nama_taman" varchar(255),
	"tanggal_kegiatan" timestamp NOT NULL,
	"tanggal_selesai" timestamp,
	"surat_permohonan" varchar(255) NOT NULL,
	"jaminan_kebersihan" boolean DEFAULT false NOT NULL,
	"status" varchar(255) DEFAULT 'Belum Ditinjau' NOT NULL,
	"catatan_admin" text,
	"created_at" timestamp,
	"updated_at" timestamp,
	"nomor_hp" varchar(255)
);
CREATE TABLE "permohonan_rekomendasis" (
	"id" bigserial PRIMARY KEY,
	"nomor_tiket" varchar(255) NOT NULL CONSTRAINT "permohonan_rekomendasis_nomor_tiket_unique" UNIQUE,
	"nama_perusahaan" varchar(255) NOT NULL,
	"nama_pemilik" varchar(255) NOT NULL,
	"npwp" varchar(20) NOT NULL,
	"jenis_usaha" varchar(255) NOT NULL,
	"alamat_lengkap" text NOT NULL,
	"nomor_telepon" varchar(20) NOT NULL,
	"surat_permohonan" varchar(255) NOT NULL,
	"status" varchar(255) DEFAULT 'Belum Ditindaklanjuti' NOT NULL,
	"catatan_verifikasi" text,
	"dokumen_lengkap_terverifikasi" boolean DEFAULT false NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "registrasi_usaha_lb3" (
	"id" bigserial PRIMARY KEY,
	"nomor_registrasi" varchar(255) NOT NULL CONSTRAINT "registrasi_usaha_lb3_nomor_registrasi_unique" UNIQUE,
	"nama_perusahaan" varchar(255) NOT NULL,
	"alamat" text NOT NULL,
	"jenis_lb3" varchar(255),
	"status" varchar(255) DEFAULT 'Diajukan' NOT NULL,
	"catatan" text,
	"created_at" timestamp,
	"updated_at" timestamp,
	"nomor_telepon" varchar(255),
	"jenis_lb3_lainnya" varchar(255)
);
CREATE TABLE "role" (
	"id" bigserial PRIMARY KEY,
	"name" varchar(255) NOT NULL,
	"guard_name" varchar(255) NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp,
	CONSTRAINT "role_name_guard_name_unique" UNIQUE("name","guard_name")
);
CREATE TABLE "role_has_permission" (
	"permission_id" bigint,
	"role_id" bigint,
	CONSTRAINT "role_has_permission_pkey" PRIMARY KEY("permission_id","role_id")
);
CREATE TABLE "sanksi" (
	"id" bigserial PRIMARY KEY,
	"pelanggaran_id" bigint NOT NULL,
	"jenis_sanksi" varchar(255) NOT NULL,
	"batas_waktu_perbaikan" date,
	"status_sanksi" varchar(255) DEFAULT 'diberikan' NOT NULL,
	"surat_path" varchar(255),
	"catatan" text,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "session" (
	"id" varchar(255) PRIMARY KEY,
	"user_id" bigint,
	"ip_address" varchar(45),
	"user_agent" text,
	"payload" text NOT NULL,
	"last_activity" integer NOT NULL
);
CREATE TABLE "setting" (
	"id" bigserial PRIMARY KEY,
	"key" varchar(255) NOT NULL CONSTRAINT "setting_key_unique" UNIQUE,
	"value" json,
	"group" varchar(255) DEFAULT 'general' NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "sidak" (
	"id" bigserial PRIMARY KEY,
	"objek_pengawasan_id" bigint NOT NULL,
	"pengaduan_tata_penataan_id" bigint,
	"tanggal_sidak" date NOT NULL,
	"nama_petugas" varchar(255) NOT NULL,
	"user_id" bigint,
	"hasil" varchar(255),
	"temuan" text,
	"rekomendasi" text,
	"status_tindak_lanjut" varchar(255) DEFAULT 'belum' NOT NULL,
	"is_jadwal" boolean DEFAULT false NOT NULL,
	"catatan_jadwal" text,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "sidak_media" (
	"id" bigserial PRIMARY KEY,
	"sidak_id" bigint NOT NULL,
	"path" varchar(255) NOT NULL,
	"tipe" varchar(255) DEFAULT 'foto' NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "sosialisasi" (
	"id" bigserial PRIMARY KEY,
	"judul" varchar(255) NOT NULL,
	"tanggal" date,
	"materi" text,
	"hasil_evaluasi" text,
	"created_at" timestamp,
	"updated_at" timestamp,
	"jenis_kegiatan" varchar(255) DEFAULT 'sosialisasi' NOT NULL,
	"periode_tw" varchar(255),
	"tahun" varchar(255)
);
CREATE TABLE "sosialisasi_file" (
	"id" bigserial PRIMARY KEY,
	"sosialisasi_id" bigint NOT NULL,
	"path" varchar(255) NOT NULL,
	"tipe" varchar(255) DEFAULT 'materi' NOT NULL,
	"nama" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "sosialisasi_peserta" (
	"id" bigserial PRIMARY KEY,
	"sosialisasi_id" bigint NOT NULL,
	"objek_pengawasan_id" bigint,
	"sertifikat_path" varchar(255),
	"created_at" timestamp,
	"updated_at" timestamp,
	"nama_perusahaan" varchar(255),
	"jenis_usaha" varchar(255),
	"tanggal" date,
	"lokasi" varchar(255),
	"tim_survey" varchar(255),
	"token" varchar(64) CONSTRAINT "sosialisasi_peserta_token_unique" UNIQUE
);
CREATE TABLE "statistik_sampah" (
	"id" bigserial PRIMARY KEY,
	"tanggal" date NOT NULL,
	"volume_ton" numeric(10, 2) NOT NULL,
	"periode" varchar(20) NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp
);
CREATE TABLE "ticket_feedback" (
	"id" bigserial PRIMARY KEY,
	"feedbackable_type" varchar(255) NOT NULL,
	"feedbackable_id" bigint NOT NULL,
	"rating" smallint NOT NULL,
	"komentar" text,
	"created_at" timestamp,
	"updated_at" timestamp,
	CONSTRAINT "ticket_feedback_feedbackable_type_feedbackable_id_unique" UNIQUE("feedbackable_type","feedbackable_id")
);
CREATE TABLE "user" (
	"id" bigserial PRIMARY KEY,
	"name" varchar(255) NOT NULL,
	"username" varchar(255) NOT NULL CONSTRAINT "user_username_unique" UNIQUE,
	"email" varchar(255) CONSTRAINT "user_email_unique" UNIQUE,
	"email_verified_at" timestamp,
	"password" varchar(255) NOT NULL,
	"remember_token" varchar(100),
	"created_at" timestamp,
	"updated_at" timestamp,
	"is_active" boolean DEFAULT true NOT NULL,
	"additional_access" json,
	"photo_path" varchar(255),
	"preferences" json
);
CREATE TABLE "website_visit" (
	"id" bigserial PRIMARY KEY,
	"visit_date" date NOT NULL,
	"ip_address" varchar(45) NOT NULL,
	"session_id" varchar(100) NOT NULL,
	"created_at" timestamp,
	"updated_at" timestamp,
	CONSTRAINT "website_visit_visit_date_ip_address_session_id_unique" UNIQUE("visit_date","ip_address","session_id")
);
CREATE INDEX "activity_log_auditable_type_auditable_id_index" ON "activity_log" ("auditable_type","auditable_id");
CREATE INDEX "activity_log_created_at_index" ON "activity_log" ("created_at");
CREATE INDEX "activity_log_event_index" ON "activity_log" ("event");
CREATE INDEX "activity_log_module_index" ON "activity_log" ("module");
CREATE UNIQUE INDEX "activity_log_pkey" ON "activity_log" ("id");
CREATE INDEX "activity_log_user_id_index" ON "activity_log" ("user_id");
CREATE UNIQUE INDEX "ai_provider_pkey" ON "ai_provider" ("id");
CREATE UNIQUE INDEX "artikel_pkey" ON "artikel" ("id");
CREATE UNIQUE INDEX "artikel_slug_unique" ON "artikel" ("slug");
CREATE INDEX "artikel_komentar_artikel_id_is_hidden_index" ON "artikel_komentar" ("artikel_id","is_hidden");
CREATE INDEX "artikel_komentar_artikel_id_is_pinned_index" ON "artikel_komentar" ("artikel_id","is_pinned");
CREATE INDEX "artikel_komentar_artikel_id_parent_id_index" ON "artikel_komentar" ("artikel_id","parent_id");
CREATE INDEX "artikel_komentar_created_at_index" ON "artikel_komentar" ("created_at");
CREATE UNIQUE INDEX "artikel_komentar_pkey" ON "artikel_komentar" ("id");
CREATE UNIQUE INDEX "artikel_komentar_reaction_komentar_id_type_fingerprint_unique" ON "artikel_komentar_reaction" ("komentar_id","type","fingerprint");
CREATE INDEX "artikel_komentar_reaction_komentar_id_type_index" ON "artikel_komentar_reaction" ("komentar_id","type");
CREATE UNIQUE INDEX "artikel_komentar_reaction_pkey" ON "artikel_komentar_reaction" ("id");
CREATE INDEX "cache_expiration_index" ON "cache" ("expiration");
CREATE UNIQUE INDEX "cache_pkey" ON "cache" ("key");
CREATE INDEX "cache_lock_expiration_index" ON "cache_lock" ("expiration");
CREATE UNIQUE INDEX "cache_lock_pkey" ON "cache_lock" ("key");
CREATE INDEX "chat_message_conversation_id_created_at_index" ON "chat_message" ("conversation_id","created_at");
CREATE INDEX "chat_message_conversation_id_index" ON "chat_message" ("conversation_id");
CREATE INDEX "chat_message_created_at_index" ON "chat_message" ("created_at");
CREATE UNIQUE INDEX "chat_message_pkey" ON "chat_message" ("id");
CREATE INDEX "data_tanam_pohon_created_at_index" ON "data_tanam_pohon" ("created_at");
CREATE UNIQUE INDEX "data_tanam_pohon_pkey" ON "data_tanam_pohon" ("id");
CREATE UNIQUE INDEX "failed_job_pkey" ON "failed_job" ("id");
CREATE UNIQUE INDEX "failed_job_uuid_unique" ON "failed_job" ("uuid");
CREATE INDEX "gis_data_layer_bidang_index" ON "gis_data_layer" ("bidang");
CREATE INDEX "gis_data_layer_bidang_is_visible_index" ON "gis_data_layer" ("bidang","is_visible");
CREATE INDEX "gis_data_layer_parent_id_index" ON "gis_data_layer" ("parent_id");
CREATE UNIQUE INDEX "gis_data_layer_pkey" ON "gis_data_layer" ("id");
CREATE UNIQUE INDEX "gps_vehicle_cache_pkey" ON "gps_vehicle_cache" ("imei");
CREATE UNIQUE INDEX "jadwal_armada_pkey" ON "jadwal_armada" ("id");
CREATE UNIQUE INDEX "job_batches_pkey" ON "job_batches" ("id");
CREATE UNIQUE INDEX "jobs_pkey" ON "jobs" ("id");
CREATE INDEX "jobs_queue_index" ON "jobs" ("queue");
CREATE UNIQUE INDEX "migration_pkey" ON "migration" ("id");
CREATE UNIQUE INDEX "model_has_permission_pkey" ON "model_has_permission" ("permission_id","model_id","model_type");
CREATE INDEX "model_has_permissions_model_id_model_type_index" ON "model_has_permission" ("model_id","model_type");
CREATE UNIQUE INDEX "model_has_role_pkey" ON "model_has_role" ("role_id","model_id","model_type");
CREATE INDEX "model_has_roles_model_id_model_type_index" ON "model_has_role" ("model_id","model_type");
CREATE INDEX "notification_notifiable_type_notifiable_id_index" ON "notification" ("notifiable_type","notifiable_id");
CREATE UNIQUE INDEX "notification_pkey" ON "notification" ("id");
CREATE UNIQUE INDEX "objek_pengawasan_pkey" ON "objek_pengawasan" ("id");
CREATE UNIQUE INDEX "objek_dokumen_unique" ON "objek_pengawasans_dokumen" ("objek_pengawasan_id","jenis_dokumen");
CREATE UNIQUE INDEX "objek_pengawasans_dokumen_pkey" ON "objek_pengawasans_dokumen" ("id");
CREATE UNIQUE INDEX "pelanggaran_media_pkey" ON "pelanggaran_media" ("id");
CREATE INDEX "pelanggarans_created_at_index" ON "pelanggarans" ("created_at");
CREATE UNIQUE INDEX "pelanggarans_pkey" ON "pelanggarans" ("id");
CREATE INDEX "pengaduan_pengendalian_created_at_index" ON "pengaduan_pengendalian" ("created_at");
CREATE INDEX "pengaduan_pengendalian_jenis_pengaduan_index" ON "pengaduan_pengendalian" ("jenis_pengaduan");
CREATE UNIQUE INDEX "pengaduan_pengendalian_nomor_tiket_unique" ON "pengaduan_pengendalian" ("nomor_tiket");
CREATE UNIQUE INDEX "pengaduan_pengendalian_pkey" ON "pengaduan_pengendalian" ("id");
CREATE INDEX "pengaduan_pengendalian_status_index" ON "pengaduan_pengendalian" ("status");
CREATE UNIQUE INDEX "pengaduan_pengendalian_foto_pkey" ON "pengaduan_pengendalian_foto" ("id");
CREATE INDEX "pengaduan_rth_created_at_index" ON "pengaduan_rth" ("created_at");
CREATE INDEX "pengaduan_rth_jenis_pengaduan_index" ON "pengaduan_rth" ("jenis_pengaduan");
CREATE UNIQUE INDEX "pengaduan_rth_nomor_tiket_unique" ON "pengaduan_rth" ("nomor_tiket");
CREATE UNIQUE INDEX "pengaduan_rth_pkey" ON "pengaduan_rth" ("id");
CREATE INDEX "pengaduan_rth_status_index" ON "pengaduan_rth" ("status");
CREATE UNIQUE INDEX "pengaduan_rth_foto_pkey" ON "pengaduan_rth_foto" ("id");
CREATE INDEX "pengaduan_sampah_created_at_index" ON "pengaduan_sampah" ("created_at");
CREATE INDEX "pengaduan_sampah_jenis_pengaduan_index" ON "pengaduan_sampah" ("jenis_pengaduan");
CREATE UNIQUE INDEX "pengaduan_sampah_nomor_tiket_unique" ON "pengaduan_sampah" ("nomor_tiket");
CREATE UNIQUE INDEX "pengaduan_sampah_pkey" ON "pengaduan_sampah" ("id");
CREATE INDEX "pengaduan_sampah_status_index" ON "pengaduan_sampah" ("status");
CREATE UNIQUE INDEX "pengaduan_sampah_foto_pkey" ON "pengaduan_sampah_foto" ("id");
CREATE INDEX "pengaduan_tata_penataan_created_at_index" ON "pengaduan_tata_penataan" ("created_at");
CREATE INDEX "pengaduan_tata_penataan_jenis_pengaduan_index" ON "pengaduan_tata_penataan" ("jenis_pengaduan");
CREATE UNIQUE INDEX "pengaduan_tata_penataan_nomor_tiket_unique" ON "pengaduan_tata_penataan" ("nomor_tiket");
CREATE UNIQUE INDEX "pengaduan_tata_penataan_pkey" ON "pengaduan_tata_penataan" ("id");
CREATE INDEX "pengaduan_tata_penataan_status_index" ON "pengaduan_tata_penataan" ("status");
CREATE UNIQUE INDEX "pengaduan_tata_penataan_foto_pkey" ON "pengaduan_tata_penataan_foto" ("id");
CREATE INDEX "pengajuan_rintek_pertek_created_at_index" ON "pengajuan_rintek_pertek" ("created_at");
CREATE UNIQUE INDEX "pengajuan_rintek_pertek_nomor_pengajuan_unique" ON "pengajuan_rintek_pertek" ("nomor_pengajuan");
CREATE UNIQUE INDEX "pengajuan_rintek_pertek_pkey" ON "pengajuan_rintek_pertek" ("id");
CREATE INDEX "pengajuan_rintek_pertek_status_index" ON "pengajuan_rintek_pertek" ("status");
CREATE UNIQUE INDEX "permission_name_guard_name_unique" ON "permission" ("name","guard_name");
CREATE UNIQUE INDEX "permission_pkey" ON "permission" ("id");
CREATE UNIQUE INDEX "permohonan_dokumen_pkey" ON "permohonan_dokumen" ("id");
CREATE INDEX "permohonan_pinjam_taman_created_at_index" ON "permohonan_pinjam_taman" ("created_at");
CREATE UNIQUE INDEX "permohonan_pinjam_taman_nomor_tiket_unique" ON "permohonan_pinjam_taman" ("nomor_tiket");
CREATE UNIQUE INDEX "permohonan_pinjam_taman_pkey" ON "permohonan_pinjam_taman" ("id");
CREATE INDEX "permohonan_pinjam_taman_status_index" ON "permohonan_pinjam_taman" ("status");
CREATE INDEX "permohonan_rekomendasis_created_at_index" ON "permohonan_rekomendasis" ("created_at");
CREATE UNIQUE INDEX "permohonan_rekomendasis_nomor_tiket_unique" ON "permohonan_rekomendasis" ("nomor_tiket");
CREATE UNIQUE INDEX "permohonan_rekomendasis_pkey" ON "permohonan_rekomendasis" ("id");
CREATE INDEX "permohonan_rekomendasis_status_index" ON "permohonan_rekomendasis" ("status");
CREATE INDEX "registrasi_usaha_lb3_created_at_index" ON "registrasi_usaha_lb3" ("created_at");
CREATE UNIQUE INDEX "registrasi_usaha_lb3_nomor_registrasi_unique" ON "registrasi_usaha_lb3" ("nomor_registrasi");
CREATE UNIQUE INDEX "registrasi_usaha_lb3_pkey" ON "registrasi_usaha_lb3" ("id");
CREATE INDEX "registrasi_usaha_lb3_status_index" ON "registrasi_usaha_lb3" ("status");
CREATE UNIQUE INDEX "role_name_guard_name_unique" ON "role" ("name","guard_name");
CREATE UNIQUE INDEX "role_pkey" ON "role" ("id");
CREATE UNIQUE INDEX "role_has_permission_pkey" ON "role_has_permission" ("permission_id","role_id");
CREATE UNIQUE INDEX "sanksi_pkey" ON "sanksi" ("id");
CREATE INDEX "session_last_activity_index" ON "session" ("last_activity");
CREATE UNIQUE INDEX "session_pkey" ON "session" ("id");
CREATE INDEX "session_user_id_index" ON "session" ("user_id");
CREATE INDEX "setting_group_index" ON "setting" ("group");
CREATE UNIQUE INDEX "setting_key_unique" ON "setting" ("key");
CREATE UNIQUE INDEX "setting_pkey" ON "setting" ("id");
CREATE INDEX "sidak_created_at_index" ON "sidak" ("created_at");
CREATE UNIQUE INDEX "sidak_pkey" ON "sidak" ("id");
CREATE INDEX "sidak_status_tindak_lanjut_index" ON "sidak" ("status_tindak_lanjut");
CREATE UNIQUE INDEX "sidak_media_pkey" ON "sidak_media" ("id");
CREATE INDEX "sosialisasi_created_at_index" ON "sosialisasi" ("created_at");
CREATE INDEX "sosialisasi_jenis_kegiatan_index" ON "sosialisasi" ("jenis_kegiatan");
CREATE UNIQUE INDEX "sosialisasi_pkey" ON "sosialisasi" ("id");
CREATE INDEX "sosialisasi_tanggal_index" ON "sosialisasi" ("tanggal");
CREATE UNIQUE INDEX "sosialisasi_file_pkey" ON "sosialisasi_file" ("id");
CREATE UNIQUE INDEX "sosialisasi_peserta_pkey" ON "sosialisasi_peserta" ("id");
CREATE UNIQUE INDEX "sosialisasi_peserta_token_unique" ON "sosialisasi_peserta" ("token");
CREATE UNIQUE INDEX "statistik_sampah_pkey" ON "statistik_sampah" ("id");
CREATE INDEX "ticket_feedback_feedbackable_type_feedbackable_id_index" ON "ticket_feedback" ("feedbackable_type","feedbackable_id");
CREATE UNIQUE INDEX "ticket_feedback_feedbackable_type_feedbackable_id_unique" ON "ticket_feedback" ("feedbackable_type","feedbackable_id");
CREATE UNIQUE INDEX "ticket_feedback_pkey" ON "ticket_feedback" ("id");
CREATE UNIQUE INDEX "user_email_unique" ON "user" ("email");
CREATE INDEX "user_is_active_index" ON "user" ("is_active");
CREATE UNIQUE INDEX "user_pkey" ON "user" ("id");
CREATE UNIQUE INDEX "user_username_unique" ON "user" ("username");
CREATE UNIQUE INDEX "website_visit_pkey" ON "website_visit" ("id");
CREATE INDEX "website_visit_visit_date_index" ON "website_visit" ("visit_date");
CREATE UNIQUE INDEX "website_visit_visit_date_ip_address_session_id_unique" ON "website_visit" ("visit_date","ip_address","session_id");
ALTER TABLE "activity_log" ADD CONSTRAINT "activity_log_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "user"("id") ON DELETE SET NULL;
ALTER TABLE "artikel" ADD CONSTRAINT "artikel_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "user"("id") ON DELETE SET NULL;
ALTER TABLE "artikel_komentar" ADD CONSTRAINT "artikel_komentar_artikel_id_foreign" FOREIGN KEY ("artikel_id") REFERENCES "artikel"("id") ON DELETE CASCADE;
ALTER TABLE "artikel_komentar" ADD CONSTRAINT "artikel_komentar_parent_id_foreign" FOREIGN KEY ("parent_id") REFERENCES "artikel_komentar"("id") ON DELETE CASCADE;
ALTER TABLE "artikel_komentar" ADD CONSTRAINT "artikel_komentar_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "user"("id") ON DELETE SET NULL;
ALTER TABLE "artikel_komentar_reaction" ADD CONSTRAINT "artikel_komentar_reaction_komentar_id_foreign" FOREIGN KEY ("komentar_id") REFERENCES "artikel_komentar"("id") ON DELETE CASCADE;
ALTER TABLE "artikel_komentar_reaction" ADD CONSTRAINT "artikel_komentar_reaction_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "user"("id") ON DELETE SET NULL;
ALTER TABLE "gis_data_layer" ADD CONSTRAINT "gis_data_layer_parent_id_foreign" FOREIGN KEY ("parent_id") REFERENCES "gis_data_layer"("id") ON DELETE SET NULL;
ALTER TABLE "model_has_permission" ADD CONSTRAINT "model_has_permission_permission_id_foreign" FOREIGN KEY ("permission_id") REFERENCES "permission"("id") ON DELETE CASCADE;
ALTER TABLE "model_has_role" ADD CONSTRAINT "model_has_role_role_id_foreign" FOREIGN KEY ("role_id") REFERENCES "role"("id") ON DELETE CASCADE;
ALTER TABLE "objek_pengawasans_dokumen" ADD CONSTRAINT "objek_pengawasans_dokumen_objek_pengawasan_id_foreign" FOREIGN KEY ("objek_pengawasan_id") REFERENCES "objek_pengawasan"("id") ON DELETE CASCADE;
ALTER TABLE "pelanggaran_media" ADD CONSTRAINT "pelanggaran_media_pelanggaran_id_foreign" FOREIGN KEY ("pelanggaran_id") REFERENCES "pelanggarans"("id") ON DELETE CASCADE;
ALTER TABLE "pelanggarans" ADD CONSTRAINT "pelanggarans_sidak_id_foreign" FOREIGN KEY ("sidak_id") REFERENCES "sidak"("id") ON DELETE SET NULL;
ALTER TABLE "pengaduan_pengendalian_foto" ADD CONSTRAINT "pengaduan_pengendalian_foto_pengaduan_pengendalian_id_foreign" FOREIGN KEY ("pengaduan_pengendalian_id") REFERENCES "pengaduan_pengendalian"("id") ON DELETE CASCADE;
ALTER TABLE "pengaduan_rth_foto" ADD CONSTRAINT "pengaduan_rth_foto_pengaduan_rth_id_foreign" FOREIGN KEY ("pengaduan_rth_id") REFERENCES "pengaduan_rth"("id") ON DELETE CASCADE;
ALTER TABLE "pengaduan_sampah_foto" ADD CONSTRAINT "pengaduan_sampah_foto_pengaduan_sampah_id_foreign" FOREIGN KEY ("pengaduan_sampah_id") REFERENCES "pengaduan_sampah"("id") ON DELETE CASCADE;
ALTER TABLE "pengaduan_tata_penataan" ADD CONSTRAINT "pengaduan_tata_penataan_assigned_user_id_foreign" FOREIGN KEY ("assigned_user_id") REFERENCES "user"("id") ON DELETE SET NULL;
ALTER TABLE "pengaduan_tata_penataan_foto" ADD CONSTRAINT "pengaduan_tata_penataan_foto_pengaduan_tata_penataan_id_foreign" FOREIGN KEY ("pengaduan_tata_penataan_id") REFERENCES "pengaduan_tata_penataan"("id") ON DELETE CASCADE;
ALTER TABLE "pengajuan_rintek_pertek" ADD CONSTRAINT "pengajuan_rintek_pertek_registrasi_usaha_lb3_id_foreign" FOREIGN KEY ("registrasi_usaha_lb3_id") REFERENCES "registrasi_usaha_lb3"("id") ON DELETE SET NULL;
ALTER TABLE "permohonan_dokumen" ADD CONSTRAINT "permohonan_dokumen_permohonan_rekomendasi_id_foreign" FOREIGN KEY ("permohonan_rekomendasi_id") REFERENCES "permohonan_rekomendasis"("id") ON DELETE CASCADE;
ALTER TABLE "role_has_permission" ADD CONSTRAINT "role_has_permission_permission_id_foreign" FOREIGN KEY ("permission_id") REFERENCES "permission"("id") ON DELETE CASCADE;
ALTER TABLE "role_has_permission" ADD CONSTRAINT "role_has_permission_role_id_foreign" FOREIGN KEY ("role_id") REFERENCES "role"("id") ON DELETE CASCADE;
ALTER TABLE "sanksi" ADD CONSTRAINT "sanksi_pelanggaran_id_foreign" FOREIGN KEY ("pelanggaran_id") REFERENCES "pelanggarans"("id") ON DELETE CASCADE;
ALTER TABLE "sidak" ADD CONSTRAINT "sidak_objek_pengawasan_id_foreign" FOREIGN KEY ("objek_pengawasan_id") REFERENCES "objek_pengawasan"("id") ON DELETE CASCADE;
ALTER TABLE "sidak" ADD CONSTRAINT "sidak_pengaduan_tata_penataan_id_fk" FOREIGN KEY ("pengaduan_tata_penataan_id") REFERENCES "pengaduan_tata_penataan"("id") ON DELETE SET NULL;
ALTER TABLE "sidak" ADD CONSTRAINT "sidak_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "user"("id") ON DELETE SET NULL;
ALTER TABLE "sidak_media" ADD CONSTRAINT "sidak_media_sidak_id_foreign" FOREIGN KEY ("sidak_id") REFERENCES "sidak"("id") ON DELETE CASCADE;
ALTER TABLE "sosialisasi_file" ADD CONSTRAINT "sosialisasi_file_sosialisasi_id_foreign" FOREIGN KEY ("sosialisasi_id") REFERENCES "sosialisasi"("id") ON DELETE CASCADE;
ALTER TABLE "sosialisasi_peserta" ADD CONSTRAINT "sosialisasi_peserta_objek_pengawasan_id_foreign" FOREIGN KEY ("objek_pengawasan_id") REFERENCES "objek_pengawasan"("id") ON DELETE CASCADE;
ALTER TABLE "sosialisasi_peserta" ADD CONSTRAINT "sosialisasi_peserta_sosialisasi_id_foreign" FOREIGN KEY ("sosialisasi_id") REFERENCES "sosialisasi"("id") ON DELETE CASCADE;