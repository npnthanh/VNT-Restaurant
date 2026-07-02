@extends('layout.pos')

@section('title', 'VNT Pos - Nguyên Liệu')

@section('content')
  
  @push('css')
    <link rel="stylesheet" href="{{ asset('css/pos/ingredient.css') }}">
  @endpush
  <meta name="csrf-token" content="{{ csrf_token() }}" data-store-url="{{ route('ingredient.category.store') }}">

  <!-- CONTENT START -->
  <div class="page-container">
    <!-- Sidebar -->
    <div class="sidebar">

      <!-- 🔍 SEARCH -->
      <div class="box">
        <h3>Tìm kiếm</h3>
        <input type="text" id="ingredient-search" placeholder="Nhập tên, mã nguyên liệu...">
      </div>

        <!-- 📦 CATEGORY -->
      <div class="box group-box">
        <div class="group-header">
          <span>Nhóm nguyên liệu</span>
          <div class="group-actions">
            @can('create_category_ingredient')
              <button type="button" class="add-group">＋</button>
            @endcan
            <span class="group-arrow"></span>
          </div>
        </div>

        <div class="group-content">
          <input type="text" class="group-search" placeholder="🔍 Tìm kiếm nhóm nguyên liệu">

          <!-- ALL -->
          <div class="group-all {{ request('category') ? '' : 'active' }}">
            <a href="{{ route('pos.ingredient', request()->except('category')) }}">Tất cả</a>
          </div>

          <ul class="group-list">
            @foreach($categories as $category)
              <li class="category-item" data-category="{{ $category->id }}">
                <span class="cat-name">{{ $category->name }}</span>
                @can('update_category_ingredient')
                <i class="fa-regular fa-pen-to-square edit-icon"></i>
                @endcan
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="main-content">
      <div class="top-bar">
        <h2>Nguyên Liệu</h2>
        @can('create_ingredient')
          <button id="btnOpenForm" class="btn-add"><i class="far fa-plus"></i> Thêm Nguyên Liệu</button>
        @endcan
      </div>

      <table class="ingredient-table">
        <thead>
          <tr>
            <th>Mã Nguyên liệu</th>
            <th>Tên Nguyên liệu</th>
            <th>Giá Vốn</th>
            <th>Tồn Kho</th>
          </tr>
        </thead>
        <tbody>
          @foreach($ingredients as $ingredient)
            <tr class="ingredient-item" data-id="{{ $ingredient->id }}" data-category-id="{{ $ingredient->category_id }}"
              data-name="{{ strtolower($ingredient->name) }}" data-code="{{ strtolower($ingredient->code) }}">
              <td class="ingredient-code">{{ $ingredient->code }}</td>
              <td class="ingredient-name">{{ $ingredient->name }}  ({{ $ingredient->unit }})</td>
              <td>{{ number_format($ingredient->price, 0, ',', '.') }}</td>
              <td>{{ str_replace('.', ',', (float)$ingredient->quantity) }}</td>
            </tr>
            <!-- Row chi tiết (ẩn) -->
            <tr class="detail-row" id="detail-{{ $ingredient->id }}" style="display:none;">
              <td colspan="6">
                  <h3>{{ $ingredient->name }}</h3>
                  <div class="detail-content">
                    <!-- Thông tin -->
                    <div class="detail-col info">
                      <div class="field">
                        <div class="field-label">Mã hàng hóa:</div>
                        <div class="field-value">{{ $ingredient->code }}</div>
                      </div>
                      <div class="field">
                        <div class="field-label">Nhóm hàng:</div>
                        <div class="field-value">{{ $ingredient->category->name ?? '---' }}</div>
                      </div>
                      <div class="field">
                        <div class="field-label">Tồn kho:</div>
                        <div class="field-value">{{ str_replace('.', ',', (float)$ingredient->quantity) }}</div>
                      </div>
                      <div class="field">
                        <div class="field-label">Giá vốn:</div>
                        <div class="field-value">{{ number_format($ingredient->price, 0, ',', '.') }}</div>
                      </div>
                    </div>
                  </div>
                  <!-- Nút -->
                  <div class="detail-actions">
                    @can('update_ingredient')
                      <a href="#" class="btn ing-update"><i class="fa fa-check-square"></i> Cập nhật</a>
                    @endcan
                    @can('delete_ingredient')
                      <a href="#" class="btn ing-delete"><i class="far fa-trash-alt"></i> Xoá</a>
                    @endcan
                  </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="ing-pagination" id="pagination">
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

  <!-- FORM Add & Edit & Delete CATEGORY START -->
  <!-- Overlay nền mờ -->
  <div id="popup-overlay" class="popup-overlay"></div>
  <!-- Popup form -->
  <div id="popup-add-group" class="popup-box">
    <h2>Thêm Nhóm Hàng</h2>
    <label>Tên nhóm</label>
    <input type="text" id="group-name" placeholder="Nhập tên nhóm...">
    <div class="popup-actions">
      @canany(['create_category_ingredient', 'update_category_ingredient'])
        <button id="save-popup" class="btn-save" type="button"><i class="fas fa-save"></i> Lưu</button>
      @endcanany
      <button id="cancel-popup" class="btn-cancel" type="button"><i class="fas fa-ban"></i> Hủy</button>
      @can('delete_category_ingredient')
        <button id="delete-popup" class="btn-delete" type="button"><i class="far fa-trash-alt"></i> Xóa</button>
      @endcan
    </div>
  </div>
  <!-- FORM Add & Edit & Delete CATEGORY END -->

  <!-- FORM ADD & EDIT & DELETE INGREDIENT START -->
  <div id="ingredientFormOverlay" class="overlay">
    <div class="modal">
      <div class="modal-header">
        <h3 id="formTitle">Thêm nguyên liệu</h3>
        <button id="btnCloseHeader" class="close-btn">×</button>
      </div>
      <!-- TAB: THÔNG TIN -->
        <form id="ingredientInfoForm">
          <input type="hidden" id="ingredient_id">
          <div class="form-group">
            <label>Mã hàng hóa</label>
            <input class="write" type="text" placeholder="Mã hàng tự động" disabled>
          </div>

          <div class="form-group">
            <label>Tên nguyên liệu</label>
            <input class="write" type="text" name="name" id="ingredient_name">
          </div>

          <div class="form-group">
            <label>Nhóm hàng</label>
            <div class="ingredient-select" data-ingredient-select>
              <button type="button" class="ingredient-select-trigger" id="ingredientCategoryDisplay" aria-expanded="false" aria-controls="ingredientCategoryMenu">
                <span class="ingredient-select-value is-placeholder" id="ingredientCategoryText"></span>
                <i class="fas fa-chevron-down"></i>
              </button>
              <div class="ingredient-select-menu" id="ingredientCategoryMenu" aria-hidden="true"></div>
              <select class="choose" name="category_id" id="category_id">
              <option value="">-- Lựa chọn --</option>
              @foreach($categories as $category) 
              <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Giá nhập</label>
            <input class="write" type="text" name="price" id="price">
          </div>

          <div class="form-group">
            <label>Đơn vị tính</label>
            <input class="write" type="text" name="unit" id="unit">
          </div>

          <div class="form-actions">
            @canany(['create_ingredient', 'update_ingredient'])
              <button id="ing-save" class="ing-save" type="button"><i class="fas fa-save"></i> Lưu</button>
            @endcanany
              <button id="cancelBtn" class="ing-cancel" type="button"><i class="fas fa-ban"></i> Hủy</button>
          </div>
        </form>
    </div>
  </div>
  <!-- FORM ADD & EDIT & DELETE INGREDIENT END -->
@endsection

@push('js')
  <script>
    window.routes = {
      storeCategory: "{{ route('ingredient.category.store') }}",
      updateCategory: "{{ route('ingredient.category.update', ':id') }}",
      deleteCategory: "{{ route('ingredient.category.delete', ':id') }}"
    };
  </script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="{{ asset('js/pos/ingredient.js') }}"></script>
@endpush
