<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    public function login()
    {
        if (Session::isLoggedIn()) {
            $this->redirectByRole(Session::role());
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            // Rate Limiting Protection: Max 5 failed attempts within 15 minutes
            $attempts = Session::get('login_attempts', 0);
            $lockoutTime = Session::get('login_lockout_until', 0);
            $currentTime = time();

            if ($lockoutTime > $currentTime) {
                $remainingMinutes = ceil(($lockoutTime - $currentTime) / 60);
                Session::setFlash('error', "Too many failed attempts. Login temporarily locked. Please try again in {$remainingMinutes} minute(s).");
                $this->render('auth/login', ['pageTitle' => 'Login - FixMyDevice', 'email' => trim($_POST['email'] ?? '')]);
                return;
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                Session::setFlash('error', 'Please enter email and password.');
                $this->render('auth/login', ['pageTitle' => 'Login - FixMyDevice', 'email' => $email]);
                return;
            }

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user && $userModel->verifyPassword($user, $password)) {
                // Clear rate-limiting records upon success
                Session::remove('login_attempts');
                Session::remove('login_lockout_until');

                unset($user['password_hash']);
                Session::regenerate();
                Session::set('user', $user);
                Session::setFlash('success', "Welcome back, {$user['name']}!");
                $this->redirectByRole($user['role']);
            } else {
                // Increment failed attempts
                $attempts++;
                Session::set('login_attempts', $attempts);
                if ($attempts >= 5) {
                    Session::set('login_lockout_until', $currentTime + (15 * 60)); // 15 min lock
                    Session::setFlash('error', 'Too many invalid attempts. Your account login is locked for 15 minutes.');
                } else {
                    $remaining = 5 - $attempts;
                    Session::setFlash('error', "Invalid email or password. {$remaining} attempt(s) remaining.");
                }

                $this->render('auth/login', ['pageTitle' => 'Login - FixMyDevice', 'email' => $email]);
                return;
            }
        }

        $this->render('auth/login', ['pageTitle' => 'Login - FixMyDevice']);
    }

    public function register()
    {
        if (Session::isLoggedIn()) {
            $this->redirectByRole(Session::role());
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($name) || empty($email) || empty($password)) {
                Session::setFlash('error', 'Name, email, and password are required fields.');
                $this->render('auth/register', ['pageTitle' => 'Register - FixMyDevice', 'formData' => $_POST]);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('error', 'Invalid email address format.');
                $this->render('auth/register', ['pageTitle' => 'Register - FixMyDevice', 'formData' => $_POST]);
                return;
            }

            if ($password !== $confirmPassword) {
                Session::setFlash('error', 'Passwords do not match.');
                $this->render('auth/register', ['pageTitle' => 'Register - FixMyDevice', 'formData' => $_POST]);
                return;
            }

            // Strong password requirements: minimum 8 characters, at least 1 letter and 1 number
            if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                Session::setFlash('error', 'Password must be at least 8 characters long and contain both letters and numbers.');
                $this->render('auth/register', ['pageTitle' => 'Register - FixMyDevice', 'formData' => $_POST]);
                return;
            }

            $userModel = new User();
            if ($userModel->findByEmail($email)) {
                Session::setFlash('error', 'An account with this email already exists.');
                $this->render('auth/register', ['pageTitle' => 'Register - FixMyDevice', 'formData' => $_POST]);
                return;
            }

            $userId = $userModel->create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'password' => $password,
                'role' => 'customer'
            ]);

            $newUser = $userModel->findById($userId);
            Session::regenerate();
            Session::set('user', $newUser);
            Session::setFlash('success', 'Account created successfully! Welcome to FixMyDevice.');
            $this->redirect('ticket/dashboard');
        }

        $this->render('auth/register', ['pageTitle' => 'Register - FixMyDevice']);
    }

    public function profile()
    {
        $this->requireLogin();
        $user = Session::user();
        $userModel = new User();
        $userData = $userModel->findById($user['id']);

        $this->render('auth/profile', [
            'pageTitle' => 'Account Profile & Security - FixMyDevice',
            'user' => $userData
        ]);
    }

    public function update_profile()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (empty($name)) {
                Session::setFlash('error', 'Full Name is required.');
                $this->redirect('profile');
            }

            $user = Session::user();
            $userModel = new User();
            $userModel->updateProfile($user['id'], [
                'name' => $name,
                'phone' => $phone,
                'address' => $address
            ]);

            // Update session user cache
            $updatedUser = $userModel->findById($user['id']);
            Session::set('user', $updatedUser);

            Session::setFlash('success', 'Your contact profile details have been successfully updated.');
            $this->redirect('profile');
        }
        $this->redirect('profile');
    }

    public function change_password()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                Session::setFlash('error', 'All password fields are required.');
                $this->redirect('profile');
            }

            $user = Session::user();
            $userModel = new User();
            $fullUser = $userModel->findByEmail($user['email']);

            if (!$userModel->verifyPassword($fullUser, $currentPassword)) {
                Session::setFlash('error', 'Current password entered is incorrect.');
                $this->redirect('profile');
            }

            if ($newPassword !== $confirmPassword) {
                Session::setFlash('error', 'New password and confirmation do not match.');
                $this->redirect('profile');
            }

            if (strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                Session::setFlash('error', 'New password must be at least 8 characters long and contain both letters and numbers.');
                $this->redirect('profile');
            }

            $userModel->updatePassword($user['id'], $newPassword);
            Session::setFlash('success', 'Your password was changed successfully.');
            $this->redirect('profile');
        }
        $this->redirect('profile');
    }

    public function logout()
    {
        Session::destroy();
        Session::setFlash('success', 'You have been logged out.');
        $this->redirect('home');
    }

    private function redirectByRole($role)
    {
        switch ($role) {
            case 'admin':
                $this->redirect('admin/dashboard');
                break;
            case 'technician':
                $this->redirect('technician/dashboard');
                break;
            default:
                $this->redirect('ticket/dashboard');
                break;
        }
    }
}
