# 🌟 Himatic Competition Season 1

### 🏆 Andhika Tri Hazra - TUAN MUDA Team

Welcome to the official repository for **Himatic Competition Season 1**! This project showcases the work of the TUAN MUDA Team,by Andhika Tri Hazra. Below are the steps to get started.

---

## 🚀 Installation Steps

Follow these steps to set up the project on your local machine / device:

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/andhikatrihazra/Himatic_Competition-Inventory.git
   ```

2. **Navigate to the Project Directory**:
   ```bash
   cd Himatic_Competition-Inventory
   ```

3. **Instal Dependency Composer**:
   ```bash
   composer install
   ```

4. **Copy File .env**:
   ```bash
   cp .env.example .env
   ```
   
5. File .env:
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=Himatic_Competition_Inventory
    DB_USERNAME=username
    DB_PASSWORD=password

6. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

7. **Database Migration**:
   ```bash
   php artisan migrate
   ```

8. **Make User Account**:
   ```bash
   php artisan make:filament-user
   ```

9. **Generate Role And Permission**:
   ```bash
   php artisan shield:generate --all
   ```

10. **Make Super Admin**:
   ```bash
    php artisan shield:super-admin
   ```

Last. **Serve The Application**:
   ```bash
    php artisan serve
   ```

---

## 📚 Additional Resources

If you encounter any issues, feel free to chat men on @andhikahazraa.

---

### 🎉 Happy Coding!
