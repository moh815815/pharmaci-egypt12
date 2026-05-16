/**
 * Egyptian Pharmacies Directory - Main JavaScript
 */

(function($) {
    'use strict';

    var BASE_URL = window.BASE_URL || 'http://localhost:8080';

    $(document).ready(function() {

        // ====== Scroll to Top ======
        var scrollBtn = $('#scrollTopBtn');
        $(window).on('scroll', function() {
            scrollBtn.toggleClass('show', $(this).scrollTop() > 400);
        });
        scrollBtn.on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 400);
        });

        // ====== Auto-hide Flash Messages ======
        setTimeout(function() {
            $('.alert-dismissible').fadeOut(500);
        }, 5000);

        // ====== Mobile Sidebar ======
        $('#sidebarToggle').on('click', function() {
            $('.admin-sidebar').toggleClass('show');
        });
        $(document).on('click', function(e) {
            if ($(window).width() <= 991 && !$(e.target).closest('.admin-sidebar, #sidebarToggle').length) {
                $('.admin-sidebar').removeClass('show');
            }
        });

        // ====== Dynamic Location Dropdowns ======
        $(document).on('change', 'select[name="governorate_id"]', function() {
            var govId = $(this).val();
            var $form = $(this).closest('form');
            var $city = $form.find('select[name="city_id"]');
            var $street = $form.find('select[name="street_id"]');

            $city.html('<option value="">-- اختر المدينة --</option>').prop('disabled', !govId);
            $street.html('<option value="">-- اختر الشارع --</option>').prop('disabled', true);

            if (!govId) return;

            $.ajax({
                url: BASE_URL + '/ajax/get-cities.php',
                type: 'POST',
                data: { governorate_id: govId },
                dataType: 'json',
                beforeSend: function() { $city.prop('disabled', true); },
                success: function(response) {
                    $city.prop('disabled', false);
                    if (response.success && response.data.length) {
                        $.each(response.data, function(i, city) {
                            $city.append('<option value="' + city.id + '">' + city.name_ar + '</option>');
                        });
                    }
                },
                error: function() { $city.prop('disabled', false); }
            });
        });

        $(document).on('change', 'select[name="city_id"]', function() {
            var cityId = $(this).val();
            var $form = $(this).closest('form');
            var $street = $form.find('select[name="street_id"]');

            $street.html('<option value="">-- اختر الشارع --</option>').prop('disabled', !cityId);

            if (!cityId) return;

            $.ajax({
                url: BASE_URL + '/ajax/get-streets.php',
                type: 'POST',
                data: { city_id: cityId },
                dataType: 'json',
                beforeSend: function() { $street.prop('disabled', true); },
                success: function(response) {
                    $street.prop('disabled', false);
                    if (response.success && response.data.length) {
                        $.each(response.data, function(i, street) {
                            $street.append('<option value="' + street.id + '">' + street.name_ar + '</option>');
                        });
                    }
                },
                error: function() { $street.prop('disabled', false); }
            });
        });

        // ====== File Upload Preview (Photos) ======
        if ($('#pharmacyPhotos').length) {
            $('#pharmacyPhotos').on('change', function() {
                var $preview = $('#photoPreview').empty();
                var files = this.files;

                $.each(files, function(i, file) {
                    if (!file.type.match('image.*')) return;
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var $item = $('<div class="preview-item"></div>');
                        $item.append('<img src="' + e.target.result + '" alt="صورة" loading="lazy">');
                        $item.append('<button type="button" class="remove-btn" title="إزالة"><i class="fas fa-times"></i></button>');
                        $preview.append($item);
                    };
                    reader.readAsDataURL(file);
                });
                $('#photoCount').text(files.length + ' صور مختارة');
            });

            $(document).on('click', '.preview-item .remove-btn', function() {
                $(this).closest('.preview-item').remove();
                $('#photoCount').text($('#photoPreview .preview-item').length + ' صور');
            });
        }

        // ====== License File Preview ======
        if ($('#licenseFile').length) {
            $('#licenseFile').on('change', function() {
                var file = this.files[0];
                if (!file) { $('#licenseName').text('لم يتم اختيار ملف'); return; }
                $('#licenseName').text(file.name + ' (' + (file.size / 1024).toFixed(1) + ' كيلوبايت)');
            });
        }

        // ====== Logo Preview ======
        if ($('#pharmacyLogo').length) {
            $('#pharmacyLogo').on('change', function() {
                var file = this.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#logoPreview').html('<img src="' + e.target.result + '" class="img-fluid rounded shadow-sm" style="max-height:120px;" alt="شعار الصيدلية">');
                };
                reader.readAsDataURL(file);
            });
        }

        // ====== Quick Search ======
        var searchTimeout;
        $('#quickSearch').on('input', function() {
            clearTimeout(searchTimeout);
            var query = $(this).val();

            if (query.length < 2) { $('#quickResults').hide(); return; }

            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: BASE_URL + '/ajax/quick-search.php',
                    type: 'GET',
                    data: { q: query },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#quickResults').html('<div class="p-3 text-center text-muted"><div class="spinner-border spinner-border-sm"></div> جاري البحث...</div>').show();
                    },
                    success: function(response) {
                        if (response.success && response.data.length) {
                            var html = '';
                            $.each(response.data, function(i, p) {
                                html += '<a href="' + BASE_URL + '/pharmacy.php?id=' + p.id + '" class="quick-result-item">';
                                html += '<div class="qr-name">' + p.name_ar + '</div>';
                                html += '<div class="qr-location">' + p.city_name + ' - ' + p.street_name + '</div></a>';
                            });
                            $('#quickResults').html(html).show();
                        } else {
                            $('#quickResults').html('<div class="p-3 text-center text-muted">لا توجد نتائج</div>').show();
                        }
                    },
                    error: function() { $('#quickResults').hide(); }
                });
            }, 400);
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#quickSearch, #quickResults').length) {
                $('#quickResults').hide();
            }
        });

        // ====== Filter Chips ======
        $(document).on('click', '.filter-chip', function() {
            $(this).toggleClass('active');
            var input = $(this).data('input');
            if (input) {
                $('#' + input).val($(this).hasClass('active') ? 1 : '');
            }
        });

        // ====== Confirm Dialogs ======
        $(document).on('click', '[data-confirm]', function(e) {
            if (!confirm($(this).data('confirm') || 'هل أنت متأكد من هذا الإجراء؟')) {
                e.preventDefault();
            }
        });

        // ====== Tooltips ======
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            new bootstrap.Tooltip(el);
        });

        // ====== Admin Live Search ======
        var adminSearchTimeout;
        $('#adminSearchInput').on('input', function() {
            clearTimeout(adminSearchTimeout);
            var query = $(this).val();
            adminSearchTimeout = setTimeout(function() {
                $('.admin-table tbody tr').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(query.toLowerCase()) > -1 || query.length === 0);
                });
            }, 300);
        });

        // ====== Footer Governorates ======
        var $footerGovs = $('#footerGovernorates');
        if ($footerGovs.length) {
            $.ajax({
                url: BASE_URL + '/ajax/get-cities.php',
                type: 'POST',
                data: { governorate_id: 0 },
                dataType: 'json',
                timeout: 2000,
                success: function(response) {
                    if (response.success && response.data) return;
                }
            });
        }

    });

})(jQuery);

// ====== Google Maps ======
function initPharmacyMap(lat, lng, title) {
    if (typeof google === 'undefined' || !lat || !lng) return;
    var mapEl = document.getElementById('pharmacyMap');
    if (!mapEl) return;

    var pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
    var map = new google.maps.Map(mapEl, {
        center: pos, zoom: 16, mapTypeId: 'roadmap',
        mapTypeControl: false, streetViewControl: false, fullscreenControl: true,
    });
    new google.maps.Marker({
        position: pos, map: map, title: title || 'موقع الصيدلية', animation: google.maps.Animation.DROP,
    });
}
