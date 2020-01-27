$(document).ready(function () {
    "use strict";

    var typingTimer;                //timer identifier
    var doneTypingInterval = 1000;  //time in ms (5 seconds)
    $(".alert").alert();

    $('#input-search').on("keyup", function (e) {
        clearTimeout(typingTimer);
        var query = $(this).val();
        if (query.length >= 3) {
            typingTimer = setTimeout(doneTypingSearch, doneTypingInterval, query);
        } else {
            $("#productList tr").show("slow");
        }
    });

    $(".counter-input-number[max!='0']").keyup(function (event) {
        if (event.keyCode === 13) {
            var productId = $(this).attr("input-product-id");
            $("#add-to-cart-button-" + productId).click();
        }
    });

    function doneTypingSearch(query) {
        let spinner = $("#search-spinner");
        spinner.show();
        $.ajax({
            url: 'search/?q=' + query,
            type: 'GET',
            success: function (data) {
                onSearchSucces(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            },
            complete: function () {
                spinner.hide();
            }
        });
    }

    function onSearchSucces(data) {
        if (jQuery.isEmptyObject(data)) {
            $("#productList tr").show();
        } else {
            handleSerachResult(data);
        }
    }

    function handleSerachResult(items) {
        var productTableBody = $("#productList tbody");
        $("#productList tr").hide("slow");

        items.forEach(function (stock) {
            var productRow = $('tr[car-id=' + stock._source.car.id + ']');

            productRow.hide();
            productTableBody.prepend(productRow.slideDown('slow'));
        });
    }

    $('.add-to-cart-btn').click(function (e) {
        e.preventDefault();
        var productId = $(this).attr('product-id');
        var quantity = parseInt($('#counter-' + productId).val());

        addProductToCart(productId, quantity);
    });

    function addProductToCart(productId, quantity) {
        $.ajax({
            url: 'cart/product/' + productId + '/create/?quantity=' + quantity,
            type: 'POST',
            beforeSend: function () {
                $("#add-to-cart-button-" + productId).attr("disabled", true);
                $("#add-to-cart-button-" + productId + " i").hide();
                $("#add-to-cart-button-" + productId + " span.spinner").show();
            },
            success: function (data) {
                onItemAddSucces(productId, data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    showCloseButton: true,
                    showCancelButton: true,
                    icon: 'error',
                    title: 'Oops...',
                    text: jqXHR.responseText,
                }).then(function () {
                    window.location.reload();
                })
            },
        });
    }

    function onItemAddSucces(productId, jsonResponse) {

        $("#add-to-cart-button-" + productId).attr("disabled", false);
        $("#add-to-cart-button-" + productId + " span.spinner").hide();
        $("#add-to-cart-button-" + productId + " i").show();

        var addToCartButton = $("#add-to-cart-button-" + productId);
        buttonSuccessValueChangeAnimate(addToCartButton);
        updateCart(jsonResponse.cart);
        updateProductRow(productId, jsonResponse.stock);
        show_popups(jsonResponse.popup);
    }

    function updateProductRow(productId, stock) {

        var productRow = $('tr[car-id=' + productId + ']');
        updateStock(productId, stock);
        productRow.attr('stock', stock.quantity);
        if (!productRow.hasClass("table-success")) {
            productRow.addClass("table-success");
        }

        updateInputCounter(productId, stock);
    }

    function updateInputCounter($productId, stock) {
        var inputCounter = $('#counter-' + $productId);
        inputCounter.val(1).change();
        inputCounter.attr('max', stock.quantity);
    }

    function buttonSuccessValueChangeAnimate(clickedButton) {

        var hasDangerClass = false;
        if (clickedButton.hasClass("btn-danger")) {
            hasDangerClass = true;
            clickedButton.removeClass("btn-danger");
        }
        clickedButton.addClass('btn-success');
        var oldValue = clickedButton.html();
        clickedButton.html('<i class="fas fa-shopping-cart"></i> Success!');
        setTimeout(function () {
            clickedButton.html(oldValue);
            clickedButton.removeClass('btn-success');
            if (hasDangerClass) {
                clickedButton.addClass('btn-danger');
            }
        }, 1250);
    }

    function updateCart(cart) {
        changeHighlightValue($('#cart_count span'), cart.totalItems, 0);
        changeHighlightValue($('#cart_price span'), cart.totalAmount, 2);
    };

    function updateStock(productId, stock) {
        var stockElement = $('td[stock-id=' + stock.id + ']');

        changeHighlightValue(stockElement, stock.quantity);

        if (stock.quantity == 0) {
            var incrementButton = $("#increment-" + productId);
            incrementButton.attr('disabled', true);
            var addToCartButton = $("#add-to-cart-button-" + productId);
            addToCartButton.attr('disabled', true);
            var counterInput = $("#counter-" + productId);
            counterInput.attr('disabled', true);

        }
    };

    function changeHighlightValue(elem, newValue, precision = 0) {
        elem.animate({fontSize: '1.5em'}, {duration: 300}).animate({fontSize: '1em'}, {duration: 300});
        elem.fadeOut(300, function () {
            $(this).text(parseFloat(newValue).toFixed(precision)).fadeIn(250);
        });
    };

    $('.btn-stock-change').click(function (e) {
        e.preventDefault();
        var inputCounterId = $(this).attr('for-input');
        var buttonFunction = $(this).attr('button-function');
        var inputCounter = $("#" + inputCounterId);
        var currentVal = parseInt(inputCounter.val());

        if (!isNaN(currentVal)) {
            if (buttonFunction == 'decrement') {

                if (currentVal > inputCounter.attr('min')) {
                    inputCounter.val(currentVal - 1).change();
                }
                if (parseInt(inputCounter.val()) == inputCounter.attr('min')) {
                    $(this).attr('disabled', true);
                }

            } else if (buttonFunction == 'increment') {

                if (currentVal < inputCounter.attr('max')) {
                    inputCounter.val(currentVal + 1).change();
                }
                if (parseInt(inputCounter.val()) == inputCounter.attr('max')) {
                    $(this).attr('disabled', true);
                }

            }
        } else {
            inputCounter.val(0);
        }
    });

    $('.counter-input-number').focusin(function () {
        $(this).data('oldValue', $(this).val());
    });

    $(".counter-input-number[max!='0']").change(function (event) {
            var minValue = parseInt($(this).attr('min'));
            var maxValue = parseInt($(this).attr('max'));
            var valueCurrent = parseInt($(this).val());
            var inputId = $(this).attr('id');

            if (!isNaN(valueCurrent)) {
                if (valueCurrent >= minValue) {
                    $('[button-function="decrement"][for-input=' + inputId + ']').removeAttr('disabled')
                } else {
                    if (valueCurrent == 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'We got it.',
                            text: 'You don\'t want that car.',
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Please don\'t be dramatic!',
                            text: 'The minimum value is obviously:' + minValue,
                        });
                    }

                    $(this).val($(this).data('oldValue'));
                    event.stopImmediatePropagation();
                }

                if (valueCurrent <= maxValue) {
                    $('[button-function="increment"][for-input=' + inputId + ']').removeAttr('disabled')
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'You are being too greedy!',
                        text: " You can have max: " + maxValue + " of that product!",
                    });
                    $(this).val($(this).data('oldValue'));
                    event.stopImmediatePropagation();
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Please insert Arabic Numerals here.',
                    text: 'hint -> [1,2,3,...]',
                    footer: '<a href="https://en.wikipedia.org/wiki/Arabic_numerals">Learn More</a>'
                });
                $(this).val($(this).data('oldValue'));
                event.stopImmediatePropagation();
            }
        }
    );

    $('.cart-item-quantity').change(function (event) {
        $(this).attr("disabled", true);
        var minQuantity = parseInt($(this).attr('min'));
        var maxQuantity = parseInt($(this).attr('max'));
        var oldQuantity = parseInt($(this).data('oldValue'));
        var newQuantity = parseInt($(this).val());
        var carId = $(this).attr('quantity-product-id')

        var r = Swal.fire({
            icon: 'info',
            text: "Are you sure you want to update quantity from: " + oldQuantity + " to " + newQuantity + " products?",
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: '<i class="fa fa-thumbs-up"></i> Great!',
            confirmButtonAriaLabel: 'Thumbs up, great!',
            cancelButtonText: '<i class="fa fa-thumbs-down"></i>',
            cancelButtonAriaLabel: 'Thumbs down'
        }).then((result) => {
            if (result.value) {
                updateCartItemQuantity(carId, newQuantity);

            } else {
                $(this).val(oldQuantity);
                event.stopImmediatePropagation();
            }
        }).finally(() => {
            $(this).attr("disabled", false);
        });
    });

    function deleteCartItem(itemId) {

        $.ajax({
            url: '/cart/product/' + itemId + '/delete/',
            type: 'DELETE',
            success: function (data) {
                onItemDeleteSuccess(itemId, data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    showCloseButton: true,
                    showCancelButton: true,
                    icon: 'error',
                    title: 'Oops...',
                    text: jqXHR.responseText,
                }).then(function () {
                    window.location.reload();
                });
            }
        });

    };

    function updateCartItemQuantity(productId, newQuantity) {

        if (newQuantity == 0) {
            return deleteCartItem(productId);
        }

        var removeButton = $("#remove-button-" + productId);
        removeButton.find("i").hide();
        removeButton.find(".spinner").show();

        $.ajax({
            url: '/cart/product/' + productId + '/update/?quantity=' + newQuantity,
            type: 'PATCH',
            success: function (data) {
                onItemUpdateSuccess(productId, data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status == 401) {
                    window.location.replace("login");
                } else {
                    Swal.fire({
                        showCloseButton: true,
                        showCancelButton: true,
                        icon: 'error',
                        title: 'Oops...',
                        text: jqXHR.responseText,
                    });
                }
            },
            complete: function () {
                removeButton.find("i").show();
                removeButton.find(".spinner").hide();
            }
        });

    }

    $('.remove-item-btn').click(function (e) {
        $(this).attr('disabled', true)
        e.preventDefault();
        var itemId = $(this).attr("remove-product-id");
        deleteCartItem(itemId)
    });

    function onItemUpdateSuccess(itemId, jsonResponse) {

        var cart = jsonResponse.cart;
        updateCheckoutProductRow(itemId, jsonResponse)
        updateOrderTotalAmount(cart.totalAmount);
        show_popups(jsonResponse.popup);
    }

    function updateCheckoutProductRow(itemId, jsonResponse) {

        var deleteButton = $("#remove-button-" + itemId);
        updateStock(itemId, jsonResponse.stock);
        var totalPriceElem = $("[total-price-product=" + itemId + "] span");
        changeHighlightValue(totalPriceElem, jsonResponse.orderItem.totalPrice, 2);
    }

    function onItemDeleteSuccess(itemId, jsonResponse) {
        var cart = jsonResponse.cart;
        var deleteButton = $("#remove-button-" + itemId);

        buttonSuccessValueChangeAnimate(deleteButton);
        removeAnimateProductRow(itemId);
        updateOrderTotalAmount(cart.totalAmount);
        if (cart.totalItems == 0) {
            $("#pay-button").hide();
        }
        show_popups(jsonResponse.popup);
    }

    function removeAnimateProductRow(itemId) {
        var productRow = $('tr[car-id=' + itemId + ']');
        productRow.slideUp("slow", function () {
            $(this).remove();
        });
    }

    function updateOrderTotalAmount($newTotalAmount) {
        var totalAmountElement = $('#cart-amount-total');
        changeHighlightValue(totalAmountElement, $newTotalAmount, 2);
    }

    initializeCountDown();

    function initializeCountDown() {
        var cartCountDown = $("#cart-countdown");
        var cartExpiresAt = cartCountDown.attr("expires-at");
        if (cartExpiresAt != undefined) {
            var timeinterval = setInterval(function () {
                var t = getTimeRemaining(cartExpiresAt);
                cartCountDown.html(t.minutes + ":" + t.seconds);
                if (t.total <= 0) {
                    clearInterval(timeinterval);
                    cartCountDown.html("Expired!").slideDown();
                    window.location.reload();
                }
            }, 1000);
        }
    }

    function getTimeRemaining(end) {
        var t = Date.parse(end) - Date.parse(new Date());
        var seconds = Math.floor((t / 1000) % 60);
        var minutes = Math.floor((t / 1000 / 60) % 60);

        return {
            'minutes': minutes,
            'seconds': seconds,
            'total': t,
        };
    }

    function show_popups(popups) {
        $.each(popups, function (type, messages) {
                if ($.inArray(type, ['warning', 'success', 'danger', 'alert', 'info', 'update'])) {
                    $.each(messages, function (id, message) {
                        Swal.fire({
                            icon: 'info',
                            text: message
                        });
                    });
                }
            }
        );
    }


});
