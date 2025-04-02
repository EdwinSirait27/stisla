<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Benjamin Smith - Portfolio</title>
    <style>
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
        }

        /* Animasi Keyframes */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Container */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            display: flex;
            background-color: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            animation: fadeIn 1s ease-in;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #ffc107;
            padding: 20px;
            color: white;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 10px;
        }

        .sidebar-nav a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            transition: background-color 0.3s;
        }

        .sidebar-nav a:hover {
            background-color: rgba(255,255,255,0.2);
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
        }

        .profile {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            animation: slideIn 1s ease-out;
        }

        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin-right: 30px;
            object-fit: cover;
            border: 5px solid #ffc107;
            animation: pulse 2s infinite;
        }

        .profile-info h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .profile-info h2 {
            color: #777;
            margin-bottom: 15px;
        }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            background-color: #333;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            animation: fadeIn 1.5s ease-in;
        }

        .stat-number {
            font-size: 2.5em;
            color: #ffc107;
            margin-bottom: 10px;
        }

        /* Services */
        .services {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .service-item {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            transition: transform 0.3s;
        }

        .service-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .service-icon {
            font-size: 3em;
            color: #ffc107;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="#">HOME</a></li>
                    <li><a href="#">ABOUT ME</a></li>
                    <li><a href="#">RESUME</a></li>
                    <li><a href="#">PORTFOLIO</a></li>
                    <li><a href="#">TESTIMONIALS</a></li>
                    <li><a href="#">CONTACT</a></li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <div class="profile">
                <img src="https://via.placeholder.com/200" alt="Benjamin Smith" class="profile-image">
                <div class="profile-info">
                    <h1>Benjamin Smith</h1>
                    <h2>Graphic Designer / Photographer</h2>
                    <p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Donec velit neque, auctor sit amet.</p>
                </div>
            </div>

            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">15+</div>
                    <div>YEARS EXPERIENCE</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">350+</div>
                    <div>PROJECTS DONE</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">200+</div>
                    <div>HAPPY CLIENTS</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">45K</div>
                    <div>FOLLOWERS</div>
                </div>
            </div>

            <div class="services">
                <div class="service-item">
                    <div class="service-icon">🖨️</div>
                    <h3>PRINT DESIGN</h3>
                    <p>Specialized in creating compelling print materials</p>
                </div>
                <div class="service-item">
                    <div class="service-icon">💻</div>
                    <h3>WEB DESIGN</h3>
                    <p>Creating responsive and user-friendly websites</p>
                </div>
                <div class="service-item">
                    <div class="service-icon">📷</div>
                    <h3>PHOTOGRAPHY</h3>
                    <p>Capturing moments with professional photography</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>