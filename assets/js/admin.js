/**
 * Ripple - Admin JS
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // --- Sortable platform list ---
        var $list = $('#webworq-ss-platform-list');
        if ($list.length) {
            $list.sortable({
                handle: '.webworq-ss-drag-handle',
                placeholder: 'webworq-ss-sortable-placeholder',
                update: function() {
                    updatePlatformOrder();
                }
            });
            updatePlatformOrder();
        }

        function updatePlatformOrder() {
            var order = [];
            $list.find('.webworq-ss-platform-item').each(function() {
                order.push($(this).data('slug'));
            });
            $('#webworq-ss-platform-order').val(order.join(','));
        }

        // --- Placement toggle: hide post types when manual only ---
        $('#webworq-ss-auto-placement').on('change', function() {
            if ($(this).val() === 'none') {
                $('.webworq-ss-post-types-row').hide();
            } else {
                $('.webworq-ss-post-types-row').show();
            }
        });

        // --- Floating enable toggle: show/hide floating settings ---
        $('#webworq-ss-floating-enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.webworq-ss-floating-settings').show();
            } else {
                $('.webworq-ss-floating-settings').hide();
            }
        });

        // --- Floating position picker ---
        $('.webworq-ss-position-cell input[type="radio"]').on('change', function() {
            $('.webworq-ss-position-cell').removeClass('webworq-ss-position-active');
            $(this).closest('.webworq-ss-position-cell').addClass('webworq-ss-position-active');
        });

        // --- Color mode toggle ---
        $('#webworq-ss-color-mode').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.webworq-ss-custom-colors').show();
            } else {
                $('.webworq-ss-custom-colors').hide();
            }
        });

        // --- Color picker ---
        $('.webworq-ss-color-field').wpColorPicker();

        // --- Display mode card selection ---
        $('.webworq-ss-mode-card input[type="radio"]').on('change', function() {
            $('.webworq-ss-mode-card').removeClass('webworq-ss-mode-card-active');
            $(this).closest('.webworq-ss-mode-card').addClass('webworq-ss-mode-card-active');
        });

        // --- Shape picker selection ---
        $('.webworq-ss-shape-option input[type="radio"]').on('change', function() {
            $('.webworq-ss-shape-option').removeClass('selected');
            $(this).closest('.webworq-ss-shape-option').addClass('selected');
        });

        // --- Border radius type toggle ---
        $('.webworq-ss-radius-toggle').on('change', function() {
            var type = $('input[name="webworq_ss_settings[border_radius_type]"]:checked').val();
            if (type === 'custom') {
                $('.webworq-ss-custom-radius-input').show();
            } else {
                $('.webworq-ss-custom-radius-input').hide();
            }
        });

        // --- Range slider value sync ---
        $('.webworq-ss-range-slider').on('input', function() {
            var val = $(this).val();
            $(this).next('.webworq-ss-range-value').text(val + 'px');
        });

        // --- Color preset card selection ---
        $('.webworq-ss-preset-card input[type="radio"]').on('change', function() {
            var preset = $(this).val();
            $('.webworq-ss-preset-card').removeClass('webworq-ss-preset-active');
            $(this).closest('.webworq-ss-preset-card').addClass('webworq-ss-preset-active');

            // Show/hide per-variant color pickers when custom is selected
            if (preset === 'custom') {
                $('.webworq-ss-color-subtabs-wrapper').show();
            } else {
                $('.webworq-ss-color-subtabs-wrapper').hide();
            }
        });

        // --- Color subtab switching ---
        $('.webworq-ss-color-subtab').on('click', function(e) {
            e.preventDefault();
            var variant = $(this).data('variant');

            // Remove active class from all subtabs
            $('.webworq-ss-color-subtab').removeClass('active');
            $(this).addClass('active');

            // Hide all color panels
            $('.webworq-ss-color-panel').removeClass('active').hide();

            // Show selected panel
            $('.webworq-ss-color-panel-' + variant).addClass('active').show();
        });

        // Initialize border radius toggle state
        var radiusType = $('input[name="webworq_ss_settings[border_radius_type]"]:checked').val();
        if (radiusType === 'custom') {
            $('.webworq-ss-custom-radius-input').show();
        } else {
            $('.webworq-ss-custom-radius-input').hide();
        }

        // Initialize preset state
        var preset = $('input[name="webworq_ss_settings[color_preset]"]:checked').val();
        if (preset === 'custom') {
            $('.webworq-ss-color-subtabs-wrapper').show();
        } else {
            $('.webworq-ss-color-subtabs-wrapper').hide();
        }

        // --- Media uploader for fallback image ---
        $('#webworq-ss-upload-image').on('click', function(e) {
            e.preventDefault();

            var frame = wp.media({
                title: 'Choose Fallback Image',
                button: { text: 'Use This Image' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#webworq-ss-default-image').val(attachment.url);
            });

            frame.open();
        });
    });

})(jQuery);
