/**
 * Simple Cost of Goods & Profit Tracker Admin JavaScript
 * Author: NexForge AI
 */
(function ($) {
    'use strict';

    $(function () {
        const currencySymbol = (typeof scogsAdminParams !== 'undefined' && scogsAdminParams.currency_symbol) ? scogsAdminParams.currency_symbol : '';

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

            let $wrapper = $('.scogs-live-margin');
            if (!$wrapper.length) {
                const targetField = $('#_cogs_supplier').closest('.form-field');
                if (targetField.length) {
                    targetField.after('<p class="form-field scogs-live-margin"><label>Est. Gross Margin</label><span class="scogs-live-badge-wrap"></span></p>');
                } else {
                    $('#_cogs_cost_price').closest('.form-field').after('<p class="form-field scogs-live-margin"><label>Est. Gross Margin</label><span class="scogs-live-badge-wrap"></span></p>');
                }
                $wrapper = $('.scogs-live-margin');
            }

            if (sellingPrice > 0 && costPrice > 0) {
                const profit = sellingPrice - costPrice;
                const margin = (profit / sellingPrice) * 100;

                let badgeClass = 'scogs-badge-red';
                if (margin >= 30) {
                    badgeClass = 'scogs-badge-green';
                } else if (margin >= 15) {
                    badgeClass = 'scogs-badge-amber';
                }

                const badgeHtml = '<span class="scogs-badge ' + badgeClass + '">' +
                    currencySymbol + profit.toFixed(2) + ' (' + margin.toFixed(1) + '%)' +
                    '</span>';

                $wrapper.find('.scogs-live-badge-wrap, span.scogs-badge').replaceWith(badgeHtml);
                $wrapper.show();
            } else if ($wrapper.length) {
                $wrapper.hide();
            }
        }

        $(document).on('input change keyup', '#_regular_price, #_sale_price, #_cogs_cost_price', function () {
            calculateSimpleMargin();
        });

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

                if (!$badge.length) {
                    $row.find('.scogs-variation-fields').append('<div class="scogs-var-live-margin" style="margin-top:6px; font-size:12px;"><strong>Margin:</strong> <span class="scogs-badge ' + badgeClass + '">' + currencySymbol + profit.toFixed(2) + ' (' + margin.toFixed(1) + '%)</span></div>');
                } else {
                    $badge.html('<strong>Margin:</strong> <span class="scogs-badge ' + badgeClass + '">' + currencySymbol + profit.toFixed(2) + ' (' + margin.toFixed(1) + '%)</span>');
                }
            } else if ($badge.length) {
                $badge.empty();
            }
        });
    });
})(jQuery);
