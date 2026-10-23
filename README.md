# Smart-Scrap-Management-System

A web application designed to simplify and digitize scrap collection. Users can schedule pickups online, and admins and drivers can manage the entire process digitally. This system improves efficiency, saves time, and promotes eco-friendly waste management.

### 🌐 Live Demo: [scrap-hub.infinityfreeapp.com](https://scrap-hub.infinityfreeapp.com/)

| Portal | Link |
|--------|------|
| Home page | https://scrap-hub.infinityfreeapp.com/ |
| User login | https://scrap-hub.infinityfreeapp.com/user/login.php |
| Driver login | https://scrap-hub.infinityfreeapp.com/driver/login.php |
| Admin login | https://scrap-hub.infinityfreeapp.com/admin/login.php |

## Highlights
1. Developed a full-stack web-based scrap management system for 60+ users, enabling online pickup requests, real-time tracking, and automated notifications.
2. Designed responsive UI with HTML, CSS, and JavaScript, built back-end with PHP, and integrated MySQL for efficient data management.
3. Implemented Geocoding API for location validation, reducing scheduling errors by 40%, and added automated emails, eliminating 70% of manual status updates.
4. Included driver safety modules such as daily vehicle pre-checks and incident reporting for accountability.

**Tech Stack:** HTML, CSS, JavaScript, PHP, MySQL.

## Admin Account Features
1. Secure login and dashboard view of all requests and drivers.
2. Manage drivers (add, edit, delete, set passwords).
3. Approve/reject requests and assign drivers.
4. Monitor driver status and updates.
5. Generate reports by date, driver, and request status.
6. Manage scrap price lists and recycling center details.
7. Track revenue, profit, and loss from scrap sales.
8. Manage users, content pages, and payment records.
9. Review incident reports and daily vehicle pre-checks.
10. View scrap collection reports for each component.
11. Receive notifications for new requests and driver updates.

## User Account Features
1. Sign up/login with secure credentials.
2. Submit new scrap pickup requests with details (address, scrap type, images, date/time, UPI-linked mobile).
3. Location validation using Geocoding API before request submission.
4. View request history and check live request status.
5. Receive automated email notifications on status updates.
6. Access and print payment receipts after scrap collection.
7. Manage profile and update credentials.

## Driver Account Features
1. Login with admin-assigned credentials.
2. Dashboard showing assigned, in-progress, and completed requests.
3. Update request status (On the Way, Completed, Rejected).
4. Notify users and admin on updates.
5. Generate invoices (Cash/UPI, amount, remarks).
6. Submit daily vehicle pre-check reports (tires, lights, brakes, fluids, etc.).
7. Report trip incidents with details, location, and optional file upload.
8. Manage profile and password updates.
9. Receive notifications for new assignments.

## How to Run Locally (XAMPP / WAMP)
1. Copy the project folder into `htdocs`.
2. Create the database `garbagemsdb` in phpMyAdmin and import the SQL file.
3. Check the database login in `includes/dbconnection.php` and in the `includes/dbconnection.php` file inside `admin/`, `driver/` and `user/`.
4. Add your own mail ID / app password and Geocoding API key where the placeholders are.
5. Open `http://localhost/<folder-name>/` in the browser.

# Screenshots

## Website (Public Pages)

### Home page
![Home page](logo/image-1.png)

### Price List page
![Price list page](logo/image-2.png)

### Contact Us page
![Contact us page](logo/image-18.png)

### Login page
![Login page](logo/image-17.png)

### Mobile view (fully responsive)
![Mobile view](logo/image-26.png)

## User Side

### User Dashboard
![User dashboard](logo/image-6.png)

### Pickup Request (lodged-complain) page
![Pickup request page](logo/image-7.png)

### Select Scrap Items page
![Select scrap items page](logo/image-19.png)

### Request History page
![Request history page](logo/image-20.png)

### View Request Details (view-lodged-complain-detail) page
![View request details page](logo/image-8.png)

### Payment Receipt page
![Payment receipt page](logo/image-9.png)

## Driver Side

### Driver Dashboard
![Driver dashboard](logo/image-3.png)

### View Request Details (view-complain-detail) page
![Driver view request page](logo/image-4.png)

### Lodged Request Report page
![Lodged request report page](logo/image-5.png)

### Daily Vehicle Pre-Check page
![Daily vehicle pre-check page](logo/image-21.png)

### Report Incident page
![Report incident page](logo/image-22.png)

## Admin Side

### Admin Dashboard
![Admin dashboard](logo/image-10.png)

### All Requests page
![All requests page](logo/image-23.png)

### View Request Details (view-complain-detail) page
![Admin view request page](logo/image-11.png)

### Scrap Price List (admin_price_list) page
![Admin price list page](logo/image-12.png)

### Scrap Collection History (scrap_collection_list) page
![Scrap collection list page](logo/image-13.png)

### Sales Profit Report (sell_profit_report) page
![Sell profit report page](logo/image-14.png)

### Recycling Center (recycle_center) page
![Recycle center page](logo/image-15.png)

### Registered Users (reg-users) page
![Registered users page](logo/image-16.png)

### Driver Incident Reports page
![Driver incident reports page](logo/image-24.png)

### Driver Pre-Check Reports page
![Driver pre-check reports page](logo/image-25.png)
