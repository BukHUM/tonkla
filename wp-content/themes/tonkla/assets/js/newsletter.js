(function () {
    'use strict';

    var COLOR_ERROR = '#EF4444';

    function brandColor() {
        if (typeof tonkla_ajax !== 'undefined' && tonkla_ajax.brand) {
            return tonkla_ajax.brand;
        }
        var root = getComputedStyle(document.documentElement).getPropertyValue('--tk-brand').trim();
        if (root) {
            return 'rgb(' + root.replace(/\s+/g, ', ') + ')';
        }
        return '#10b981';
    }

    function showAlert(icon, title, text, buttonText) {
        if (typeof Swal === 'undefined') {
            return;
        }
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: buttonText,
            confirmButtonColor: icon === 'success' ? brandColor() : COLOR_ERROR
        });
    }

    function setLoading(btn, isLoading) {
        if (!btn) {
            return;
        }
        var btnText = btn.querySelector('.btn-text');
        var btnLoading = btn.querySelector('.btn-loading');
        if (btnText) {
            btnText.classList.toggle('hidden', isLoading);
        }
        if (btnLoading) {
            btnLoading.classList.toggle('hidden', !isLoading);
        }
        btn.disabled = isLoading;
    }

    function fieldValue(form, name) {
        var input = form.querySelector('input[name="' + name + '"]');
        return input ? input.value : '';
    }

    function handleSubmit(e) {
        var form = e.target.closest('.newsletter-form, #newsletter-form');
        if (!form) {
            return;
        }
        e.preventDefault();

        var btn = form.querySelector('button[type="submit"]');
        setLoading(btn, true);

        var body = new URLSearchParams();
        body.append('action', 'tonkla_newsletter_subscribe');
        body.append('security', tonkla_ajax.nonce);
        body.append('email', fieldValue(form, 'email'));
        body.append('hp_field', fieldValue(form, 'hp_field'));

        fetch(tonkla_ajax.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (response) {
                var message = response && response.data ? response.data.message : '';
                if (response && response.success) {
                    showAlert('success', 'สำเร็จ!', message, 'ตกลง');
                    form.reset();
                } else {
                    showAlert('error', 'เกิดข้อผิดพลาด', message, 'ลองใหม่อีกครั้ง');
                }
            })
            .catch(function () {
                showAlert('error', 'เกิดข้อผิดพลาด', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่ภายหลัง', 'ตกลง');
            })
            .finally(function () {
                setLoading(btn, false);
            });
    }

    function handleActivationRedirect() {
        var status = tonkla_ajax.activated;
        if (!status) {
            return;
        }

        if (status === '1') {
            showAlert('success', 'สำเร็จ!', 'ยืนยันการสมัครรับข่าวสารเรียบร้อยแล้ว', 'ตกลง');
        } else if (status === 'expired') {
            showAlert('error', 'ลิงก์หมดอายุ', 'ลิงก์ยืนยันหมดอายุแล้ว กรุณาสมัครใหม่อีกครั้ง', 'ตกลง');
        }

        var url = new URL(window.location.href);
        url.searchParams.delete('newsletter_activated');
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('submit', handleSubmit);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', handleActivationRedirect);
    } else {
        handleActivationRedirect();
    }
})();
