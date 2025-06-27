# Social Media Site

A modern social media platform built with PHP and MySQL, featuring user authentication, friend management, messaging, and post sharing.

## Features

### 🔐 Security Improvements
- **Password Hashing**: All passwords are now securely hashed using PHP's `password_hash()`
- **CSRF Protection**: Cross-Site Request Forgery protection on all forms
- **SQL Injection Prevention**: All database queries use prepared statements
- **Session Security**: Secure session configuration with timeout handling
- **Input Validation**: Comprehensive input validation and sanitization
- **Security Headers**: XSS protection and content type headers

### 👥 User Management
- **User Registration**: Secure registration with email/phone validation
- **User Login**: Email or phone number login with password verification
- **Profile Management**: Profile picture upload with validation
- **Session Management**: Automatic session timeout (30 minutes)

### 👫 Friend System
- **Friend Search**: Search for users by name
- **Friend Requests**: Send, accept, reject, and block friend requests
- **Friend Management**: View and manage your friends list
- **Real-time Updates**: AJAX-powered friend request handling

### 💬 Messaging System
- **Real-time Chat**: Instant messaging between friends
- **Message History**: View conversation history
- **Security**: Only friends can message each other
- **Auto-scroll**: Messages automatically scroll to latest

### 📝 Posting System
- **Create Posts**: Share your thoughts with friends
- **Feed Display**: View posts from friends in chronological order
- **Pagination**: Navigate through posts with page system
- **Notifications**: Friends get notified of new posts

### 🔔 Notification System
- **Real-time Notifications**: New message and post notifications
- **Auto-refresh**: Notifications update automatically
- **Mark as Read**: Notifications are marked as seen when viewed

### 🎨 User Interface
- **Modern Design**: Clean, responsive interface
- **Mobile Friendly**: Responsive design for mobile devices
- **Loading States**: Visual feedback during operations
- **Error Handling**: User-friendly error messages
- **Success Feedback**: Clear success confirmations

## Database Structure

### Tables
- **users**: User accounts and profiles
- **friends**: Friend relationships and requests
- **posts**: User posts and content
- **messages**: Chat messages between users
- **notifications**: System notifications

### Indexes
- Optimized database indexes for better performance
- Foreign key constraints for data integrity
- Unique constraints to prevent duplicates

## Installation

1. **Database Setup**:
   ```sql
   -- Import the db.sql file to create the database and tables
   mysql -u root -p < db.sql
   ```

2. **File Structure**:
   ```
   task2/
   ├── assets/uploads/     # Profile pictures and uploads
   ├── config.php         # Database and security configuration
   ├── dashboard.php      # Main dashboard interface
   ├── login.php          # User login
   ├── register.php       # User registration
   ├── Friend_Request.php # Friend request management
   ├── search_friend.php  # Friend search functionality
   ├── post_status.php    # Post creation and management
   ├── send_message.php   # Message sending
   ├── fetch_messages.php # Message retrieval
   ├── get_friends.php    # Friends list
   ├── notification.php   # Notification system
   ├── update_profile.php # Profile picture updates
   └── logout.php         # User logout
   ```

3. **Configuration**:
   - Update database credentials in `config.php`
   - Ensure `assets/uploads/` directory is writable
   - Create a `default.png` file in `assets/uploads/` for default profile pictures

4. **Security Notes**:
   - Change default database credentials
   - Enable HTTPS in production
   - Set appropriate file permissions
   - Regular security updates

## Usage

1. **Registration**: Create a new account with email/phone and password
2. **Login**: Access your account using email/phone and password
3. **Profile**: Upload a profile picture and manage your account
4. **Friends**: Search for and add friends
5. **Posts**: Share your thoughts and see friends' posts
6. **Messages**: Chat with your friends in real-time
7. **Notifications**: Stay updated with friend activities

## Technical Details

### Security Features
- Password hashing with `password_hash()` and `password_verify()`
- CSRF token generation and validation
- Prepared statements for all database queries
- Input sanitization with `htmlspecialchars()`
- Session timeout and security headers

### Performance Optimizations
- Database indexes for faster queries
- AJAX for real-time updates
- Efficient pagination system
- Optimized image handling

### Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- Progressive enhancement

---

## 📸 ScreenShot
![Screenshot 2025-06-27 113737](https://github.com/user-attachments/assets/d2940425-f60f-44d5-aecc-da137c5ab43a)
![Screenshot 2025-06-27 113751](https://github.com/user-attachments/assets/3f28fa7e-9dec-4de3-be3c-056e566ef840)
![Screenshot 2025-06-27 113827](https://github.com/user-attachments/assets/46089585-9330-4f60-959a-0ce0691c8047)
![Screenshot 2025-06-27 105316](https://github.com/user-attachments/assets/8a082493-7f95-435b-84aa-ced5ab2387c0)
![Screenshot 2025-06-27 105255](https://github.com/user-attachments/assets/2e820ce7-3394-411f-90d9-06523e2ed4ad)
![Screenshot 2025-06-27 104955](https://github.com/user-attachments/assets/534e9620-2569-4328-a0e9-c33a1b289c1e)


## Support

For issues or questions, please check the code comments and ensure all dependencies are properly configured. 

---

## 📄 License

All Rights Reserved.

Copyright (c) Supriya Dutta [2025].

This software is the intellectual property of Supriya Dutta.

Unauthorized copying, use, distribution, or modification of this code, in whole or in part, is strictly prohibited without explicit written permission from the author.

---
© 2025 Supriya Dutta. All rights reserved.
This project is developed and maintained by Supriya Dutta.
Unauthorized copying, reproduction, or redistribution is prohibited.
This project is licensed - see the [LICENSE](LICENSE) file for details.
