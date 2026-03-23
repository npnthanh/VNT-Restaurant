@extends('layout.user')

@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/user/contact.css') }}">
    @endpush

    @if(session('success'))
        <div id="toast-success" class="toast-notification">
            <div class="toast-content">
                <i class="fa fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif

    <main class="menu-page">
        <section class="menu-banner">
            <div class="menu-banner-container">
                <div class="menu-banner-text">
                    <h1>Liên hệ</h1>
                    <p>
                        Mọi góp ý, phản ánh hoặc đề xuất hợp tác đều được đội ngũ của quán tiếp nhận
                        và phản hồi nhanh nhất có thể.
                    </p>
                </div>
                <div class="menu-banner-actions">
                    <a href="{{ route('location') }}" class="hero-outline-btn">THAM GIA HỘI VIÊN</a>
                </div>
            </div>
        </section>

        <div class="menu-scroll-wrapper">
            <div class="fade-zone left"></div>
            <div class="menu-scroll" id="menuScroll">
                <a href="#" id="tabComplaint" class="active">PHẢN ÁNH KHIẾU NẠI</a>
                <a href="#" id="tabMedia">HỢP TÁC TRUYỀN THÔNG</a>
            </div>
            <div class="fade-zone right"></div>
        </div>

        <div id="formComplaint" class="contact-form-wrapper active">
            @include('user.form.complaint')
        </div>
        <div id="formMedia" class="contact-form-wrapper">
            @include('user.form.media')
        </div>
    </main>
@endsection

@push('js')
    <script src="{{ asset('js/user/contact.js') }}"></script>
@endpush
