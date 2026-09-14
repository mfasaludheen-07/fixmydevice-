// FixMyDevice - Enhanced Client Micro-Animations & Interactions

document.addEventListener('DOMContentLoaded', () => {
    // 1. Dark / Light Theme Switcher
    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', nextTheme);
            try {
                localStorage.setItem('fmd_theme', nextTheme);
            } catch (e) {}
        });
    }

    // 2. Responsive Mobile Drawer Navigation
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileClose = document.getElementById('mobileNavClose');
    const mainNav = document.getElementById('mainNav');
    const backdrop = document.getElementById('mobileNavBackdrop');

    function openMobileMenu() {
        if (mainNav && backdrop) {
            mainNav.classList.add('active');
            backdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeMobileMenu() {
        if (mainNav && backdrop) {
            mainNav.classList.remove('active');
            backdrop.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (mobileBtn) mobileBtn.addEventListener('click', openMobileMenu);
    if (mobileClose) mobileClose.addEventListener('click', closeMobileMenu);
    if (backdrop) backdrop.addEventListener('click', closeMobileMenu);

    // 3. Auto-dismiss flash alerts with fade animation
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            alert.style.transition = 'all 0.4s ease';
            setTimeout(() => alert.remove(), 400);
        }, 6000);
    });

    // 4. Drag & Drop File Upload with Instant Thumbnail Preview
    const fileDropzone = document.getElementById('fileDropzone');
    const fileInput = document.getElementById('attachment');
    const filePreviewBox = document.getElementById('filePreviewBox');
    const previewThumb = document.getElementById('previewThumb');
    const previewName = document.getElementById('previewName');
    const previewSize = document.getElementById('previewSize');
    const previewRemoveBtn = document.getElementById('previewRemoveBtn');

    if (fileDropzone && fileInput) {
        ['dragenter', 'dragover'].forEach(eventName => {
            fileDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                fileDropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                fileDropzone.classList.remove('dragover');
            });
        });

        fileDropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt.files && dt.files.length > 0) {
                fileInput.files = dt.files;
                handleFileSelect(dt.files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        if (previewRemoveBtn) {
            previewRemoveBtn.addEventListener('click', () => {
                fileInput.value = '';
                if (filePreviewBox) filePreviewBox.style.display = 'none';
                if (fileDropzone) fileDropzone.style.display = 'block';
            });
        }
    }

    function handleFileSelect(file) {
        if (!filePreviewBox || !previewName || !previewSize) return;

        previewName.textContent = file.name;
        const sizeFormatted = file.size > 1024 * 1024
            ? (file.size / (1024 * 1024)).toFixed(2) + ' MB'
            : (file.size / 1024).toFixed(1) + ' KB';
        previewSize.textContent = sizeFormatted;

        if (previewThumb) {
            previewThumb.innerHTML = '';
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = file.name;
                    previewThumb.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else {
                previewThumb.innerHTML = '<i class="fa-solid fa-file-pdf" style="font-size:24px;color:#ef4444;"></i>';
            }
        }

        filePreviewBox.style.display = 'flex';
    }

    // 5. Scroll Entrance Animation Observer
    const animatedElements = document.querySelectorAll('.category-card, .stat-card, .step-card, .review-card, .card-dashboard');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animatedElements.forEach(el => observer.observe(el));
    }
});

// 6. Global Password Visibility Toggler
window.togglePasswordVisibility = function (inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
};

// 7. Global Live Password Strength Checker
window.checkPasswordStrength = function (password, barId, textId) {
    const bar = document.getElementById(barId);
    const text = document.getElementById(textId);
    if (!bar || !text) return;

    if (!password || password.length === 0) {
        bar.className = 'strength-bar-fill';
        bar.style.width = '0%';
        text.className = 'strength-text';
        text.textContent = 'Password strength';
        return;
    }

    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    bar.className = 'strength-bar-fill';
    text.className = 'strength-text';

    if (score <= 2) {
        bar.classList.add('weak');
        text.classList.add('weak');
        text.textContent = 'Weak (Needs 8+ chars, letters & numbers)';
    } else if (score <= 4) {
        bar.classList.add('medium');
        text.classList.add('medium');
        text.textContent = 'Moderate (Good password)';
    } else {
        bar.classList.add('strong');
        text.classList.add('strong');
        text.textContent = 'Strong (Excellent security!)';
    }
};

// 8. Global Brand Quick Selector
window.selectBrand = function (brandName) {
    const brandInput = document.getElementById('brand');
    if (brandInput) {
        brandInput.value = brandName;
        const modelInput = document.getElementById('model_number');
        if (modelInput) modelInput.focus();
    }
};

// 9. Global Instant Table Search Filter
window.filterTable = function (inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    const filter = input.value.toLowerCase().trim();
    const rows = table.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr') || [];

    for (let i = 0; i < rows.length; i++) {
        const rowText = rows[i].textContent || rows[i].innerText;
        if (rowText.toLowerCase().indexOf(filter) > -1) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
};