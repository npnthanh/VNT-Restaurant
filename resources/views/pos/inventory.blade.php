@extends('layout.pos')

@section('title', 'VNT Pos - Kiểm Kho')

@section('content')

  @push('css')
    <link rel="stylesheet" href="{{ asset('css/pos/inventory.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  @endpush

  @php
      $formatQty = function ($value) {
          $formatted = number_format((float) $value, 2, ',', '.');
          return rtrim(rtrim($formatted, '0'), ',');
      };
  @endphp

  <meta name="base-url" content="{{ url('') }}">

  <div class="inventory-page">
      <!-- ===== LEFT SIDEBAR ===== -->
      <div class="sidebar">

          <!-- 🔍 TÌM KIẾM -->
          <div class="box">
              <div class="box-title">Tìm kiếm</div>
              <input type="text" id="searchCode" placeholder="Theo mã phiếu kiểm" class="search-input">
              <input type="text" id="searchIngredient" placeholder="Theo mã, tên hàng" class="search-input">
          </div>

          <!-- ⏰ THỜI GIAN -->
          <div class="box">
              <div class="box-title">Thời gian</div>

              <div class="time-dropdown">
                  <button type="button" class="input-select" id="timeBtn">
                      Toàn thời gian
                      <i class="fa fa-chevron-down"></i>
                  </button>

                  <div class="time-menu" id="timeMenu">
                      <div class="time-col">
                          <div class="time-col-title">Theo ngày</div>
                          <div class="time-item" data-preset="today">Hôm nay</div>
                          <div class="time-item" data-preset="yesterday">Hôm qua</div>
                      </div>

                      <div class="time-col">
                          <div class="time-col-title">Theo tuần</div>
                          <div class="time-item" data-preset="this_week">Tuần này</div>
                          <div class="time-item" data-preset="last_week">Tuần trước</div>
                          <div class="time-item" data-preset="last_7_days">7 ngày trước</div>
                      </div>

                      <div class="time-col">
                          <div class="time-col-title">Theo tháng</div>
                          <div class="time-item" data-preset="this_month">Tháng này</div>
                          <div class="time-item" data-preset="last_month">Tháng trước</div>
                          <div class="time-item" data-preset="last_30_days">30 ngày qua</div>
                      </div>

                      <div class="time-col">
                          <div class="time-col-title">Theo năm</div>
                          <div class="time-item" data-preset="this_year">Năm nay</div>
                          <div class="time-item" data-preset="last_year">Năm trước</div>
                          <div class="time-item" data-preset="all">Toàn thời gian</div>
                      </div>
                  </div>
              </div>

              <div class="time-custom">
                  <input
                      type="text"
                      id="dateRange"
                      class="input-text"
                      placeholder="Lựa chọn khác"
                      readonly
                  >
                  <input type="hidden" id="fromDate">
                  <input type="hidden" id="toDate">
              </div>
          </div>

          <!-- TRẠNG THÁI -->
          <div class="box collapsible">
              <div class="box-title">
                  Trạng thái
                  <span class="arrow"></span>
              </div>
              <label class="radio-item">
                  <input type="radio" name="status" value="all" checked>
                  <span>Tất cả</span>
              </label>
              <label class="radio-item">
                  <input type="radio" name="status" value="draft">
                  <span>Phiếu tạm</span>
              </label>
              <label class="radio-item">
                  <input type="radio" name="status" value="completed">
                  <span>Đã cân bằng kho</span>
              </label>
          </div>
      </div>


      <!-- ===== RIGHT CONTENT ===== -->
      <div class="content">
          <div class="content-header">
              <h2>Phiếu kiểm kho</h2>
              <a href="{{ route('inventory.detail') }}" class="btn-add">
                  <i class="far fa-plus"></i> Kiểm kho
              </a>
          </div>

          <table class="inventory-table">
              <thead>
                  <tr class="info">
                      <th>Mã kiểm kho</th>
                      <th>Thời gian</th>
                      <th>Ngày cân bằng</th>
                      <th>Tổng chênh lệch</th>
                      <th>SL lệch tăng</th>
                      <th>SL lệch giảm</th>
                      <th>Ghi chú</th>
                      <th>Trạng thái</th>
                  </tr>
              </thead>

              <tbody>
                  @forelse($checks as $check)
                    @php
                        $totalDiff = $check->details->sum('diff_qty');
                        $diffIncrease = $check->details->where('diff_qty', '>', 0)->sum('diff_qty');
                        $diffDecrease = $check->details->where('diff_qty', '<', 0)->sum('diff_qty');
                    @endphp
                    <tr class="inventory-row"
                        data-id="{{ $check->id }}"
                        data-code="{{ strtolower($check->code) }}"
                        data-status="{{ $check->status }}"
                        data-time="{{ optional($check->check_time)->timestamp ?? 0 }}"
                        data-ingredients="@foreach($check->details as $d){{ strtolower($d->ingredient->name ?? '') }} {{ strtolower($d->ingredient->code ?? '') }} @endforeach">
                        <td>{{ $check->code }}</td>
                        <td>{{ optional($check->check_time)->format('d/m/Y H:i') }}</td>
                        <td>{{ $check->balance_time ? $check->balance_time->format('d/m/Y H:i') : '---' }}</td>
                        <td>{{ $formatQty($totalDiff) }}</td>
                        <td>{{ $formatQty($diffIncrease) }}</td>
                        <td>{{ $formatQty($diffDecrease) }}</td>
                        <td>{{ $check->note ?? '---' }}</td>
                        <td>
                            {{ $check->status === 'completed' ? 'Đã cân bằng kho' : 'Phiếu tạm' }}
                        </td>
                    </tr>

                    <tr class="detail-row" id="detail-{{ $check->id }}" style="display:none;">
                        <td class="detail" colspan="8">
                            <div class="detail-box">
                                <h4>Thông tin phiếu kiểm kho</h4>
                                <div class="detail-meta">
                                    <div><strong>Mã kiểm kho:</strong> {{ $check->code }}</div>
                                    <div><strong>Nhân viên:</strong> {{ $check->staff->name ?? '---' }}</div>
                                    <div><strong>Trạng thái:</strong> {{ $check->status === 'completed' ? 'Đã cân bằng kho' : 'Phiếu tạm' }}</div>
                                    <div><strong>Thời gian:</strong> {{ optional($check->check_time)->format('d/m/Y H:i') }}</div>
                                </div>

                                <table class="detail-table">
                                    <thead>
                                        <tr>
                                            <th>Mã NL</th>
                                            <th>Tên NL</th>
                                            <th>Tồn kho</th>
                                            <th>Thực tế</th>
                                            <th>SL lệch</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($check->details as $detail)
                                        <tr>
                                            <td>{{ $detail->ingredient->code ?? '---' }}</td>
                                            <td>{{ $detail->ingredient->name ?? '---' }}</td>
                                            <td>{{ $formatQty($detail->stock_qty) }}</td>
                                            <td>{{ $formatQty($detail->actual_qty) }}</td>
                                            <td>{{ $formatQty($detail->diff_qty) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <div class="detail-summary">
                                    <div>Tổng thực tế: <strong>{{ $formatQty($check->details->sum('actual_qty')) }}</strong></div>
                                    <div>Tổng lệch tăng: <strong>{{ $formatQty($diffIncrease) }}</strong></div>
                                    <div>Tổng lệch giảm: <strong>{{ $formatQty($diffDecrease) }}</strong></div>
                                    <div>Tổng chênh lệch: <strong>{{ $formatQty($totalDiff) }}</strong></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                  @empty
                    <tr>
                        <td colspan="8" class="empty-row">Chưa có phiếu kiểm kho.</td>
                    </tr>
                  @endforelse
              </tbody>
          </table>
          <div class="inventory-pagination" id="pagination">
              <button id="prevPage" class="page-btn"><i class="fas fa-chevron-left"></i></button>
              <span id="pageInfo"></span>
              <button id="nextPage" class="page-btn"><i class="fas fa-chevron-right"></i></button>
          </div>
      </div>
  </div>
@endsection

@push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ asset('js/pos/inventory.js') }}"></script>
@endpush
