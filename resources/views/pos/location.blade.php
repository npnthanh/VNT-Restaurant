@extends('layout.pos')

@section('title', 'VNT Pos - Quản lý chi nhánh')

@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/pos/location.css') }}">
    @endpush

    <div class="room-table-page">
        <div class="layout">

            <div class="sidebar">
                <div class="box">
                    <div class="box-title">
                        <span>Khu vực</span>
                        @can('create_region')
                            <button type="button" class="add-region-btn">+</button>
                        @endcan
                    </div>

                    <div class="custom-dropdown" id="regionDropdown">
                        <div class="selected-display">
                            <span id="regionCurrentValue">-- Tất cả khu vực --</span>
                            <i class="fa-solid fa-chevron-down arrow-icon"></i>
                        </div>
                        <ul class="dropdown-list">
                            <li data-value="">-- Tất cả khu vực --</li>
                            @foreach($regions as $region)
                                <li data-value="{{ $region->id }}">{{ $region->name }}</li>
                            @endforeach
                        </ul>
                        <input type="hidden" id="regionSelect">
                        @can('update_region')
                            <i class="fa-regular fa-pen-to-square edit-icon d-none" id="editRegionBtn"></i>
                        @endcan
                    </div>
                </div>

                <div class="box">
                    <div class="box-title">Tìm kiếm</div>
                    <input
                        type="text"
                        class="input-text"
                        id="location-search"
                        placeholder="Tên hoặc mã địa điểm"
                    >
                </div>

                <div class="box status-box">
                    <div class="box-title">Trạng thái
                        <span class="status-arrow"></span>
                    </div>

                    <div class="status-content">
                        <label class="radio-item">
                            <input type="radio" name="status" value="all" checked>
                            <span>Tất cả</span>
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="status" value="active">
                            <span>Đang hoạt động</span>
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="status" value="inactive">
                            <span>Ngừng hoạt động</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div class="header-row">
                    <h2>Chi nhánh</h2>
                    @can('create_location')
                        <button class="btn-create" id="addLocationBtn"><i class="far fa-plus"></i> Thêm địa điểm</button>
                    @endcan
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã địa điểm</th>
                            <th>Tên</th>
                            <th>Khu vực</th>
                            <th>Sức chứa</th>
                            <th>Diện tích (m²)</th>
                            <th>Số tầng</th>
                            <th>Giờ phục vụ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locations as $location)
                            @php
                                $start = $location->time_start ? \Carbon\Carbon::parse($location->time_start)->format('H:i') : '--';
                                $end = $location->time_end ? \Carbon\Carbon::parse($location->time_end)->format('H:i') : '--';
                            @endphp
                            <tr class="location-row"
                                data-id="{{ $location->id }}"
                                data-region="{{ $location->region_id }}"
                                data-status="{{ $location->status }}"
                                data-name="{{ strtolower($location->name) }}"
                                data-code="{{ strtolower($location->code) }}">
                                <td>{{ $location->code }}</td>
                                <td>{{ $location->name }}</td>
                                <td>{{ $location->region->name ?? '---' }}</td>
                                <td>{{ $location->capacity ?? '---' }}</td>
                                <td>{{ $location->area ?? '---' }}</td>
                                <td>{{ $location->floors ?? '---' }}</td>
                                <td>{{ $start }} - {{ $end }}</td>
                                <td>{{ $location->status === 'active' ? 'Đang hoạt động' : 'Ngừng hoạt động' }}</td>
                            </tr>
                            <tr class="detail-row" id="detail-{{ $location->id }}" style="display:none;">
                                <td colspan="8">
                                    <div class="location-detail">
                                        <div class="location-detail-content">
                                            <div class="detail-col pic">
                                                <div class="detail-image">
                                                    <img src="{{ asset($location->thumbnail ?? 'images/location/L12L04.jpg') }}"
                                                        alt="{{ $location->name }}">
                                                </div>
                                            </div>
                                            <div class="detail-col info">
                                                <div class="row">
                                                    <div class="detail-field">
                                                        <div class="field-label">Mã địa điểm</div>
                                                        <div class="field-value">{{ $location->code }}</div>
                                                    </div>
                                                    <div class="detail-field">
                                                        <div class="field-label">Tên</div>
                                                        <div class="field-value">{{ $location->name }}</div>
                                                    </div>
                                                    <div class="detail-field">
                                                        <div class="field-label">Khu vực</div>
                                                        <div class="field-value">{{ $location->region->name ?? '---' }}</div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="detail-field">
                                                        <div class="field-label">Sức chứa</div>
                                                        <div class="field-value">{{ $location->capacity ?? '---' }}</div>
                                                    </div>
                                                    <div class="detail-field">
                                                        <div class="field-label">Diện tích</div>
                                                        <div class="field-value">
                                                            {{ $location->area ? number_format($location->area, 2, ',', '.') . ' m²' : '---' }}
                                                        </div>
                                                    </div>
                                                    <div class="detail-field">
                                                        <div class="field-label">Số tầng</div>
                                                        <div class="field-value">{{ $location->floors ?? '---' }}</div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="detail-field">
                                                        <div class="field-label">Giờ phục vụ</div>
                                                        <div class="field-value">{{ $start }} - {{ $end }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="detail-actions">
                                            @can('update_location')
                                                <a href="#" class="btn tb-update" data-id="{{ $location->id }}"><i class="fa fa-check-square"></i> Cập nhật</a>
                                            @endcan
                                            @can('update_status_location')
                                                <a href="#" class="btn tb-status" data-id="{{ $location->id }}" data-status="{{ $location->status }}">
                                                    <i class="fa fa-lock"></i>
                                                    {{ $location->status === 'active' ? 'Ngừng hoạt động' : 'Cho phép hoạt động' }}
                                                </a>
                                            @endcan
                                            @can('delete_location')
                                                <a href="#" class="btn tb-delete" data-id="{{ $location->id }}"><i class="far fa-trash-alt"></i> Xóa</a>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="table-pagination" id="pagination">
                    <button id="prevPage" class="page-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span id="pageInfo"></span>
                    <button id="nextPage" class="page-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="popup-overlay" class="popup-overlay"></div>
    <div id="popup-add-region" class="popup-box">
        <h2 id="regionPopupTitle">Thêm khu vực</h2>
        <label>Tên khu vực</label>
        <input type="text" id="region-name" placeholder="Nhập tên khu vực...">
        <div class="popup-actions">
            @canany(['create_region','update_region'])
                <button id="save-popup" class="btn-save" type="button"><i class="fas fa-save"></i> Lưu</button>
            @endcanany
            <button id="cancel-popup" class="btn-cancel" type="button"><i class="fas fa-ban"></i> Hủy</button>
            @can('delete_region')
                <button id="delete-popup" class="btn-delete" type="button"><i class="far fa-trash-alt"></i> Xóa</button>
            @endcan
        </div>
    </div>

    <div id="locationFormOverlay" class="overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 id="locationFormTitle">Thêm địa điểm</h3>
                <button id="locationFormClose" class="close-btn">x</button>
            </div>
            <form id="locationForm">
                <input type="hidden" id="location_id">
                <div class="location-form-shell">
                    <section class="location-form-section">
                        <div class="location-section-head">
                            <div>
                                <h4>Thông tin cơ sở</h4>
                                <p>Cột trái dành cho ảnh đại diện, hai cột bên phải giữ phần thông tin cơ bản của cơ sở.</p>
                            </div>
                        </div>

                        <div class="location-basic-grid">
                            <div class="location-media-column">
                                <div class="location-media-card">
                                    <label>Ảnh đại diện</label>
                                    <div class="image-upload-wrap is-vertical">
                                        <div id="locationImageBox" class="location-image-box">
                                            <img id="locationPreviewImage" src="" alt="" style="display:none;">
                                            <span id="locationAddImageText">Chưa có ảnh</span>
                                            <button id="locationRemoveImageBtn" type="button" class="remove-image-btn" style="display:none;">✕</button>
                                        </div>
                                        <div class="image-upload-actions">
                                            <button type="button" id="locationChooseImage" class="image-choice-btn">Chọn ảnh</button>
                                        </div>
                                        <input type="file" id="locationImageInput" accept="image/*" hidden>
                                        <input type="hidden" id="location_thumbnail">
                                    </div>
                                </div>
                            </div>

                            <div class="location-fields-column">
                                <div class="form-group">
                                    <label>Mã địa điểm</label>
                                    <input class="write" type="text" id="location_code">
                                </div>
                                <div class="form-group">
                                    <label>Khu vực</label>
                                    <div class="staff-select" data-location-select>
                                        <button type="button" class="staff-select-trigger" id="locationRegionDisplay" aria-expanded="false" aria-controls="locationRegionMenu">
                                            <span class="staff-select-value is-placeholder" id="locationRegionText"></span>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="staff-select-menu" id="locationRegionMenu" aria-hidden="true"></div>
                                        <select class="write" id="location_region">
                                        <option value="">-- Chọn khu vực --</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Diện tích (m²)</label>
                                    <input class="write" type="number" step="0.01" id="location_area" min="0">
                                </div>
                                <div class="form-group time-input-group">
                                    <label>Giờ phục vụ</label>
                                    <div class="time-inputs">
                                        <input class="write" type="time" id="location_time_start">
                                        <span class="time-separator">→</span>
                                        <input class="write" type="time" id="location_time_end">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Link bản đồ</label>
                                    <input class="write" type="text" id="location_map_url" placeholder="https://www.google.com/maps?q=...">
                                </div>
                            </div>

                            <div class="location-fields-column">
                                <div class="form-group">
                                    <label>Tên địa điểm</label>
                                    <input class="write" type="text" id="location_name">
                                </div>
                                <div class="form-group">
                                    <label>Sức chứa</label>
                                    <input class="write" type="number" id="location_capacity" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Số tầng</label>
                                    <input class="write" type="number" id="location_floors" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <div class="staff-select" data-location-select>
                                        <button type="button" class="staff-select-trigger" id="locationStatusDisplay" aria-expanded="false" aria-controls="locationStatusMenu">
                                            <span class="staff-select-value" id="locationStatusText"></span>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="staff-select-menu" id="locationStatusMenu" aria-hidden="true"></div>
                                        <select class="write" id="location_status">
                                        <option value="active">Đang hoạt động</option>
                                        <option value="inactive">Ngừng hoạt động</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="location-form-section">
                        <div class="location-section-head">
                            <div>
                                <h4>Chi tiết trang cơ sở</h4>
                                <p>Thông tin bên dưới sẽ dùng để render trang chi tiết cơ sở ở phía user.</p>
                            </div>
                        </div>

                        <div class="detail-editor-grid">
                            <div class="form-group full-span">
                                <label>Tóm tắt ngắn</label>
                                <textarea class="write textarea-field" id="detail_summary" rows="3" placeholder="Đoạn mô tả ngắn ở đầu trang"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Tiêu đề giới thiệu</label>
                                <input class="write" type="text" id="detail_intro_title">
                            </div>
                            <div class="form-group">
                                <label>Tiêu đề cuối trang</label>
                                <input class="write" type="text" id="detail_closing_title">
                            </div>
                            <div class="form-group full-span">
                                <label>Nội dung giới thiệu</label>
                                <textarea class="write textarea-field" id="detail_intro_content" rows="5"></textarea>
                            </div>
                            <div class="form-group full-span">
                                <label>Nội dung cuối trang</label>
                                <textarea class="write textarea-field" id="detail_closing_content" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Tiêu đề thực đơn</label>
                                <input class="write" type="text" id="detail_menu_title">
                            </div>
                            <div class="form-group">
                                <label>Địa chỉ hiển thị</label>
                                <input class="write" type="text" id="detail_address">
                            </div>
                            <div class="form-group">
                                <label>Hotline</label>
                                <input class="write" type="text" id="detail_hotline">
                            </div>
                            <div class="form-group">
                                <label>Điểm đánh giá</label>
                                <input class="write" type="number" step="0.1" min="0" max="5" id="detail_rating">
                            </div>
                            <div class="form-group">
                                <label>Số lượng đánh giá</label>
                                <input class="write" type="number" min="0" id="detail_review_count">
                            </div>
                            <div class="form-group">
                                <label>Website</label>
                                <input class="write" type="text" id="detail_website_url" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>Facebook</label>
                                <input class="write" type="text" id="detail_facebook_url" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>TikTok</label>
                                <input class="write" type="text" id="detail_tiktok_url" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>Lưu ý đặt bàn</label>
                                <textarea class="write textarea-field" id="detail_booking_note" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Lưu ý gửi xe</label>
                                <textarea class="write textarea-field" id="detail_parking_note" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Thông tin thêm</label>
                                <textarea class="write textarea-field" id="detail_open_note" rows="3"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="location-form-section">
                        <div class="location-section-head">
                            <div>
                                <h4>Ảnh cho trang chi tiết</h4>
                                <p>Logo, ảnh cover và ảnh thực đơn có thể cấu hình riêng cho từng cơ sở.</p>
                            </div>
                        </div>

                        <div class="detail-media-grid">
                            <div class="detail-media-card">
                                <div class="detail-media-label">Ảnh logo / brand</div>
                                <div class="location-image-box detail-image-box">
                                    <img id="detailLogoPreviewImage" src="" alt="" style="display:none;">
                                    <span id="detailLogoAddImageText">Chưa có ảnh</span>
                                    <button id="detailLogoRemoveImageBtn" type="button" class="remove-image-btn" style="display:none;">✕</button>
                                </div>
                                <div class="image-upload-actions">
                                    <button type="button" id="detailLogoChooseImage" class="image-choice-btn">Chọn ảnh</button>
                                </div>
                                <input type="file" id="detailLogoImageInput" accept="image/*" hidden>
                                <input type="hidden" id="detail_logo_image">
                            </div>

                            <div class="detail-media-card">
                                <div class="detail-media-label">Ảnh cover đầu trang</div>
                                <div class="location-image-box detail-image-box">
                                    <img id="detailCoverPreviewImage" src="" alt="" style="display:none;">
                                    <span id="detailCoverAddImageText">Chưa có ảnh</span>
                                    <button id="detailCoverRemoveImageBtn" type="button" class="remove-image-btn" style="display:none;">✕</button>
                                </div>
                                <div class="image-upload-actions">
                                    <button type="button" id="detailCoverChooseImage" class="image-choice-btn">Chọn ảnh</button>
                                </div>
                                <input type="file" id="detailCoverImageInput" accept="image/*" hidden>
                                <input type="hidden" id="detail_cover_image">
                            </div>

                            <div class="detail-media-card">
                                <div class="detail-media-label">Ảnh thực đơn</div>
                                <div class="location-image-box detail-image-box">
                                    <img id="detailMenuPreviewImage" src="" alt="" style="display:none;">
                                    <span id="detailMenuAddImageText">Chưa có ảnh</span>
                                    <button id="detailMenuRemoveImageBtn" type="button" class="remove-image-btn" style="display:none;">✕</button>
                                </div>
                                <div class="image-upload-actions">
                                    <button type="button" id="detailMenuChooseImage" class="image-choice-btn">Chọn ảnh</button>
                                </div>
                                <input type="file" id="detailMenuImageInput" accept="image/*" hidden>
                                <input type="hidden" id="detail_menu_image">
                            </div>
                        </div>
                    </section>

                    <section class="location-form-section">
                        <div class="location-section-head">
                            <div>
                                <h4>Các block nội dung</h4>
                                <p>Mỗi block gồm tiêu đề, nội dung, ảnh và thứ tự hiển thị trên trang chi tiết.</p>
                            </div>
                            <button type="button" id="addLocationSectionBtn" class="section-add-btn">
                                <i class="fas fa-plus"></i> Thêm block
                            </button>
                        </div>

                        <div id="locationSectionsList" class="detail-sections-list"></div>

                        <template id="locationSectionTemplate">
                            <article class="detail-section-card">
                                <div class="detail-section-head">
                                    <h5>Block nội dung</h5>
                                    <button type="button" class="section-remove-btn">
                                        <i class="far fa-trash-alt"></i> Xóa block
                                    </button>
                                </div>
                                <div class="detail-section-grid">
                                    <div class="form-group">
                                        <label>Tiêu đề block</label>
                                        <input class="write section-title-input" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label>Thứ tự hiển thị</label>
                                        <input class="write section-sort-order-input" type="number" min="0">
                                    </div>
                                    <div class="form-group full-span">
                                        <label>Nội dung block</label>
                                        <textarea class="write textarea-field section-content-input" rows="5"></textarea>
                                    </div>
                                </div>
                                <div class="detail-section-image-wrap">
                                    <div class="location-image-box section-image-box">
                                        <img class="section-preview-image" src="" alt="" style="display:none;">
                                        <span class="section-add-image-text">Chưa có ảnh</span>
                                        <button type="button" class="remove-image-btn section-remove-image-btn" style="display:none;">✕</button>
                                    </div>
                                    <div class="image-upload-actions">
                                        <button type="button" class="image-choice-btn section-choose-image-btn">Chọn ảnh</button>
                                    </div>
                                    <input type="file" class="section-image-input" accept="image/*" hidden>
                                    <input type="hidden" class="section-image-path-input">
                                </div>
                            </article>
                        </template>
                    </section>
                </div>
                <div class="form-actions">
                    @canany(['create_location','update_location'])
                        <button id="locationSave" type="button" class="table-save"><i class="fas fa-save"></i> Lưu</button>
                    @endcanany
                    <button id="locationCancel" type="button" class="table-cancel"><i class="fas fa-ban"></i> Hủy</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script>
        window.routes = {
            baseUrl: "{{ url('') }}",
            assetUrl: "{{ asset('') }}",
            location: {
                store: "{{ route('pos.location.store') }}",
                showPattern: "{{ route('pos.location.show', ['id' => '__ID__']) }}",
                updatePattern: "{{ route('pos.location.update', ['id' => '__ID__']) }}",
                toggleStatusPattern: "{{ route('pos.location.toggleStatus', ['id' => '__ID__']) }}",
                deletePattern: "{{ route('pos.location.delete', ['id' => '__ID__']) }}"
            },
            region: {
                store: "{{ route('pos.region.store') }}",
                updatePattern: "{{ route('pos.region.update', ['id' => '__ID__']) }}",
                deletePattern: "{{ route('pos.region.delete', ['id' => '__ID__']) }}"
            }
        };
    </script>
    <script src="{{ asset('js/pos/location.js') }}"></script>
@endpush
