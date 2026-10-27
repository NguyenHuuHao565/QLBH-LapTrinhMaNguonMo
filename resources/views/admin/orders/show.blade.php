@php($money = fn ($value) => number_format($value, 0, ',', '.').'₫')

<x-admin-layout :title="'Đơn hàng '.$order->code">
    <x-slot:actions>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Danh sách đơn</a>
    </x-slot:actions>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Sản phẩm</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Sản phẩm (lúc mua)</th><th class="text-end">Đơn giá</th><th class="text-center">SL</th><th class="text-end">Thành tiền</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->product_name }}
                                        @if (! $item->product)
                                            <div class="small text-muted">Sản phẩm đã bị xóa khỏi hệ thống</div>
                                        @elseif ($item->product->finalPrice() !== $item->price)
                                            <div class="small text-muted">Giá hiện tại: {{ $money($item->product->finalPrice()) }}</div>
                                        @endif
                                    </td>
                                    {{-- Luôn hiển thị giá đã lưu trong order_items, không lấy giá hiện tại --}}
                                    <td class="text-end text-nowrap">{{ $money($item->price) }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end text-nowrap">{{ $money($item->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-semibold">
                                <td colspan="3" class="text-end">Tổng tiền</td>
                                <td class="text-end text-nowrap fs-5">{{ $money($order->total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Người nhận & giao hàng</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Người nhận</dt><dd class="col-sm-8">{{ $order->recipient_name }}</dd>
                        <dt class="col-sm-4">Điện thoại</dt><dd class="col-sm-8"><a href="tel:{{ $order->recipient_phone }}">{{ $order->recipient_phone }}</a></dd>
                        <dt class="col-sm-4">Địa chỉ</dt><dd class="col-sm-8">{{ $order->shipping_address }}</dd>
                        <dt class="col-sm-4">Ngày giao</dt><dd class="col-sm-8">{{ $order->delivery_date?->format('d/m/Y') ?? 'Sớm nhất có thể' }}</dd>
                        <dt class="col-sm-4">Ghi chú</dt><dd class="col-sm-8">{!! $order->note ? nl2br(e($order->note)) : '—' !!}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Trạng thái</div>
                <div class="card-body">
                    <p class="mb-2">Hiện tại: <x-admin-order-status :order="$order" class="fs-6" /></p>
                    @if ($order->status === 'cancelled')
                        <div class="alert alert-secondary small py-2">
                            Hủy bởi <strong>{{ $order->cancelled_by === \App\Models\User::ROLE_CUSTOMER ? 'khách hàng' : 'cửa hàng' }}</strong>
                            @if ($order->cancelled_at) lúc {{ $order->cancelled_at->format('d/m/Y H:i') }}@endif
                            @if ($order->cancel_reason)<br>Lý do: {{ $order->cancel_reason }}@endif
                            @if ($order->isPaid())<br><strong class="text-danger">Khách đã chuyển khoản — cần hoàn tiền.</strong>@endif
                        </div>
                    @endif

                    @if ($order->allowedTransitions())
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="post"
                              data-confirm="Xác nhận cập nhật trạng thái đơn {{ $order->code }}?">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="from" value="{{ $order->status }}">
                            <label for="status" class="form-label">Chuyển sang</label>
                            <select id="status" name="status" class="form-select mb-2 @error('status') is-invalid @enderror" required>
                                @foreach ($order->allowedTransitions() as $next)
                                    <option value="{{ $next }}">{{ \App\Models\Order::STATUSES[$next] }}</option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror
                            <label for="cancel_reason" class="form-label small text-muted mb-1">Lý do (chỉ dùng khi hủy đơn)</label>
                            <input type="text" id="cancel_reason" name="cancel_reason" maxlength="255" class="form-control form-control-sm mb-2"
                                   placeholder="VD: Khách không nghe máy xác nhận">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-repeat"></i> Cập nhật trạng thái</button>
                        </form>
                        <ul class="small text-muted mt-3 mb-0 ps-3">
                            <li>Hủy đơn sẽ hoàn lại tồn kho.</li>
                            <li>Hoàn thành = đã giao và đã thu tiền COD.</li>
                        </ul>
                    @else
                        <p class="small text-muted mb-0">Đơn đã ở trạng thái cuối, không thể thay đổi.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Thanh toán</div>
                <div class="card-body small">
                    <p class="mb-1">Phương thức: <strong>{{ $order->paymentMethodLabel() }}</strong></p>
                    <p class="mb-1">Tình trạng:
                        <span class="badge text-bg-{{ $order->isPaid() ? 'success' : 'warning' }}">{{ $order->paymentStatusLabel() }}</span>
                    </p>
                    @if ($order->paid_at)
                        <p class="mb-1 text-muted">Ghi nhận lúc {{ $order->paid_at->format('d/m/Y H:i') }}</p>
                    @endif

                    @if ($order->awaitsBankTransfer())
                        <p class="text-muted mt-2 mb-2">Đối soát: số tiền <strong>{{ number_format($order->total, 0, ',', '.') }}₫</strong>, nội dung <code>{{ $order->transferContent() }}</code>.</p>
                        <form action="{{ route('admin.orders.mark-paid', $order) }}" method="post"
                              data-confirm="Xác nhận đã nhận {{ number_format($order->total, 0, ',', '.') }}₫ cho đơn {{ $order->code }}?">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100"><i class="bi bi-cash-coin"></i> Xác nhận đã nhận chuyển khoản</button>
                        </form>
                    @elseif (! $order->isBankTransfer() && ! $order->isPaid())
                        <p class="text-muted mt-2 mb-0">COD: tự động chuyển “Đã thanh toán” khi đơn Hoàn thành.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Thông tin đơn</div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5">Ngày đặt</dt><dd class="col-7">{{ $order->placed_at->format('d/m/Y H:i') }}</dd>
                        <dt class="col-5">Cập nhật</dt><dd class="col-7">{{ $order->updated_at->format('d/m/Y H:i') }}</dd>
                        <dt class="col-5">Khách hàng</dt>
                        <dd class="col-7">
                            @if ($order->user)
                                <a href="{{ route('admin.customers.show', $order->user) }}">{{ $order->user->name }}</a>
                                <div class="text-muted">{{ $order->user->email }}</div>
                            @else
                                <span class="text-muted">Tài khoản đã xóa</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
