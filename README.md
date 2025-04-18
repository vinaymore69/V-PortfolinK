![PortfolinK Screenshot](https://github.com/vinaymore69/V-PortfolinK/blob/main/images_file/V-PortfolinK.png)

# PortfolinK - Student Portfolio PDF Generator

## Description
PortfolinK is a web-based application designed to help administrators generate structured PDF portfolios for students. The application retrieves student details, including their skills, projects, certifications, extracurricular activities, and achievements, and formats them into a professional PDF document using TCPDF.

## Features
- **Secure Authentication**: Admin login system for secure access.
- **Student Data Management**: Fetch student details from the database.
- **Automated PDF Generation**: Generates a structured PDF portfolio with:
  - Student profile information
  - Skills with proficiency levels
  - Projects with descriptions and links
  - Certifications and issuing organizations
  - Extracurricular activities and achievements
- **User-Friendly Interface**: Displays a list of students with options to generate PDFs.
- **Responsive UI**: Features a video background and custom styling.

## Technologies Used
- **Backend**: PHP (handling logic and database operations)
- **Database**: MySQL (storing student data securely)
- **PDF Generation**: TCPDF (for structured PDF creation)
- **Frontend**: HTML, CSS (for styling and layout)
- **JavaScript**: UI enhancements and interactivity

## Installation & Setup
1. **Clone the repository:**
   ```sh
   git clone https://github.com/your-repo/portfolink.git
   ```
2. **Navigate to the project directory:**
   ```sh
   cd portfolink
   ```
3. **Install dependencies:**
   - Ensure PHP is installed.
   - Download and place TCPDF from [TCPDF GitHub](https://github.com/tecnickcom/TCPDF) into the project directory.
4. **Set up the database:**
   - Import the provided `database.sql` file into MySQL.
   - Update `config.php` with your database credentials.
5. **Run the application on a local server:**
   ```sh
   php -S localhost:8000
   ```
6. **Access the application in your browser:**
   ```
   http://localhost:8000
   ```

## Usage
1. **Admin Login:** Securely log in as an administrator.
2. **Student Selection:** Choose a student from the list.
3. **Portfolio Generation:** Generate and view/download the student's PDF portfolio.
4. **Database Management:** Admins can update student details as needed.

## Future Enhancements
- **Student Self-Registration**: Allow students to create and update their own profiles.
- **Theme Selection**: Enable different portfolio designs and styles.
- **API Integration**: Provide an API for external applications to fetch student portfolios.
- **More Export Options**: Generate portfolios in other formats like DOCX and JSON.

## Author
Vinay Prakash More

## License
This project is licensed under the MIT License.
