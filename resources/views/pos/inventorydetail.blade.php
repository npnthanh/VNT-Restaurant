@extends('layout.pos')

@section('title', 'VNT Pos - Kiểm Kho')

@section('content')

  @push('css')
    <link rel="stylesheet" href="{{ asset('css/pos/inventorydetail.css') }}">
  @endpush

  <meta name="base-url" content="{{ url('') }}">

  <form id="inventoryForm" method="POST" action="{{ route('inventory.store') }}">
    @csrf
    <input type="hidden" name="check_time" id="check_time" value="{{ now()->format('Y-m-d H:i') }}">
    <input type="hidden" name="status" id="inventory_status" value="draft">
    <div id="hiddenInputs"></div>

    <div class="inventory-toolbar">
        <div class="toolbar-left">
            <a href="{{ route('pos.inventory') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Kiểm kho</h1>
        </div>

        <div class="toolbar-search">
            <input type="text" placeholder="Tìm nguyên liệu theo mã hoặc tên" id="ingredientSearch">
            <div id="ingredientSuggest" class="suggest-box"></div>
        </div>
    </div>

    <div class="inventory-body">
        <div class="inventory-table-card">
            <div class="inventory-tabs">
                <button type="button" class="tab active" data-tab="all">Tất cả (<span id="countAll">0</span>)</button>
                <button type="button" class="tab" data-tab="match">Khớp (<span id="countMatch">0</span>)</button>
                <button type="button" class="tab" data-tab="diff">Lệch (<span id="countDiff">0</span>)</button>
                <button type="button" class="tab" data-tab="unchecked">Chưa kiểm (<span id="countUnchecked">0</span>)</button>
            </div>

            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã hàng hóa</th>
                        <th>Tên hàng</th>
                        <th>Tồn kho</th>
                        <th>Thực tế</th>
                        <th>SL lệch</th>
                        <th>Giá trị lệch</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody id="ingredientList">

                </tbody>
            </table>
        </div>

        <aside class="inventory-sidebar">
            <div class="sidebar-card">
                <div class="sidebar-row">
                    <span>Nhân viên</span>
                    <strong>{{ auth()->user()->name ?? '---' }}</strong>
                </div>
                <div class="sidebar-row">
                    <span>Mã kiểm kho</span>
                    <span class="muted">Mã phiếu tự động</span>
                </div>
                <div class="sidebar-row">
                    <span>Thời gian</span>
                    <input type="text" class="readonly-input" value="{{ now()->format('d/m/Y H:i') }}" readonly>
                </div>
                <div class="sidebar-row">
                    <span>Trạng thái</span>
                    <span id="statusLabel" class="status-chip">Phiếu tạm</span>
                </div>
                <div class="sidebar-row">
                    <span>Tổng SL thực tế</span>
                    <strong id="totalActual">0</strong>
                </div>
                <label for="note">Ghi chú</label>
                <textarea name="note" id="note" rows="3" placeholder="Nhập ghi chú..."></textarea>
            </div>

            <div class="sidebar-actions">
                <button type="button" class="btn-secondary" id="saveDraftBtn">
                    Lưu tạm
                </button>
                <button type="button" class="btn-primary" id="completeBtn">
                    Hoàn thành
                </button>
            </div>
        </aside>
    </div>
  </form>
@endsection

@push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/pos/inventorydetail.js') }}"></script>
@endpush
