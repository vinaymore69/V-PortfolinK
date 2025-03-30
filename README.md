# PortfolinK - Student Portfolio Management System

## Overview
PortfolinK is a web-based platform that allows students to manage and display their portfolios dynamically. Each student has a unique portfolio page that showcases their projects, skills, certifications, and achievements. Admins can bulk upload student data via Excel files and manage student profiles efficiently.

## Features
- **User Authentication**: Secure login system for admins.
- **Class Management**: View student lists by class, with search and deletion capabilities.
- **Dynamic Portfolio Generation**: Individual portfolio pages generated based on stored data.
- **Bulk Data Upload**: Import student details via Excel sheets.
- **Custom Portfolio Styling**: Students can upload their own CSS to customize their portfolio.
- **Search & Filter**: Find students by roll number in the class details page.
- **Profile Picture Management**: Default or custom profile pictures for each student.

## Tech Stack
- **Backend**: PHP, MySQL (PDO for secure database interaction)
- **Frontend**: HTML, CSS, JavaScript
- **Libraries & Tools**: PhpSpreadsheet (for Excel processing), Lenis.js (smooth scrolling), FontAwesome (icons)

## Installation & Setup
### 1. Clone the Repository
```bash
 git clone https://github.com/vinaymore69/V-PortfolinK.git
 cd PortfolinK
```

### 2. Set Up the Database
- Create a database in MySQL, e.g., `portfolink_db`.
- Import the provided `portfolink_db.sql` file.
- Ensure your database tables include `students`, `skills`, `projects`, `certifications`, `achievements`, and `extracurricular`.

### 3. Configure Database Connection
Edit `config.php`:
```php
$host = 'localhost';
$dbname = 'portfolink_db';
$username = 'your_db_user';
$password = 'your_db_password';
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
```

### 4. Install Dependencies
```bash
composer install
```

### 5. Set Up File Permissions
Ensure `uploads/` and its subdirectories (`profile_images/`, `custom_css/`) have proper write permissions:
```bash
chmod -R 775 uploads/
```

### 6. Run the Application
Place the project in your web server directory (e.g., `htdocs/` for XAMPP) and access it via:
```
http://localhost/PortfolinK/
```

## Usage
### Admin Panel
1. **Login**: Admins log in to manage student data.
2. **Upload Student Data**: Use `feedData.php` to upload student details via an Excel file.
3. **Manage Classes**: View, search, or delete student records in `classDetails.php`.

### Student Portfolio
- Visit `portfolio.php?roll_no=ROLLNUMBER` to view a student’s portfolio.
- Alternatively, use URL rewriting for cleaner URLs: `portfolio/ROLLNUMBER`.
- If a student has uploaded a custom CSS file (`uploads/custom_css/ROLLNUMBER_cssfile.css`), it is applied to their portfolio.

## Folder Structure
```
PortfolinK/
├── css_files/            # Default CSS stylesheets
├── images_file/          # Default images (e.g., profile placeholders)
├── javascript_files/     # JavaScript files
├── uploads/              # Uploaded student files (profile images, custom CSS)
│   ├── profile_images/   # Student profile pictures
│   ├── custom_css/       # Student custom CSS files
├── config.php            # Database connection file
├── login.php             # Admin login page
├── dashboard.php         # Admin dashboard
├── classDetails.php      # Class-wise student list
├── portfolio.php         # Individual student portfolio pages
├── feedData.php          # Bulk data upload script
├── README.md             # Project documentation (this file)
```

## Future Enhancements
- **User Registration**: Allow students to register and update their own profiles.
- **More Customization**: Let students select themes or layouts.
- **API Integration**: Provide an API to fetch student portfolios externally.
- **Portfolio Templates**: Offer multiple portfolio layouts to choose from.

## Contributions
Feel free to contribute by submitting pull requests, reporting issues, or suggesting improvements!

## License
This project is open-source and available under the [MIT License](LICENSE).

