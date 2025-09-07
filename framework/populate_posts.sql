-- =====================================================
-- POPULATE CLASSROOM POSTS - Sample Data
-- =====================================================
-- This script populates sample posts for testing the teacher classroom stream
-- Make sure you have existing teachers, classrooms, and students in your database

-- =====================================================
-- CLASSROOM POSTS
-- =====================================================

-- Announcements
INSERT INTO classroom_posts (classroom_id, author_id, content, post_type, attachments, is_pinned, created_at, updated_at) VALUES
(1, 1, 'Welcome to our Computer Science class! This semester we will be covering advanced programming concepts, data structures, and algorithms. Please make sure to check this stream regularly for updates and announcements.', 'announcement', NULL, TRUE, '2024-08-15 08:00:00', '2024-08-15 08:00:00'),

(1, 1, 'Reminder: Tomorrow is our first quiz on Basic Programming Concepts. Please review chapters 1-3 from your textbook. The quiz will be held in Room 201 at 9:00 AM.', 'announcement', NULL, TRUE, '2024-08-20 14:30:00', '2024-08-20 14:30:00'),

(1, 1, 'Class Schedule Update: Our Wednesday class (Aug 28) is moved to Friday (Aug 30) same time due to a faculty meeting. Please adjust your schedules accordingly.', 'announcement', NULL, FALSE, '2024-08-25 10:15:00', '2024-08-25 10:15:00'),

(1, 1, 'Great job everyone on the midterm exams! The average score was 85%. I''m proud of your progress. Keep up the excellent work for the final stretch of the semester.', 'announcement', NULL, FALSE, '2024-09-05 16:45:00', '2024-09-05 16:45:00'),

-- Assignment Links
(1, 1, 'Programming Assignment 1: Implement a Binary Search Tree with the following operations: insert, delete, search, and traversal. Due date: September 15, 2024. Submit your code files through the assignment portal.', 'assignment_link', '{"files": [{"name": "BST_Template.java", "url": "/uploads/assignments/bst_template.java"}, {"name": "Assignment_Guidelines.pdf", "url": "/uploads/assignments/guidelines.pdf"}]}', FALSE, '2024-09-01 09:00:00', '2024-09-01 09:00:00'),

(1, 1, 'Group Project: Design and implement a simple Library Management System. Groups of 3-4 students. Project proposal due: September 20. Final submission: October 15. Check the attached requirements document.', 'assignment_link', '{"files": [{"name": "Project_Requirements.pdf", "url": "/uploads/projects/library_system_req.pdf"}, {"name": "Grading_Rubric.pdf", "url": "/uploads/projects/rubric.pdf"}]}', FALSE, '2024-09-03 11:30:00', '2024-09-03 11:30:00'),

(1, 1, 'Lab Exercise 3: Database Design and SQL Queries. Complete the exercises in the attached workbook. This is an individual assignment. Submit your SQL file by September 18.', 'assignment_link', '{"files": [{"name": "SQL_Workbook.pdf", "url": "/uploads/labs/sql_workbook.pdf"}, {"name": "Sample_Database.sql", "url": "/uploads/labs/sample_db.sql"}]}', FALSE, '2024-09-10 13:15:00', '2024-09-10 13:15:00'),

-- Materials
(1, 1, 'Week 3 Lecture Slides: Advanced Data Structures - Trees and Graphs. Please download and review before our next class. We will have a discussion session based on these materials.', 'material', '{"files": [{"name": "Week3_DataStructures.pptx", "url": "/uploads/materials/week3_slides.pptx"}, {"name": "Trees_Examples.pdf", "url": "/uploads/materials/tree_examples.pdf"}, {"name": "Graph_Algorithms.pdf", "url": "/uploads/materials/graph_algorithms.pdf"}]}', FALSE, '2024-08-28 07:45:00', '2024-08-28 07:45:00'),

(1, 1, 'Recommended Reading: "Clean Code" by Robert Martin, Chapters 2-4. This will help you understand best practices in writing maintainable code. Digital copy available in the library.', 'material', '{"links": [{"title": "Library Digital Access", "url": "https://library.example.edu/cleancode"}, {"title": "Author''s Website", "url": "https://cleancoder.com"}]}', FALSE, '2024-09-02 12:00:00', '2024-09-02 12:00:00'),

(1, 1, 'Video Tutorial: Setting up Development Environment - IDE Configuration and Git Basics. Watch this before our next lab session. Duration: 45 minutes.', 'material', '{"videos": [{"title": "IDE Setup Tutorial", "url": "/uploads/videos/ide_setup.mp4", "duration": "25:30"}, {"title": "Git Basics", "url": "/uploads/videos/git_basics.mp4", "duration": "20:15"}]}', FALSE, '2024-08-18 15:20:00', '2024-08-18 15:20:00'),

-- Discussions
(1, 1, 'Discussion Topic: What are the pros and cons of different sorting algorithms? Share your thoughts on when you would use Quick Sort vs Merge Sort vs Heap Sort. Consider time complexity, space complexity, and practical applications.', 'discussion', NULL, FALSE, '2024-08-30 10:30:00', '2024-08-30 10:30:00'),

(1, 1, 'Open Forum: Industry Trends in Software Development. What technologies are you most excited about? How do you think AI will impact software development in the next 5 years? Share articles, insights, or personal experiences.', 'discussion', NULL, FALSE, '2024-09-07 14:00:00', '2024-09-07 14:00:00'),

(1, 1, 'Study Group Formation: Anyone interested in forming study groups for the upcoming final exam? Please comment below with your preferred study topics and available times. Let''s help each other succeed!', 'discussion', NULL, FALSE, '2024-09-08 18:30:00', '2024-09-08 18:30:00');

-- Add posts for another classroom (classroom_id = 2) if exists
INSERT INTO classroom_posts (classroom_id, author_id, content, post_type, attachments, is_pinned, created_at, updated_at) VALUES
(2, 2, 'Welcome to Advanced Mathematics! This course will cover Calculus III, Linear Algebra, and Differential Equations. Please ensure you have the required textbooks listed in the syllabus.', 'announcement', NULL, TRUE, '2024-08-16 09:00:00', '2024-08-16 09:00:00'),

(2, 2, 'Problem Set 1: Integration Techniques. Solve problems 1-20 from Chapter 7. Show all your work clearly. Due: September 12, 2024.', 'assignment_link', '{"files": [{"name": "Problem_Set_1.pdf", "url": "/uploads/math/problem_set_1.pdf"}]}', FALSE, '2024-09-05 10:00:00', '2024-09-05 10:00:00'),

(2, 2, 'Reference Material: Integration by Parts - Step by Step Guide with Examples. This should help you with this week''s homework assignments.', 'material', '{"files": [{"name": "Integration_Guide.pdf", "url": "/uploads/math/integration_guide.pdf"}]}', FALSE, '2024-09-06 11:15:00', '2024-09-06 11:15:00'),

(2, 2, 'Discussion: Real-world applications of Linear Algebra. Where have you encountered linear algebra concepts outside of mathematics? Share examples from engineering, computer graphics, data science, etc.', 'discussion', NULL, FALSE, '2024-09-04 16:20:00', '2024-09-04 16:20:00');

-- =====================================================
-- POST COMMENTS
-- =====================================================

-- Comments for the first announcement post
INSERT INTO post_comments (post_id, author_id, content, created_at, updated_at) VALUES
(1, 3, 'Thank you for the warm welcome, Professor! I''m excited to learn about advanced programming concepts this semester.', '2024-08-15 08:30:00', '2024-08-15 08:30:00'),
(1, 4, 'Looking forward to the algorithms section! I''ve heard great things about this course.', '2024-08-15 09:15:00', '2024-08-15 09:15:00'),
(1, 5, 'Is there a recommended IDE for this course? I''m currently using VS Code but open to suggestions.', '2024-08-15 10:45:00', '2024-08-15 10:45:00'),
(1, 1, 'Great question! VS Code is perfectly fine. I''ll also demonstrate IntelliJ IDEA during our labs. Use whatever you''re most comfortable with.', '2024-08-15 11:00:00', '2024-08-15 11:00:00');

-- Comments for the quiz announcement
INSERT INTO post_comments (post_id, author_id, content, created_at, updated_at) VALUES
(2, 6, 'Will the quiz be multiple choice or coding problems?', '2024-08-20 15:00:00', '2024-08-20 15:00:00'),
(2, 1, 'It will be a mix - 10 multiple choice questions and 2 short coding problems. 50 minutes total.', '2024-08-20 15:15:00', '2024-08-20 15:15:00'),
(2, 7, 'Can we bring a cheat sheet?', '2024-08-20 16:30:00', '2024-08-20 16:30:00'),
(2, 1, 'Yes, one A4 page, handwritten notes only. Both sides are allowed.', '2024-08-20 16:45:00', '2024-08-20 16:45:00');

-- Comments for the BST assignment
INSERT INTO post_comments (post_id, author_id, content, created_at, updated_at) VALUES
(5, 8, 'Should we implement this in Java or can we use other languages?', '2024-09-01 09:30:00', '2024-09-01 09:30:00'),
(5, 1, 'Java is preferred, but Python or C++ are also acceptable. Just make sure your code is well-commented.', '2024-09-01 10:00:00', '2024-09-01 10:00:00'),
(5, 9, 'Do we need to handle duplicate values in the BST?', '2024-09-01 11:15:00', '2024-09-01 11:15:00'),
(5, 1, 'Good question! Yes, please handle duplicates by storing a count in each node rather than creating duplicate nodes.', '2024-09-01 11:30:00', '2024-09-01 11:30:00');

-- Comments for the sorting algorithms discussion
INSERT INTO post_comments (post_id, author_id, content, created_at, updated_at) VALUES
(11, 10, 'I think Quick Sort is great for general-purpose sorting because of its average O(n log n) performance, but Merge Sort is better when you need guaranteed O(n log n) time complexity.', '2024-08-30 11:00:00', '2024-08-30 11:00:00'),
(11, 11, 'Heap Sort is my favorite for memory-constrained environments since it sorts in-place with O(1) space complexity!', '2024-08-30 12:15:00', '2024-08-30 12:15:00'),
(11, 3, 'What about Radix Sort for integer arrays? It can be O(n) in some cases.', '2024-08-30 13:30:00', '2024-08-30 13:30:00'),
(11, 1, 'Excellent points everyone! @John, you''re right about Radix Sort, but remember it''s limited to specific data types. Great discussion!', '2024-08-30 14:00:00', '2024-08-30 14:00:00');

-- Comments for study group formation
INSERT INTO post_comments (post_id, author_id, content, created_at, updated_at) VALUES
(13, 12, 'I''m interested! I''m free Monday, Wednesday, and Friday evenings. Would like to focus on algorithms and data structures.', '2024-09-08 19:00:00', '2024-09-08 19:00:00'),
(13, 13, 'Count me in! I need help with dynamic programming problems. Available Tuesday and Thursday afternoons.', '2024-09-08 19:15:00', '2024-09-08 19:15:00'),
(13, 14, 'I can help with database design concepts if anyone needs it. Free most weekends.', '2024-09-08 19:30:00', '2024-09-08 19:30:00'),
(13, 15, 'Let''s create a group chat! I''ll send everyone my contact info.', '2024-09-08 19:45:00', '2024-09-08 19:45:00');

-- =====================================================
-- POST LIKES
-- =====================================================

-- Likes for various posts (simulating student engagement)
INSERT INTO post_likes (post_id, user_id, created_at) VALUES
-- Welcome post likes
(1, 3, '2024-08-15 08:35:00'),
(1, 4, '2024-08-15 09:20:00'),
(1, 5, '2024-08-15 10:50:00'),
(1, 6, '2024-08-15 11:30:00'),
(1, 7, '2024-08-15 12:00:00'),
(1, 8, '2024-08-15 14:15:00'),
(1, 9, '2024-08-15 15:30:00'),

-- Quiz announcement likes
(2, 3, '2024-08-20 15:05:00'),
(2, 4, '2024-08-20 15:25:00'),
(2, 6, '2024-08-20 16:00:00'),
(2, 7, '2024-08-20 16:35:00'),

-- BST assignment likes
(5, 8, '2024-09-01 09:35:00'),
(5, 9, '2024-09-01 11:20:00'),
(5, 10, '2024-09-01 13:45:00'),
(5, 11, '2024-09-01 14:30:00'),

-- Lecture slides likes
(8, 3, '2024-08-28 08:00:00'),
(8, 4, '2024-08-28 08:30:00'),
(8, 5, '2024-08-28 09:15:00'),
(8, 6, '2024-08-28 10:00:00'),
(8, 7, '2024-08-28 11:45:00'),

-- Discussion post likes
(11, 10, '2024-08-30 11:05:00'),
(11, 11, '2024-08-30 12:20:00'),
(11, 3, '2024-08-30 13:35:00'),
(11, 12, '2024-08-30 14:30:00'),
(11, 13, '2024-08-30 15:15:00'),

-- Study group post likes
(13, 12, '2024-09-08 19:05:00'),
(13, 13, '2024-09-08 19:20:00'),
(13, 14, '2024-09-08 19:35:00'),
(13, 15, '2024-09-08 19:50:00'),
(13, 3, '2024-09-08 20:15:00'),
(13, 4, '2024-09-08 20:30:00');

-- =====================================================
-- NOTES FOR IMPLEMENTATION
-- =====================================================

/*
IMPORTANT NOTES:

1. ADJUST IDS: Make sure to update the classroom_id and author_id values 
   to match your actual database records:
   - classroom_id: Should match existing classroom IDs
   - author_id: Should match existing teacher user IDs for posts
   - user_id: Should match existing student user IDs for comments/likes

2. CHECK CONSTRAINTS: Ensure your database has the following structure:
   - classroom_posts table with columns: id, classroom_id, author_id, content, post_type, attachments, is_pinned, created_at, updated_at
   - post_comments table with columns: id, post_id, author_id, content, created_at, updated_at
   - post_likes table with columns: id, post_id, user_id, created_at

3. POST TYPES: The script uses these post types (matching your schema):
   - 'announcement': Important class announcements
   - 'assignment_link': Homework and project assignments
   - 'material': Course materials, slides, readings
   - 'discussion': Discussion topics and forums

4. ATTACHMENTS: The JSON format for attachments includes:
   - files: Array of file objects with name and url
   - links: Array of link objects with title and url
   - videos: Array of video objects with title, url, and duration

5. TIMESTAMPS: All timestamps are in 'YYYY-MM-DD HH:MM:SS' format
   You may want to adjust these to recent dates for testing

6. BEFORE RUNNING: 
   - Backup your database
   - Verify you have users with teacher role
   - Verify you have active classrooms
   - Update the IDs in this script to match your data

7. TESTING: After running this script, you should have:
   - Multiple post types in your classroom stream
   - Comments showing teacher-student interaction
   - Likes showing student engagement
   - Pinned announcements at the top
   - Rich content with attachments

RUN THIS SCRIPT: Execute in your MySQL/database client after adjusting the IDs
*/
