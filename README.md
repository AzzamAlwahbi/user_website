# user_website
![user_website](user_shot.png)

## Features

### 👥 User Management
- **Add Users**: Simple form to add new users with name and age
- **Status Toggling**: Real-time toggle between Active/Inactive status
- **Delete Users**: Remove users with confirmation dialog
- **User Count**: Dynamic user counter in header

### ⚡ Real-time Operations
- Immediate status updates with visual feedback
- Smooth UI transitions after database operations
- Beautiful notifications for all actions

### 🔒 Database Integration
- Automatic database and table creation
- Secure database interactions
- Persistent data storage

## Technologies Used

- **Frontend**: 
  - HTML5, CSS3 (Flexbox, Grid, Animations)
  - JavaScript (ES6)
- **Backend**: 
  - PHP (PDO for database access)
- **Database**: 
  - MySQL
- **Libraries**:
  - Font Awesome (Icons)
  - SweetAlert2 (Beautiful alerts)
- **Styling**: 
  - CSS Variables
  - Gradient Effects
  - Responsive Design Principles

- **Installation Steps**

1. INSTALL XAMPP/MAMP
   - Download and install XAMPP (Windows) or MAMP (Mac)
   - Start Apache and MySQL services

2. SETUP PROJECT
   - Clone repository:
     git clone https://github.com/your-username/luxury-user-management.git
   - Copy files to htdocs:
     cp -r luxury-user-management /path/to/xampp/htdocs/user-management

3. ACCESS APPLICATION
   - Open browser and visit:
     http://localhost/user-management/

## Database Setup (Automatic)
- Database 'user_management' will be created automatically
- 'users' table with structure will be created on first run

- ## Basic Usage

ADD USER:
1. Enter full name
2. Enter age (1-120)
3. Click "Add User"

TOGGLE STATUS:
- Click the status switch (Green=Active, Red=Inactive)

DELETE USER:
1. Click "Delete" button
2. Confirm in dialog

## Troubleshooting
- Ensure Apache and MySQL are running
- Check PHP version (requires PHP 7.0+)
- Verify database credentials in config if manual setup needed

