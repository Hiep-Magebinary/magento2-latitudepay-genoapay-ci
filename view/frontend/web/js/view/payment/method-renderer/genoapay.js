/*browser:true*/
/*global define*/
define(
    [   'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Latitude_Payment/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Catalog/js/price-utils',
        'Magento_Customer/js/customer-data',
        'mage/translate'
    ],
    function ($,Component, setPaymentMethodAction, additionalValidators, quote, totals,messageList,fullScreenLoader,priceUtils,customerData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Latitude_Payment/payment/genoapay'
            },
            initialize: function () {
                this._super();
                var _self = this;
                _self.PaymentFaileMsg();
                setTimeout(this.initPopup,2000);
                return this;
            },
            initPopup: function() {
                var e = document.querySelectorAll("img[src*='https://images.latitudepayapps.com/v2/snippet.svg'], img[src*='https://images.latitudepayapps.com/v2/api/banner'], img[src*='https://images.latitudepayapps.com/v2/LatitudePayPlusSnippet.svg']");
                [].forEach.call(
                    e, function (e) {
                        e.style.cursor = "pointer",
                            e.addEventListener("click", handleClick)
                    })
                function handleClick(e) {
                    if (0 == document.getElementsByClassName("lpay-modal-wrapper").length) {
                        var t = new XMLHttpRequest;
                        t.onreadystatechange = function () {
                            4 == t.readyState && 200 == t.status && null != t.responseText && (document.body.insertAdjacentHTML("beforeend", t.responseText))
                        },
                            t.open("GET", e.srcElement.currentSrc.replace('snippet.svg','modal.html'), !0),
                            t.send(null)
                    } else document.querySelector(".lpay-modal-wrapper").style.display = "block"
                }
            },
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getLogoUrl: function() {
                return window.checkoutConfig.latitudepayments.genoapay;
            },
            getInstallmentText: function() {
                var grandTotal  = 0,
                installmentText = '',
                curInstallment  = window.checkoutConfig.latitudepayments.installmentno,
                currency        = window.checkoutConfig.latitudepayments.currency_symbol,
                grandTotal      = totals.getSegment('grand_total').value,
                html            = window.checkoutConfig.latitudepayments.gpay_installment_block;
                if(grandTotal){
                    installmentText = html.replace('__AMOUNT__',grandTotal);
                }
                return installmentText;
            },
            /** Redirect to Genoapay */
            continueToGenoapay: function () {
                fullScreenLoader.startLoader();
                if (additionalValidators.validate()) {
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.get(window.checkoutConfig.payment.latitude.redirectUrl[quote.paymentMethod().method]+'?isAjax=true')
                                .done(function (response) {
                                    if (response['success']) {
                                        if (response['redirect_url']) {
                                            $.mage.redirect(response['redirect_url']);
                                        }
                                    } else {
                                        var msg = $.mage.__('There was an error with your payment, please try again or select other payment method');
                                        if(response['error']){
                                            msg = response['message'];
                                        }
                                        fullScreenLoader.stopLoader();
                                        messageList.addErrorMessage({ message: msg});
                                    }
                                }).fail(function (response) {
                                $.mage.redirect(
                                    window.checkoutConfig.payment.latitude.redirectUrl[quote.paymentMethod().method]+'?method=genoapay'
                                );
                                fullScreenLoader.stopLoader();
                            });
                        }
                    );
                    return false;
                }
            },
            PaymentFaileMsg: function () {
                var cancelUrl = document.URL.split('?')[1];
                if(cancelUrl){
                    var CancelRedirect = cancelUrl.split("/")[0];
                }
                if(CancelRedirect){
                    var msg = $.mage.__('There was an error with your payment, please try again or select other payment method');
                    messageList.addErrorMessage({ message: msg });
                }
            }

        });
    }
);
