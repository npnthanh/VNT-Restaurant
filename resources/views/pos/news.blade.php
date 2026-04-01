@extends('layout.pos')

@section('title', 'VNT Pos - Quản lý tin tức')

@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/pos/news.css') }}">
    @endpush

    <div class="news-management-page">
        <aside class="news-sidebar">
            <div class="news-filter-card">
                <div class="news-filter-title">Tìm kiếm</div>
                <input type="text" id="newsSearchInput" class="news-filter-input" placeholder="Theo tiêu đề hoặc slug">
            </div>

            <div class="news-filter-card">
                <div class="news-filter-title">Danh mục</div>
                <div class="news-category-select-wrapper">
                    <div class="custom-dropdown" id="newsCategoryDropdown">
                        <div class="selected-display">
                            <span id="currentNewsCategoryText">Tất cả danh mục</span>
                            <i class="fa-solid fa-chevron-down arrow-icon"></i>
                        </div>
                        <ul class="dropdown-list">
                            <li data-value="">Tất cả danh mục</li>
                            <li data-value="Ưu đãi">Ưu đãi</li>
                            <li data-value="Sự kiện">Sự kiện</li>
                            <li data-value="Tin thương hiệu">Tin thương hiệu</li>
                            <li data-value="Thông báo">Thông báo</li>
                        </ul>
                        <input type="hidden" id="newsCategoryFilter" value="">
                    </div>
                </div>
            </div>

            <div class="news-filter-card">
                <div class="news-filter-title">Trạng thái</div>
                <label class="news-radio-item">
                    <input type="radio" name="news_status" value="all" checked>
                    <span>Tất cả</span>
                </label>
                <label class="news-radio-item">
                    <input type="radio" name="news_status" value="published">
                    <span>Đã xuất bản</span>
                </label>
                <label class="news-radio-item">
                    <input type="radio" name="news_status" value="draft">
                    <span>Bản nháp</span>
                </label>
            </div>
        </aside>

        <section class="news-content">
            <div class="news-content-header">
                <div>
                    <h2>Danh sách bài viết</h2>
                    <p>Quản lý bài viết hiển thị ở trang tin tức người dùng.</p>
                </div>
                @can('create_news')
                    <button type="button" class="news-create-btn" id="openNewsModalBtn">
                        <i class="fas fa-plus"></i>
                        <span>Tạo bài viết</span>
                    </button>
                @endcan
            </div>

            <div class="news-table-wrap">
                <table class="news-table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Danh mục</th>
                            <th>Slug</th>
                            <th>Trạng thái</th>
                            <th>Nổi bật</th>
                            <th>Ngày đăng</th>
                            <th>Cập nhật</th>
                        </tr>
                    </thead>
                    <tbody id="newsTableBody">
                        @forelse($newsItems as $item)
                            <tr class="news-row"
                                data-id="{{ $item->id }}"
                                data-search="{{ \Illuminate\Support\Str::lower($item->title . ' ' . $item->slug) }}"
                                data-category="{{ $item->category }}"
                                data-status="{{ $item->status }}"
                                data-published="{{ optional($item->published_at)->timestamp ?? 0 }}">
                                <td class="news-thumb-cell">
                                    <img
                                        class="news-thumb"
                                        src="{{ asset($item->image ?: 'images/news/news4.png') }}"
                                        alt="{{ $item->title }}">
                                </td>
                                <td class="news-title-cell">{{ $item->title }}</td>
                                <td>{{ $item->category }}</td>
                                <td><code>{{ $item->slug }}</code></td>
                                <td>
                                    <span class="news-status-badge {{ $item->status === 'published' ? 'is-published' : 'is-draft' }}">
                                        {{ $item->status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
                                    </span>
                                </td>
                                <td>{{ $item->is_featured ? 'Có' : 'Không' }}</td>
                                <td>{{ optional($item->published_at)->format('d/m/Y H:i') ?: '--' }}</td>
                                <td>{{ optional($item->updated_at)->format('d/m/Y H:i') ?: '--' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="news-empty-row">Chưa có bài viết nào trong bảng `news`.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="news-pagination" id="newsPagination">
                <button type="button" class="page-btn" id="newsPrevPage"><i class="fas fa-chevron-left"></i></button>
                <span id="newsPageInfo"></span>
                <button type="button" class="page-btn" id="newsNextPage"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>
    </div>

    <div class="news-modal" id="newsModal" style="display:none;">
        <div class="news-modal-content">
            <div class="news-modal-header">
                <h3 id="newsModalTitle">Tạo bài viết</h3>
                <button type="button" class="news-modal-close" id="closeNewsModalBtn">&times;</button>
            </div>

            <form id="newsForm" class="news-form" method="POST" action="{{ route('news.admin.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="news-form-grid">
                    <div class="form-group">
                        <label for="news_title">Tiêu đề</label>
                        <input type="text" id="news_title" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="news_slug">Slug</label>
                        <input type="text" id="news_slug" name="slug" class="form-control" placeholder="Để trống để tự tạo">
                    </div>

                    <div class="form-group">
                        <label for="news_category">Danh mục</label>
                        <div class="news-form-select-wrapper">
                            <div class="custom-dropdown" id="newsCategoryFormDropdown">
                                <div class="selected-display" tabindex="0" role="button" aria-haspopup="listbox" aria-expanded="false">
                                    <span id="currentNewsFormCategoryText">Ưu đãi</span>
                                    <i class="fa-solid fa-chevron-down arrow-icon"></i>
                                </div>
                                <ul class="dropdown-list" role="listbox">
                                    <li data-value="Ưu đãi">Ưu đãi</li>
                                    <li data-value="Sự kiện">Sự kiện</li>
                                    <li data-value="Tin thương hiệu">Tin thương hiệu</li>
                                    <li data-value="Thông báo">Thông báo</li>
                                </ul>
                                <input type="hidden" id="news_category" name="category" value="Ưu đãi" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="news_status">Trạng thái</label>
                        <div class="news-form-select-wrapper">
                            <div class="custom-dropdown" id="newsStatusDropdown">
                                <div class="selected-display" tabindex="0" role="button" aria-haspopup="listbox" aria-expanded="false">
                                    <span id="currentNewsStatusText">Bản nháp</span>
                                    <i class="fa-solid fa-chevron-down arrow-icon"></i>
                                </div>
                                <ul class="dropdown-list" role="listbox">
                                    <li data-value="draft">Bản nháp</li>
                                    <li data-value="published">Đã xuất bản</li>
                                </ul>
                                <input type="hidden" id="news_status" name="status" value="draft" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="news_published_at">Ngày đăng</label>
                        <input type="datetime-local" id="news_published_at" name="published_at" class="form-control">
                    </div>

                    <div class="form-group form-group-checkbox">
                        <label class="checkbox-label">
                            <input type="checkbox" id="news_is_featured" name="is_featured" value="1">
                            <span>Đánh dấu là bài nổi bật</span>
                        </label>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="news_summary">Tóm tắt</label>
                        <textarea id="news_summary" name="summary" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="news_content">Nội dung chi tiết</label>
                        <textarea id="news_content" name="content" class="form-control news-content-input" rows="12"></textarea>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="news_image">Ảnh đại diện</label>
                        <div class="news-upload-field">
                            <input type="file" id="news_image" name="image" class="news-file-input" accept="image/*">
                            <div class="news-file-picker">
                                <button type="button" class="news-file-trigger" id="newsFileTrigger">
                                    <i class="fas fa-image"></i>
                                    <span>Chọn ảnh</span>
                                </button>
                                <span class="news-file-name" id="newsFileName">Chưa chọn ảnh nào</span>
                            </div>
                            <p class="news-file-hint">Ưu tiên ảnh ngang, dung lượng tối đa 5MB.</p>
                        </div>
                    </div>
                </div>

                <div class="news-image-preview" id="newsImagePreview" hidden>
                    <img id="newsPreviewImage" src="" alt="Preview ảnh bài viết">
                </div>

                <div class="news-form-actions">
                    @can('update_news')
                        <button type="submit" class="news-save-btn">
                            <i class="fas fa-save"></i>
                            <span>Lưu bài viết</span>
                        </button>
                    @endcan
                    <button type="button" class="news-cancel-btn" id="cancelNewsBtn">
                        <i class="fas fa-ban"></i>
                        <span>Hủy</span>
                    </button>
                    @can('delete_news')
                        <button type="button" class="news-delete-btn" id="deleteNewsBtn" style="display:none;">
                            <i class="far fa-trash-alt"></i>
                            <span>Xóa</span>
                        </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('js/pos/news.js') }}"></script>
@endpush
