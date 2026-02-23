/**
 * Ripple - Admin JS
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // --- Sortable platform list ---
        var $list = $('#ripple-platform-list');
        if ($list.length) {
            $list.sortable({
                handle: '.ripple-drag-handle',
                placeholder: 'ripple-sortable-placeholder',
                update: function() {
                    updatePlatformOrder();
                }
            });
            updatePlatformOrder();
        }

        function updatePlatformOrder() {
            var order = [];
            $list.find('.ripple-platform-item').each(function() {
                order.push($(this).data('slug'));
            });
            $('#ripple-platform-order').val(order.join(','));
        }

        // --- Placement toggle: hide post types when manual only ---
        $('#ripple-auto-placement').on('change', function() {
            if ($(this).val() === 'none') {
                $('.ripple-post-types-row').hide();
            } else {
                $('.ripple-post-types-row').show();
            }
        });

        // --- Floating enable toggle: show/hide floating settings ---
        $('#ripple-floating-enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ripple-floating-settings').show();
            } else {
                $('.ripple-floating-settings').hide();
            }
        });

        // --- Floating position picker ---
        $('.ripple-position-cell input[type="radio"]').on('change', function() {
            $('.ripple-position-cell').removeClass('ripple-position-active');
            $(this).closest('.ripple-position-cell').addClass('ripple-position-active');
        });

        // --- Color mode toggle ---
        $('#ripple-color-mode').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.ripple-custom-colors').show();
            } else {
                $('.ripple-custom-colors').hide();
            }
        });

        // --- Color picker ---
        $('.ripple-color-field').wpColorPicker();

        // --- Display mode card selection ---
        $('.ripple-mode-card input[type="radio"]').on('change', function() {
            $('.ripple-mode-card').removeClass('ripple-mode-card-active');
            $(this).closest('.ripple-mode-card').addClass('ripple-mode-card-active');
        });

        // --- Shape picker selection ---
        $('.ripple-shape-option input[type="radio"]').on('change', function() {
            $('.ripple-shape-option').removeClass('selected');
            $(this).closest('.ripple-shape-option').addClass('selected');
        });

        // --- Border radius type toggle ---
        $('.ripple-radius-toggle').on('change', function() {
            var type = $('input[name="ripple_settings[border_radius_type]"]:checked').val();
            if (type === 'custom') {
                $('.ripple-custom-radius-input').show();
            } else {
                $('.ripple-custom-radius-input').hide();
            }
        });

        // --- Range slider value sync ---
        $('.ripple-range-slider').on('input', function() {
            var val = $(this).val();
            $(this).next('.ripple-range-value').text(val + 'px');
        });

        // --- Color preset card selection ---
        $('.ripple-preset-card input[type="radio"]').on('change', function() {
            var preset = $(this).val();
            $('.ripple-preset-card').removeClass('ripple-preset-active');
            $(this).closest('.ripple-preset-card').addClass('ripple-preset-active');

            // Show/hide per-variant color pickers when custom is selected
            if (preset === 'custom') {
                $('.ripple-color-subtabs-wrapper').show();
            } else {
                $('.ripple-color-subtabs-wrapper').hide();
            }
        });

        // --- Color subtab switching ---
        $('.ripple-color-subtab').on('click', function(e) {
            e.preventDefault();
            var variant = $(this).data('variant');

            // Remove active class from all subtabs
            $('.ripple-color-subtab').removeClass('active');
            $(this).addClass('active');

            // Hide all color panels
            $('.ripple-color-panel').removeClass('active').hide();

            // Show selected panel
            $('.ripple-color-panel-' + variant).addClass('active').show();
        });

        // Initialize border radius toggle state
        var radiusType = $('input[name="ripple_settings[border_radius_type]"]:checked').val();
        if (radiusType === 'custom') {
            $('.ripple-custom-radius-input').show();
        } else {
            $('.ripple-custom-radius-input').hide();
        }

        // Initialize preset state
        var preset = $('input[name="ripple_settings[color_preset]"]:checked').val();
        if (preset === 'custom') {
            $('.ripple-color-subtabs-wrapper').show();
        } else {
            $('.ripple-color-subtabs-wrapper').hide();
        }

        // --- Media uploader for fallback image ---
        $('#ripple-upload-image').on('click', function(e) {
            e.preventDefault();

            var frame = wp.media({
                title: 'Choose Fallback Image',
                button: { text: 'Use This Image' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#ripple-default-image').val(attachment.url);
            });

            frame.open();
        });
    });

})(jQuery);
