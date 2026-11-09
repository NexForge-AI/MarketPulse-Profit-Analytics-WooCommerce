/**
 * Simple Cost of Goods & Profit Tracker Admin JavaScript
 * Author: NexForge AI
 */
(function ($) {
    'use strict';

    $(function () {
        // Universal Dynamic WooCommerce Store Currency Formatter
        function formatCurrency(amount) {
            const numDecimals = (typeof scogsAdminParams !== 'undefined' && scogsAdminParams.num_decimals !== undefined) ? parseInt(scogsAdminParams.num_decimals, 10) : 2;
            const decimalSep  = (typeof scogsAdminParams !== 'undefined' && scogsAdminParams.decimal_sep) ? scogsAdminParams.decimal_sep : '.';
            const thousandSep = (typeof scogsAdminParams !== 'undefined' && scogsAdminParams.thousand_sep) ? scogsAdminParams.thousand_sep : ',';
            const symbol      = (typeof scogsAdminParams !== 'undefined' && scogsAdminParams.currency_symbol) ? scogsAdminParams.currency_symbol : '$';
            const pos         = (typeof scogsAdminParams !== 'undefined' && scogsAdminParams.currency_pos) ? scogsAdminParams.currency_pos : 'left';

            let n = parseFloat(amount) || 0;
            let parts = n.toFixed(numDecimals).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSep);
            let formattedNum = parts.join(decimalSep);

            switch (pos) {
                case 'right':
                    return formattedNum + symbol;
                case 'right_space':
                    return formattedNum + ' ' + symbol;
                case 'left_space':
                    return symbol + ' ' + formattedNum;
                case 'left':
                default:
                    return symbol + formattedNum;
            }
        }

        // 1. Pre-fill Quick Edit fields from row data
        if (typeof inlineEditPost !== 'undefined') {
            const originalInlineEdit = inlineEditPost.edit;

            inlineEditPost.edit = function (id) {
                originalInlineEdit.apply(this, arguments);

                let postId = 0;
                if (typeof id === 'object') {
                    postId = parseInt(this.getId(id), 10);
                } else if (typeof id === 'number') {
                    postId = id;
                }

                if (postId > 0) {
                    const $editRow = $('#edit-' + postId);
                    const $postRow = $('#post-' + postId);

                    const cost = $postRow.find('.scogs-raw-cost-data').data('cost');
                    const supplier = $postRow.find('.scogs-raw-supplier-data').data('supplier');

                    if (typeof cost !== 'undefined' && cost !== '') {
                        $editRow.find('input[name="_cogs_cost_price"]').val(cost);
                    }
                    if (typeof supplier !== 'undefined' && supplier !== '') {
                        $editRow.find('input[name="_cogs_supplier"]').val(supplier);
                    }
                }
            };
        }

        // 2. Real-time Live Margin calculation for Simple Products
        function calculateSimpleMargin() {
            const regularPrice = parseFloat($('#_regular_price').val()) || 0;
            const salePrice = parseFloat($('#_sale_price').val()) || 0;
            const costPrice = parseFloat($('#_cogs_cost_price').val()) || 0;

            const sellingPrice = salePrice > 0 ? salePrice : regularPrice;
            const $costInput = $('#_cogs_cost_price');

            let $wrapper = $('.scogs-live-margin');
            if (!$wrapper.length) {
                $costInput.closest('.form-field').after('<p class="form-field scogs-live-margin"><label>Est. Gross Margin</label><span class="scogs-live-badge-wrap"></span></p>');
                $wrapper = $('.scogs-live-margin');
            }

            // Remove existing inline warning chip next to cost input
            $costInput.closest('.form-field').find('.scogs-cost-inline-chip').remove();

            if (sellingPrice > 0 && costPrice > 0) {
                const profit = sellingPrice - costPrice;
                const margin = (profit / sellingPrice) * 100;

                let badgeClass = 'scogs-badge-red';
                let tipHtml = '';
                let calloutHtml = '';
                const planUrl = 'https://usemarketpulse.com/signup?plan=cmu76n3c80005n2el6itwbile&utm_source=cogs_plugin&utm_medium=product_edit_alert&utm_campaign=boost_margin';
                const brandLogoHtml = (typeof scogsAdminParams !== 'undefined' && scogsAdminParams.logo_url) ? '<img src="' + scogsAdminParams.logo_url + '" class="scogs-btn-brand-icon" alt="" />' : '';

                if (margin < 15) {
                    badgeClass = 'scogs-badge-red';
                    $costInput.removeClass('scogs-input-warning').addClass('scogs-input-danger');
                    $costInput.after('<span class="scogs-cost-inline-chip scogs-inline-danger"><span class="scogs-pulse-dot" style="width:6px;height:6px;"></span> ⚠️ ' + margin.toFixed(1) + '% Critical Margin</span>');

                    tipHtml = ' <a href="' + planUrl + '" target="_blank" class="scogs-margin-boost-tip" style="margin-left:8px;">' + brandLogoHtml + 'Boost Margin with MarketPulse &rarr;</a>';
                    calloutHtml = '<div class="scogs-margin-alert-callout scogs-callout-danger">' +
                        '<div class="scogs-callout-content">' +
                            '<span class="scogs-callout-icon">⚠️</span>' +
                            '<div class="scogs-callout-text">' +
                                '<strong>Dangerously Low Margin Alert (' + margin.toFixed(1) + '%):</strong>' +
                                'After payment gateway fees and courier deficits, your net take-home profit is nearly zero.' +
                            '</div>' +
                        '</div>' +
                        '<a href="' + planUrl + '" target="_blank" class="scogs-callout-btn">' +
                            brandLogoHtml + 'Protect & Reprice with MarketPulse AI &rarr;' +
                        '</a>' +
                    '</div>';
                } else if (margin < 30) {
                    badgeClass = 'scogs-badge-amber';
                    $costInput.removeClass('scogs-input-danger').addClass('scogs-input-warning');
                    $costInput.after('<span class="scogs-cost-inline-chip scogs-inline-warning">⚡ ' + margin.toFixed(1) + '% Low Margin</span>');

                    tipHtml = ' <a href="' + planUrl + '" target="_blank" class="scogs-margin-boost-tip" style="margin-left:8px;">' + brandLogoHtml + 'Boost Margin with MarketPulse &rarr;</a>';
                    calloutHtml = '<div class="scogs-margin-alert-callout scogs-callout-warning">' +
                        '<div class="scogs-callout-content">' +
                            '<span class="scogs-callout-icon">💡</span>' +
                            '<div class="scogs-callout-text">' +
                                '<strong>Unlock Higher Margin (' + margin.toFixed(1) + '%):</strong>' +
                                'When competitors run out of stock, automated AI repricing safely lifts prices to 30%+ with zero lost sales.' +
                            '</div>' +
                        '</div>' +
                        '<a href="' + planUrl + '" target="_blank" class="scogs-callout-btn">' +
                            brandLogoHtml + 'Optimize Price with MarketPulse &rarr;' +
                        '</a>' +
                    '</div>';
                } else {
                    badgeClass = 'scogs-badge-green';
                    $costInput.removeClass('scogs-input-danger scogs-input-warning');
                }

                const badgeHtml = '<span class="scogs-badge ' + badgeClass + '">' +
                    formatCurrency(profit) + ' (' + margin.toFixed(1) + '%)' +
                    '</span>' + tipHtml;

                $wrapper.find('.scogs-live-badge-wrap, span.scogs-badge, a.scogs-margin-boost-tip').remove();
                $wrapper.find('label').after(badgeHtml);
                $wrapper.next('.scogs-margin-alert-callout').remove();
                if (calloutHtml) {
                    $wrapper.after(calloutHtml);
                }
                $wrapper.show();
            } else {
                $costInput.removeClass('scogs-input-danger scogs-input-warning');
                if ($wrapper.length) {
                    $wrapper.hide();
                    $wrapper.next('.scogs-margin-alert-callout').remove();
                }
            }
        }

        $(document).on('input change keyup', '#_regular_price, #_sale_price, #_cogs_cost_price', function () {
            calculateSimpleMargin();
        });
        calculateSimpleMargin();
        setTimeout(calculateSimpleMargin, 150);

        // 3. Real-time Live Margin calculation for Variable Products (inside variations tab)
        $(document).on('input change keyup', '.scogs-variation-fields input[name^="_cogs_cost_price"], .woocommerce_variation input[name^="variable_regular_price"], .woocommerce_variation input[name^="variable_sale_price"]', function () {
            const $row = $(this).closest('.woocommerce_variation');
            const cost = parseFloat($row.find('input[name^="_cogs_cost_price"]').val()) || 0;
            const sale = parseFloat($row.find('input[name^="variable_sale_price"]').val()) || 0;
            const reg = parseFloat($row.find('input[name^="variable_regular_price"]').val()) || 0;

            const selling = sale > 0 ? sale : reg;
            let $badge = $row.find('.scogs-var-live-margin');

            if (cost > 0 && selling > 0) {
                const profit = selling - cost;
                const margin = (profit / selling) * 100;
                let badgeClass = 'scogs-badge-red';
                if (margin >= 30) {
                    badgeClass = 'scogs-badge-green';
                } else if (margin >= 15) {
                    badgeClass = 'scogs-badge-amber';
                }

                let tipHtml = '';
                if (margin < 30) {
                    const planUrl = 'https://usemarketpulse.com/signup?plan=cmu76n3c80005n2el6itwbile&utm_source=cogs_plugin&utm_medium=variable_edit_margin&utm_campaign=boost_margin';
                    tipHtml = ' <a href="' + planUrl + '" target="_blank" class="scogs-margin-boost-tip" style="margin-left:6px; display:inline-flex; align-items:center;">⚡ Boost margin with MarketPulse</a>';
                }

                const contentHtml = '<strong>Margin:</strong> <span class="scogs-badge ' + badgeClass + '">' + formatCurrency(profit) + ' (' + margin.toFixed(1) + '%)</span>' + tipHtml;

                if (!$badge.length) {
                    $row.find('.scogs-variation-fields').append('<div class="scogs-var-live-margin" style="margin-top:6px; font-size:12px;">' + contentHtml + '</div>');
                } else {
                    $badge.html(contentHtml);
                }
            } else if ($badge.length) {
                $badge.empty();
            }
        });

        // 4. Interactive Profit Leak Simulator Slider
        $(document).on('input change', '#scogs-interactive-slider', function () {
            const $slider = $(this);
            const pct = parseFloat($slider.val()) || 12;
            const rev = parseFloat($slider.data('revenue')) || 0;
            const currentProfit = parseFloat($slider.data('profit')) || 0;

            const leaked = Math.round(rev * (pct / 100) * 100) / 100;
            const optProfit = Math.round((currentProfit + leaked) * 100) / 100;

            $('#scogs-slider-val').text(pct);
            $('#scogs-headline-loss').html('&minus;' + formatCurrency(leaked));
            $('#scogs-card-loss').html('&minus;' + formatCurrency(leaked));
            $('#scogs-label-optimized').text(formatCurrency(optProfit));

            const total = optProfit > 0 ? optProfit : 1;
            let actualPct = Math.min(100, Math.max(15, Math.round((currentProfit / total) * 100)));
            let leakedPct = 100 - actualPct;

            $('#scogs-bar-actual-fill').css('width', actualPct + '%');
            $('#scogs-bar-leaked-fill').css('width', leakedPct + '%');

            $('#scogs-bar-actual-pct-text').text(actualPct);
            $('#scogs-bar-leaked-pct-text').text(leakedPct);

            if (currentProfit > 0) {
                const gainPct = Math.round((leaked / currentProfit) * 100);
                $('#scogs-gain-pct').text(gainPct);
            }
        });
    });
})(jQuery);

