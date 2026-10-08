-- Pejabat Daerah Ranau CMS database
-- MySQL 8.0+
-- Import this file with a MySQL account allowed to create databases.

CREATE DATABASE IF NOT EXISTS pdr_cms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;
USE pdr_cms;

-- CMS accounts are staff accounts only. Public visitors do not need accounts.
-- Store password_hash() output from PHP (or an equivalent secure password hash), never a plaintext password.
CREATE TABLE IF NOT EXISTS cms_users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(160) NOT NULL,
  email VARCHAR(254) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_users_email (email),
  KEY idx_cms_users_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_roles (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_key VARCHAR(50) NOT NULL,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_roles_key (role_key)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_permissions (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  permission_key VARCHAR(80) NOT NULL,
  description VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_permissions_key (permission_key)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id SMALLINT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_cms_user_roles_user FOREIGN KEY (user_id)
    REFERENCES cms_users (id) ON DELETE CASCADE,
  CONSTRAINT fk_cms_user_roles_role FOREIGN KEY (role_id)
    REFERENCES cms_roles (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_role_permissions (
  role_id SMALLINT UNSIGNED NOT NULL,
  permission_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_cms_role_permissions_role FOREIGN KEY (role_id)
    REFERENCES cms_roles (id) ON DELETE CASCADE,
  CONSTRAINT fk_cms_role_permissions_permission FOREIGN KEY (permission_id)
    REFERENCES cms_permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_media (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  original_name VARCHAR(255) NOT NULL,
  storage_path VARCHAR(1024) NOT NULL,
  mime_type VARCHAR(127) NOT NULL,
  file_size_bytes BIGINT UNSIGNED NOT NULL,
  alt_text VARCHAR(255) NULL,
  caption VARCHAR(500) NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cms_media_uploaded_by (uploaded_by),
  CONSTRAINT fk_cms_media_uploaded_by FOREIGN KEY (uploaded_by)
    REFERENCES cms_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_pages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  excerpt TEXT NULL,
  body LONGTEXT NOT NULL,
  status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  featured_image_id BIGINT UNSIGNED NULL,
  meta_title VARCHAR(220) NULL,
  meta_description VARCHAR(320) NULL,
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  published_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_pages_slug (slug),
  KEY idx_cms_pages_status_published (status, published_at),
  KEY idx_cms_pages_updated_at (updated_at),
  CONSTRAINT fk_cms_pages_featured_image FOREIGN KEY (featured_image_id)
    REFERENCES cms_media (id) ON DELETE SET NULL,
  CONSTRAINT fk_cms_pages_created_by FOREIGN KEY (created_by)
    REFERENCES cms_users (id) ON DELETE SET NULL,
  CONSTRAINT fk_cms_pages_updated_by FOREIGN KEY (updated_by)
    REFERENCES cms_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_page_revisions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  excerpt TEXT NULL,
  body LONGTEXT NOT NULL,
  saved_by BIGINT UNSIGNED NULL,
  saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cms_page_revisions_page_saved (page_id, saved_at),
  CONSTRAINT fk_cms_page_revisions_page FOREIGN KEY (page_id)
    REFERENCES cms_pages (id) ON DELETE CASCADE,
  CONSTRAINT fk_cms_page_revisions_user FOREIGN KEY (saved_by)
    REFERENCES cms_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_announcements (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  summary TEXT NULL,
  body LONGTEXT NOT NULL,
  status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  cover_media_id BIGINT UNSIGNED NULL,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  published_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_announcements_slug (slug),
  KEY idx_cms_announcements_listing (status, published_at),
  KEY idx_cms_announcements_schedule (starts_at, ends_at),
  CONSTRAINT fk_cms_announcements_cover FOREIGN KEY (cover_media_id)
    REFERENCES cms_media (id) ON DELETE SET NULL,
  CONSTRAINT fk_cms_announcements_created_by FOREIGN KEY (created_by)
    REFERENCES cms_users (id) ON DELETE SET NULL,
  CONSTRAINT fk_cms_announcements_updated_by FOREIGN KEY (updated_by)
    REFERENCES cms_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_gallery_albums (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  description TEXT NULL,
  status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  cover_media_id BIGINT UNSIGNED NULL,
  event_date DATE NULL,
  published_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_gallery_albums_slug (slug),
  KEY idx_cms_gallery_albums_listing (status, event_date),
  CONSTRAINT fk_cms_gallery_albums_cover FOREIGN KEY (cover_media_id)
    REFERENCES cms_media (id) ON DELETE SET NULL,
  CONSTRAINT fk_cms_gallery_albums_created_by FOREIGN KEY (created_by)
    REFERENCES cms_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_gallery_images (
  album_id BIGINT UNSIGNED NOT NULL,
  media_id BIGINT UNSIGNED NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  caption VARCHAR(500) NULL,
  PRIMARY KEY (album_id, media_id),
  KEY idx_cms_gallery_images_order (album_id, sort_order),
  CONSTRAINT fk_cms_gallery_images_album FOREIGN KEY (album_id)
    REFERENCES cms_gallery_albums (id) ON DELETE CASCADE,
  CONSTRAINT fk_cms_gallery_images_media FOREIGN KEY (media_id)
    REFERENCES cms_media (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_menus (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  menu_key VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cms_menus_key (menu_key)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_menu_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  menu_id BIGINT UNSIGNED NOT NULL,
  parent_id BIGINT UNSIGNED NULL,
  label VARCHAR(120) NOT NULL,
  url VARCHAR(1024) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  open_in_new_tab BOOLEAN NOT NULL DEFAULT FALSE,
  is_visible BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (id),
  KEY idx_cms_menu_items_order (menu_id, parent_id, sort_order),
  CONSTRAINT fk_cms_menu_items_menu FOREIGN KEY (menu_id)
    REFERENCES cms_menus (id) ON DELETE CASCADE,
  CONSTRAINT fk_cms_menu_items_parent FOREIGN KEY (parent_id)
    REFERENCES cms_menu_items (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_settings (
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (setting_key),
  CONSTRAINT fk_cms_settings_updated_by FOREIGN KEY (updated_by)
    REFERENCES cms_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cms_audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  details JSON NULL,
  ip_address VARBINARY(16) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cms_audit_logs_user_time (user_id, created_at),
  KEY idx_cms_audit_logs_entity_time (entity_type, entity_id, created_at),
  CONSTRAINT fk_cms_audit_logs_user FOREIGN KEY (user_id)
    REFERENCES cms_users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default CMS roles and capabilities. Create the first staff account separately,
-- then assign its user id to the super_admin role in cms_user_roles.
INSERT IGNORE INTO cms_roles (role_key, name, description) VALUES
  ('super_admin', 'Super Admin', 'Full access to CMS configuration and content.'),
  ('editor', 'Editor', 'Manage pages, announcements, galleries, media and menus.');

INSERT IGNORE INTO cms_permissions (permission_key, description) VALUES
  ('users.manage', 'Create, update, disable and assign staff accounts.'),
  ('roles.manage', 'Manage roles and permissions.'),
  ('pages.manage', 'Create and edit pages.'),
  ('pages.publish', 'Publish, unpublish and archive pages.'),
  ('announcements.manage', 'Create and edit announcements.'),
  ('announcements.publish', 'Publish and archive announcements.'),
  ('gallery.manage', 'Manage gallery albums and images.'),
  ('media.manage', 'Upload and manage media files.'),
  ('menus.manage', 'Manage site navigation menus.'),
  ('settings.manage', 'Manage site-wide settings.'),
  ('audit.view', 'View CMS activity history.');

INSERT IGNORE INTO cms_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM cms_roles r CROSS JOIN cms_permissions p
WHERE r.role_key = 'super_admin';

INSERT IGNORE INTO cms_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM cms_roles r JOIN cms_permissions p
  ON p.permission_key IN (
    'pages.manage', 'pages.publish',
    'announcements.manage', 'announcements.publish',
    'gallery.manage', 'media.manage', 'menus.manage'
  )
WHERE r.role_key = 'editor';

-- First-admin setup example (run after creating an account with a secure hash):
-- INSERT INTO cms_users (full_name, email, password_hash)
-- VALUES ('CMS Administrator', 'admin@example.gov.my', '$2y$...');
-- INSERT INTO cms_user_roles (user_id, role_id)
-- SELECT u.id, r.id FROM cms_users u JOIN cms_roles r ON r.role_key = 'super_admin'
-- WHERE u.email = 'admin@example.gov.my';
