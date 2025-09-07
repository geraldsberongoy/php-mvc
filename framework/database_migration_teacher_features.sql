-- Database Migration for Teacher Dashboard Features
-- Run this script to add all required tables and columns

-- 1. Enhance existing classrooms table
ALTER TABLE classrooms 
ADD COLUMN subject VARCHAR(50) DEFAULT 'other',
ADD COLUMN grade_level VARCHAR(10) DEFAULT 'college',
ADD COLUMN settings JSON,
ADD COLUMN status ENUM('active', 'archived') DEFAULT 'active';

-- 2. Stream/Posts System
CREATE TABLE classroom_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classroom_id INT NOT NULL,
    author_id INT NOT NULL,
    content TEXT NOT NULL,
    post_type ENUM('announcement', 'assignment_link', 'material', 'discussion') DEFAULT 'announcement',
    attachments JSON,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES classroom_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES classroom_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Enhance assignments table
ALTER TABLE assignments 
ADD COLUMN assignment_type ENUM('assignment', 'quiz', 'material', 'project') DEFAULT 'assignment',
ADD COLUMN points INT DEFAULT 100,
ADD COLUMN instructions TEXT,
ADD COLUMN attachments JSON,
ADD COLUMN status ENUM('draft', 'published', 'completed', 'archived') DEFAULT 'draft',
ADD COLUMN allow_late_submission BOOLEAN DEFAULT TRUE,
ADD COLUMN late_penalty_percent INT DEFAULT 0;

-- 4. Enhance submissions table
ALTER TABLE submissions 
ADD COLUMN status ENUM('draft', 'submitted', 'graded', 'returned') DEFAULT 'draft',
ADD COLUMN feedback TEXT,
ADD COLUMN attachments JSON,
ADD COLUMN late_submission BOOLEAN DEFAULT FALSE,
ADD COLUMN graded_at TIMESTAMP NULL,
ADD COLUMN graded_by INT NULL,
ADD FOREIGN KEY (graded_by) REFERENCES users(id);

-- 5. Grades System
CREATE TABLE grade_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classroom_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    weight_percentage DECIMAL(5,2) DEFAULT 100.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE
);

CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    points_earned DECIMAL(8,2),
    points_possible DECIMAL(8,2),
    percentage DECIMAL(5,2),
    letter_grade VARCHAR(5),
    feedback TEXT,
    graded_by INT NOT NULL,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_grade (assignment_id, student_id),
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id)
);

-- 6. Classroom Settings
CREATE TABLE classroom_settings (
    classroom_id INT PRIMARY KEY,
    allow_student_posts BOOLEAN DEFAULT TRUE,
    allow_student_comments BOOLEAN DEFAULT TRUE,
    allow_late_submissions BOOLEAN DEFAULT TRUE,
    grade_visibility ENUM('hidden', 'individual', 'all') DEFAULT 'individual',
    grading_scale ENUM('standard', 'percentage', 'points', 'custom') DEFAULT 'standard',
    auto_publish_grades BOOLEAN DEFAULT FALSE,
    enable_plagiarism_detection BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE
);

-- 7. Notification System
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    classroom_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('enrollment', 'assignment', 'grade', 'post', 'comment', 'system') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE SET NULL
);

CREATE TABLE notification_preferences (
    user_id INT PRIMARY KEY,
    email_new_enrollments BOOLEAN DEFAULT TRUE,
    email_assignment_submissions BOOLEAN DEFAULT TRUE,
    email_student_posts BOOLEAN DEFAULT FALSE,
    email_weekly_summary BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 8. Participation Tracking
CREATE TABLE participation_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classroom_id INT NOT NULL,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    participation_type ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
    points DECIMAL(3,1) DEFAULT 1.0,
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- 9. File Management
CREATE TABLE classroom_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classroom_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100),
    file_category ENUM('assignment', 'material', 'submission', 'post_attachment') NOT NULL,
    related_id INT,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- 10. Add joined_at to classroom_students for better tracking
ALTER TABLE classroom_students 
ADD COLUMN status ENUM('active', 'pending', 'removed') DEFAULT 'active',
ADD COLUMN joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update the existing enrolled_at to joined_at
UPDATE classroom_students SET joined_at = enrolled_at WHERE joined_at IS NULL;

-- 11. Create indexes for better performance
CREATE INDEX idx_classroom_posts_classroom ON classroom_posts(classroom_id);
CREATE INDEX idx_classroom_posts_author ON classroom_posts(author_id);
CREATE INDEX idx_post_comments_post ON post_comments(post_id);
CREATE INDEX idx_grades_assignment ON grades(assignment_id);
CREATE INDEX idx_grades_student ON grades(student_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_unread ON notifications(user_id, is_read);
CREATE INDEX idx_participation_classroom_date ON participation_records(classroom_id, date);
CREATE INDEX idx_classroom_files_classroom ON classroom_files(classroom_id);

-- Insert default settings for existing classrooms
INSERT INTO classroom_settings (classroom_id)
SELECT id FROM classrooms 
WHERE id NOT IN (SELECT classroom_id FROM classroom_settings);

-- Insert default notification preferences for existing users
INSERT INTO notification_preferences (user_id)
SELECT id FROM users 
WHERE id NOT IN (SELECT user_id FROM notification_preferences);
