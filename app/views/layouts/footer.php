<footer class="app-footer">
    <div class="footer-container">
        <div class="footer-col brand-col">
            <div class="footer-logo">
                <i class="fa-solid fa-screwdriver-wrench"></i> FixMy<span>Device</span>
            </div>
            <p>Your trusted hardware appliance repair & ticketing platform. Expert diagnosis for TVs, Refrigerators, Washing Machines, Air Conditioners, Laptops & more.</p>
            <div class="social-links">
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-linkedin"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Supported Hardware</h4>
            <ul>
                <li><a href="index.php?url=home#categories"><i class="fa-solid fa-tv"></i> Smart TVs & Displays</a></li>
                <li><a href="index.php?url=home#categories"><i class="fa-solid fa-snowflake"></i> Refrigerators & Freezers</a></li>
                <li><a href="index.php?url=home#categories"><i class="fa-solid fa-soap"></i> Washing Machines</a></li>
                <li><a href="index.php?url=home#categories"><i class="fa-solid fa-wind"></i> Split & Window ACs</a></li>
                <li><a href="index.php?url=home#categories"><i class="fa-solid fa-laptop"></i> Laptops & Computer Hardware</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php?url=home">Home Page</a></li>
                <li><a href="index.php?url=track">Track Complaint Code</a></li>
                <li><a href="index.php?url=login">Customer Portal Login</a></li>
                <li><a href="index.php?url=register">Create New Account</a></li>
                <li><a href="index.php?url=login">Technician & Staff Login</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Technician Contacts</h4>
            <div class="contact-info">
                <div class="technician-phones-list">
                    <p><i class="fa-solid fa-wrench"></i> <span class="tech-label">Technician 1:</span> <a href="tel:9447556907">9447556907</a></p>
                    <p><i class="fa-solid fa-wrench"></i> <span class="tech-label">Technician 2:</span> <a href="tel:8137012739">8137012739</a></p>
                    <p><i class="fa-solid fa-wrench"></i> <span class="tech-label">Technician 3:</span> <a href="tel:9746219299">9746219299</a></p>
                </div>
                <p><i class="fa-solid fa-envelope"></i> <a href="mailto:support@fixmydevice.com">support@fixmydevice.com</a></p>
                <p><i class="fa-solid fa-clock"></i> Mon - Sat: 8:00 AM - 8:00 PM</p>
                <p><i class="fa-solid fa-location-dot"></i> 100 Service HQ Blvd, Tech City</p>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <p>&copy; <?= date('Y') ?> FixMyDevice Hardware Service Ticketing. All rights reserved.</p>
            <div class="footer-meta">
                <span><i class="fa-solid fa-shield-halved"></i> 100% Genuine Parts Guarantee</span>
                <span><i class="fa-solid fa-certificate"></i> Certified Technicians</span>
            </div>
        </div>
    </div>
</footer>

<script src="<?= (!empty($baseUrl) && $baseUrl !== '/') ? rtrim($baseUrl, '/') : '' ?>/assets/js/app.js"></script>
</body>
</html>
