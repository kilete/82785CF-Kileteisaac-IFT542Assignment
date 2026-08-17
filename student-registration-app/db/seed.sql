-- ============================================================
-- Seed data - FICTITIOUS test accounts only, per assignment rules
-- Default password for ALL seeded accounts: Passw0rd!123
-- (hash below is Argon2id for that string - change after first login)
-- ============================================================

USE student_registration;

-- Password hash generated with password_hash('Passw0rd!123', PASSWORD_ARGON2ID)
-- Run tools/generate_hash.php if you want to regenerate this.
INSERT INTO users (matric_no, full_name, email, password_hash, role) VALUES
('2020/1/00001CS', 'Amina Yusuf',   'amina.test@example.local',  '$argon2id$v=19$m=65536,t=4,p=1$REPLACE_WITH_GENERATED_HASH', 'student'),
('2020/1/00002CS', 'Chinedu Okafor','chinedu.test@example.local','$argon2id$v=19$m=65536,t=4,p=1$REPLACE_WITH_GENERATED_HASH', 'student'),
(NULL,             'System Admin',  'admin.test@example.local',  '$argon2id$v=19$m=65536,t=4,p=1$REPLACE_WITH_GENERATED_HASH', 'admin');

INSERT INTO courses (course_code, title, units, capacity) VALUES
('IFT542', 'Web Application Security', 3, 60),
('IFT501', 'Advanced Database Systems', 3, 60),
('IFT510', 'Network Security', 3, 50),
('CPT411', 'Software Engineering', 3, 80);
