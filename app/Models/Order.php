<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

// code, total và status không cho gán hàng loạt: do server tự sinh / tự tính
#[Fillable(['user_id', 'recipient_name', 'recipient_phone', 'shipping_address', 'note', 'delivery_date', 'payment_method'])]
class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    /**
     * Quy tắc chuyển trạng thái: chỉ đi tới, completed và cancelled là trạng thái cuối.
     */
    public const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipping', 'cancelled'],
        'shipping' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * Doanh thu chỉ tính đơn đã hoàn thành (đã giao và đã thu tiền COD).
     */
    public const REVENUE_STATUS = 'completed';

    public const PAYMENT_METHODS = [
        'cod' => 'Thanh toán khi nhận hàng (COD)',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->code ??= static::generateCode();
            $order->status ??= 'pending';
            // COD: khách chưa trả tiền khi đặt, chỉ chuyển 'paid' khi đã thu tiền thực tế
            $order->payment_status ??= 'unpaid';
            $order->total ??= 0;
            $order->placed_at ??= now();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Mã đơn dạng BL + yyMMdd + 5 ký tự, ví dụ BL261021K7Q2M.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'BL'.now()->format('ymd').Str::upper(Str::random(5));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'placed_at' => 'datetime',
            'total' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Tính lại tổng tiền từ các dòng hàng đã lưu trong CSDL,
     * không dùng số tiền gửi lên từ trình duyệt.
     */
    public function recalculateTotal(): void
    {
        $this->total = (int) $this->items()->sum('subtotal');
        $this->save();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * @return list<string>
     */
    public function allowedTransitions(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function scopeCountedInRevenue(Builder $query): Builder
    {
        return $query->where('status', self::REVENUE_STATUS);
    }

    /**
     * Màu badge Bootstrap cho từng trạng thái (dùng ở trang quản trị).
     */
    public const STATUS_COLORS = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'shipping' => 'primary',
        'completed' => 'success',
        'cancelled' => 'secondary',
    ];

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'light';
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }
}
