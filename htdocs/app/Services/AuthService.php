<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\AppException;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\UserRepository;

class AuthService
{
    protected UserRepository $userRepo;
    protected LoginAttemptRepository $loginAttemptRepo;

    public function __construct(
        ?UserRepository $userRepo = null,
        ?LoginAttemptRepository $loginAttemptRepo = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->loginAttemptRepo = $loginAttemptRepo ?? new LoginAttemptRepository();
    }

    /**
     * Register a new user and automatically log them in.
     */
    public function register(array $input): array
    {
        $validated = Validator::validate($input, [
            'username' => 'required|min:3|max:100',
            'email' => 'required|email|max:100',
            'password' => 'required|min:8',
            'phone' => 'nullable|phone',
        ]);

        $email = strtolower((string) $validated['email']);

        if ($this->userRepo->emailExists($email)) {
            throw new AppException(
                'VALIDATION_ERROR',
                'Periksa kembali isian Anda.',
                422,
                ['email' => 'Email sudah terdaftar.']
            );
        }

        $userId = $this->userRepo->create([
            'username' => $validated['username'],
            'email' => $email,
            'password' => password_hash($validated['password'], PASSWORD_BCRYPT),
            'phone' => $validated['phone'] ?? null,
            'role' => 'USER',
            'is_active' => 1,
        ]);

        // Automatically start session and log in
        Session::start();
        Session::regenerate();
        Session::set('user_id', $userId);

        return [
            'id' => $userId,
            'username' => $validated['username'],
            'email' => $email,
            'phone' => $validated['phone'] ?? null,
            'role' => 'USER',
            'store_status' => null,
        ];
    }

    /**
     * Authenticate user with rate limiting and session regeneration.
     */
    public function login(array $input, string $clientIp): array
    {
        $validated = Validator::validate($input, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = strtolower((string) $validated['email']);

        // Check login rate limit (5 failed attempts within 15 minutes)
        $failedAttempts = $this->loginAttemptRepo->countRecentAttempts($email, $clientIp, 15);
        if ($failedAttempts >= 5) {
            throw new AppException(
                'RATE_LIMITED',
                'Terlalu banyak percobaan masuk yang gagal. Silakan coba lagi setelah 15 menit.',
                429
            );
        }

        $user = $this->userRepo->findByEmail($email);

        if (!$user || !password_verify((string) $validated['password'], (string) $user['password']) || $user['is_active'] !== 1) {
            $this->loginAttemptRepo->record($email, $clientIp);

            throw new AppException(
                'INVALID_CREDENTIALS',
                'Email atau kata sandi yang Anda masukkan salah.',
                401
            );
        }

        // Clear previous failed attempts
        $this->loginAttemptRepo->clear($email, $clientIp);

        // Regenerate session and set user ID
        Session::start();
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);

        $csrfToken = Csrf::getToken();

        return [
            'user' => [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role'],
                'store_status' => $user['store_status'],
            ],
            'csrf_token' => $csrfToken,
        ];
    }

    /**
     * Terminate the user session.
     */
    public function logout(): void
    {
        Session::destroy();
    }

    /**
     * Update authenticated user profile.
     */
    public function updateProfile(int $userId, array $input): array
    {
        $validated = Validator::validate($input, [
            'username' => 'nullable|min:3|max:100',
            'phone' => 'nullable|phone',
        ]);

        $updateData = [];
        if (array_key_exists('username', $input) && $validated['username'] !== null) {
            $updateData['username'] = $validated['username'];
        }
        if (array_key_exists('phone', $input)) {
            $updateData['phone'] = $validated['phone'];
        }

        if (!empty($updateData)) {
            $this->userRepo->updateProfile($userId, $updateData);
        }

        $user = $this->userRepo->findUserWithStore($userId);
        if (!$user) {
            throw new AppException('NOT_FOUND', 'Pengguna tidak ditemukan.', 404);
        }

        return [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'store_status' => $user['store_status'],
        ];
    }

    /**
     * Change user password.
     */
    public function changePassword(int $userId, array $input): void
    {
        $validated = Validator::validate($input, [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        $currentHash = $this->userRepo->getPasswordHash($userId);
        if (!$currentHash || !password_verify((string) $validated['current_password'], $currentHash)) {
            throw new AppException(
                'VALIDATION_ERROR',
                'Periksa kembali isian Anda.',
                422,
                ['current_password' => 'Kata sandi saat ini tidak sesuai.']
            );
        }

        $newHash = password_hash((string) $validated['new_password'], PASSWORD_BCRYPT);
        $this->userRepo->updatePassword($userId, $newHash);
    }

    /**
     * Get the authenticated user data or null if guest / session invalid.
     */
    public function getCurrentUser(): ?array
    {
        Session::start();
        $userId = Session::get('user_id');

        if (!$userId || !is_numeric($userId)) {
            return null;
        }

        $user = $this->userRepo->findUserWithStore((int) $userId);
        if (!$user || $user['is_active'] !== 1) {
            Session::destroy();
            return null;
        }

        return [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'store_status' => $user['store_status'],
        ];
    }

    /**
     * Assemble payload for GET /auth/me.
     */
    public function getAuthMeData(): array
    {
        $user = $this->getCurrentUser();
        $csrfToken = Csrf::getToken();

        $unreadNotifications = 0;
        $unreadMessages = 0;

        if ($user !== null) {
            $unreadNotifications = $this->userRepo->countUnreadNotifications((int) $user['id']);
            $unreadMessages = $this->userRepo->countUnreadMessages((int) $user['id']);
        }

        return [
            'user' => $user,
            'csrf_token' => $csrfToken,
            'unread_notifications' => $unreadNotifications,
            'unread_messages' => $unreadMessages,
        ];
    }
}
