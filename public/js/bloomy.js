// Bloomy Flower Shop — tương tác nhỏ phía khách hàng (không dùng framework)
document.addEventListener('DOMContentLoaded', () => {
    // Ô sắp xếp: đổi lựa chọn là tự gửi form
    document.querySelectorAll('select[data-autosubmit]').forEach((select) => {
        select.addEventListener('change', () => select.form.submit());
    });

    // Nút tăng/giảm số lượng, giữ giá trị trong khoảng [min, max]
    document.querySelectorAll('[data-qty]').forEach((stepper) => {
        const input = stepper.querySelector('input');
        const clamp = (value) => {
            const min = Number(input.min) || 1;
            const max = Number(input.max) || Infinity;
            return Math.min(Math.max(Number(value) || min, min), max);
        };

        stepper.querySelectorAll('[data-qty-step]').forEach((button) => {
            button.addEventListener('click', () => {
                input.value = clamp(Number(input.value) + Number(button.dataset.qtyStep));
            });
        });
        input.addEventListener('change', () => { input.value = clamp(input.value); });
    });

    // Form đặt hàng: khóa nút sau lần bấm đầu để tránh double-click
    // (server vẫn chống trùng bằng idempotency key, đây chỉ là lớp bảo vệ phía giao diện)
    document.querySelectorAll('form[data-submit-once]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitted) {
                event.preventDefault();
                return;
            }
            form.dataset.submitted = '1';
            form.querySelectorAll('button[type="submit"]').forEach((button) => {
                button.disabled = true;
                if (button.dataset.loadingText) button.textContent = button.dataset.loadingText;
            });
        });
    });
});
