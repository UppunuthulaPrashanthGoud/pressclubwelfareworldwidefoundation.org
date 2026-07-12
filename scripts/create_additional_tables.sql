-- Additional tables for enhanced admin functionality

-- Create donations table if not exists (with additional fields)
ALTER TABLE donations ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'online';
ALTER TABLE donations ADD COLUMN IF NOT EXISTS notes TEXT;

-- Create contact_info table for dynamic contact information
CREATE TABLE IF NOT EXISTS contact_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    info_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    icon VARCHAR(100),
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default contact info
INSERT IGNORE INTO contact_info (info_type, title, content, icon, sort_order) VALUES
('address', 'पता', 'सनातन धर्म जागृति विश्व परिषद\nमुख्य कार्यालय\nलखनऊ, उत्तर प्रदेश', 'fas fa-map-marker-alt', 1),
('phone', 'फोन', '+91-9999999999', 'fas fa-phone', 2),
('email', 'ईमेल', 'info@sanatandharmajagruti.org', 'fas fa-envelope', 3),
('timing', 'समय', 'सोमवार - शनिवार: 9:00 AM - 6:00 PM\nरविवार: बंद', 'fas fa-clock', 4);

-- Create topbar_settings table for dynamic topbar content
CREATE TABLE IF NOT EXISTS topbar_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default topbar settings
INSERT IGNORE INTO topbar_settings (setting_key, setting_value) VALUES
('show_topbar', '1'),
('topbar_text', 'स्वागत है सनातन धर्म जागृति विश्व परिषद में'),
('topbar_phone', '+91-9999999999'),
('topbar_email', 'info@sanatandharmajagruti.org'),
('topbar_background_color', '#291872');

-- Create footer_settings table for dynamic footer content
CREATE TABLE IF NOT EXISTS footer_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL,
    title VARCHAR(255),
    content TEXT,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default footer content
INSERT IGNORE INTO footer_settings (section_name, title, content, sort_order) VALUES
('about', 'हमारे बारे में', 'सनातन धर्म जागृति विश्व परिषद एक धार्मिक और सामाजिक संस्था है जो सनातन धर्म के मूल्यों और परंपराओं को बढ़ावा देने के लिए कार्य करती है।', 1),
('quick_links', 'त्वरित लिंक', 'होम|/\nहमारे बारे में|/about\nसेवाएं|/services\nगैलरी|/gallery\nसंपर्क|/contact', 2),
('services', 'हमारी सेवाएं', 'धार्मिक शिक्षा\nसमाज सेवा\nगौ सेवा\nअन्न दान\nचिकित्सा सहायता', 3);

-- Create blog_posts table for dynamic blog management
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id INT,
    category VARCHAR(100),
    tags TEXT,
    meta_title VARCHAR(255),
    meta_description TEXT,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create certificates table for certificate management
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_name VARCHAR(255) NOT NULL,
    template_image VARCHAR(255) NOT NULL,
    fields JSON, -- Store field positions and properties
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create generated_certificates table
CREATE TABLE IF NOT EXISTS generated_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_id INT NOT NULL,
    user_id INT,
    recipient_name VARCHAR(255) NOT NULL,
    certificate_data JSON, -- Store all certificate data
    certificate_file VARCHAR(255),
    generated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create id_card_templates table
CREATE TABLE IF NOT EXISTS id_card_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(255) NOT NULL,
    template_image VARCHAR(255) NOT NULL,
    fields JSON, -- Store field positions and properties
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create generated_id_cards table
CREATE TABLE IF NOT EXISTS generated_id_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    user_id INT NOT NULL,
    id_card_file VARCHAR(255),
    generated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES id_card_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Add indexes for better performance
CREATE INDEX idx_donations_status ON donations(status);
CREATE INDEX idx_donations_created_at ON donations(created_at);
CREATE INDEX idx_blog_posts_status ON blog_posts(status);
CREATE INDEX idx_blog_posts_published_at ON blog_posts(published_at);
CREATE INDEX idx_certificates_status ON certificates(status);
CREATE INDEX idx_generated_certificates_user ON generated_certificates(user_id);
