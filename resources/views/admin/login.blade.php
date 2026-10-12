@extends('layout.website-admin')
@section('title', 'Đăng nhập quản trị website | Tới Bến Quán')

@section('content')
    <main class="login-page-centered">
        <div class="login-backdrop"></div>

        <div class="login-box-wrapper">
            <div class="login-card">
                <!-- Brand & Header -->
                <div class="login-header">
                    <a class="login-logo-link" href="{{ route('home') }}" title="Trang chủ Tới Bến Quán">
                        <img src="{{ asset($website['logo']) }}" alt="{{ $website['site_name'] }}" class="login-logo-img">
                    </a>
                    <div class="login-badge-pill">
                        <span class="live-dot"></span>
                        <span>QUẢN TRỊ WEBSITE</span>
                    </div>
                    <h1 class="login-title">Đăng nhập quản trị</h1>
                    <p class="login-subtitle">Dành riêng cho Quản lý và Quản trị viên cơ sở</p>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="notice error login-notice" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('admin.login.submit') }}" class="login-form" id="loginForm">
                    @csrf

                    <!-- Location Code -->
                    <div class="form-group">
                        <label for="location_code" class="form-label">
                            <i class="fa-solid fa-shop"></i>
                            <span>Mã quán / Chi nhánh</span>
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper has-icon">
                            <span class="input-icon-pill"><i class="fa-solid fa-store"></i></span>
                            <input id="location_code"
                                   name="location_code"
                                   type="text"
                                   value="{{ old('location_code') }}"
                                   autocomplete="organization"
                                   maxlength="50"
                                   placeholder="Nhập mã quán (ví dụ: CS01)"
                                   required
                                   autofocus
                                   class="form-control">
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fa-solid fa-phone"></i>
                            <span>Số điện thoại đăng nhập</span>
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper has-icon">
                            <span class="input-icon-pill"><i class="fa-solid fa-phone-volume"></i></span>
                            <input id="phone"
                                   name="phone"
                                   type="tel"
                                   value="{{ old('phone') }}"
                                   autocomplete="username"
                                   maxlength="30"
                                   placeholder="Số điện thoại nhân viên / quản lý"
                                   required
                                   class="form-control">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fa-solid fa-lock"></i>
                            <span>Mật khẩu</span>
                            <span class="required">*</span>
                        </label>
                        <div class="input-wrapper has-icon has-trailing-action">
                            <span class="input-icon-pill"><i class="fa-solid fa-key"></i></span>
                            <input id="password"
                                   name="password"
                                   type="password"
                                   autocomplete="current-password"
                                   maxlength="200"
                                   placeholder="Nhập mật khẩu"
                                   required
                                   class="form-control">
                            <button type="button" class="btn-toggle-password" id="togglePasswordBtn" aria-label="Hiện hoặc ẩn mật khẩu" title="Hiện hoặc ẩn mật khẩu">
                                <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button class="button primary login-submit-btn" type="submit" id="loginSubmitBtn">
                        <span class="btn-text">Đăng nhập quản trị</span>
                        <i class="fa-solid fa-arrow-right btn-icon"></i>
                    </button>
                </form>

                <!-- Footer Links -->
                <div class="login-footer">
                    <a class="back-link" href="{{ route('home') }}">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Quay lại trang khách</span>
                    </a>
                </div>
            </div>

            <!-- Bottom System Info -->
            <div class="login-system-footer">
                <span><i class="fa-solid fa-shield-halved"></i> Hệ thống quản trị nội bộ Tới Bến Quán</span>
            </div>
        </div>
    </main>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Password Show/Hide Toggle
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (toggleBtn && passwordInput && toggleIcon) {
                toggleBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    toggleIcon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
                });
            }

            // Form Submit Loading State
            const loginForm = document.getElementById('loginForm');
            const submitBtn = document.getElementById('loginSubmitBtn');
            if (loginForm && submitBtn) {
                loginForm.addEventListener('submit', function () {
                    submitBtn.classList.add('is-submitting');
                    submitBtn.disabled = true;
                    submitBtn.querySelector('.btn-text').textContent = 'Đang đăng nhập…';
                    const icon = submitBtn.querySelector('.btn-icon');
                    if (icon) {
                        icon.className = 'fa-solid fa-circle-notch fa-spin btn-icon';
                    }
                });
            }
        });
    </script>
@endpush
