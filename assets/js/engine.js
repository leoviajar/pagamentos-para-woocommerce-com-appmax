(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('axios')) :
    typeof define === 'function' && define.amd ? define(['exports', 'axios'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.appMaxGatewayPublic = {}, global.axios));
})(this, (function (exports, axios) { 'use strict';

    function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

    var axios__default = /*#__PURE__*/_interopDefaultLegacy(axios);

/**
 * InputField: Classe para gerenciar um campo de formulário, sua validação e exibição de erros.
 *
 * CORREÇÃO PRINCIPAL: A validação de erro (isValid) agora é chamada apenas no evento 'blur' (quando o usuário
 * sai do campo). O evento 'keyup' é usado apenas para atualizar o preview visual do cartão,
 * proporcionando um feedback instantâneo sem ser intrusivo com mensagens de erro.
 */
var InputField = function() {
    function t(t, e) {
        this._validate = e; // A função de validação (ex: validateNumber)
        this._input = t.input; // O elemento <input>
        this._render = t.render; // O elemento onde o valor é espelhado (preview)
        this._parent = t.input.parentElement; // O elemento pai do input
        this._message = this._parent.querySelector(".input-error"); // Onde a mensagem de erro aparece
        this.listen(); // Inicia os "ouvintes" de eventos
    }

    // Retorna o valor atual do campo.
    t.prototype.value = function() {
        var t;
        return null !== (t = this._input.value) && void 0 !== t ? t : "";
    };

    // Atualiza o preview visual (o "desenho" do cartão) com o valor digitado.
    t.prototype.updatePreview = function() {
        if (this._render) {
            this._render.textContent = this._input.value;
        }
    };

    // Valida o campo, mostra/esconde a mensagem de erro e retorna true (válido) ou false (inválido).
    t.prototype.isValid = function() {
        var t = this._validate(this._input.value); // Roda a função de validação
        if (t !== true) {
            // Se a validação retornar uma string de erro...
            this._parent.classList.add("has-error"); // Adiciona a classe de erro
            if (this._message) {
                this._message.textContent = t; // Mostra a mensagem de erro
            }
            return false; // Retorna que é inválido
        } else {
            // Se a validação retornar 'true'...
            this._parent.classList.remove("has-error"); // Remove a classe de erro
            return true; // Retorna que é válido
        }
    };

    // Configura os eventos do campo.
    t.prototype.listen = function() {
        var t = this;

        // --- LÓGICA DE EVENTOS CORRIGIDA ---

        // 1. No 'blur' (ao sair do campo): Roda a validação completa.
        this._input.addEventListener("blur", function() {
            t.isValid();
        });

        // 2. No 'keyup' (a cada tecla): Apenas atualiza o preview visual.
        this._input.addEventListener("keyup", function() {
            t.updatePreview();
        });

        // Os listeners de 'focus' e 'change' foram removidos por serem redundantes e inconvenientes.
    };

    return t;
}();


/**
 * CheckoutListener: Classe principal que orquestra os campos do formulário de cartão de crédito.
 *
 * OTIMIZAÇÃO: A lógica de 'mountFields' foi simplificada. Agora ela apenas instancia
 * a classe 'InputField' para cada campo, que já contém a lógica de validação correta.
 * A função 'displayError' e 'hideError' foi removida daqui, pois a 'InputField' já gerencia
 * os erros diretamente no campo correspondente.
 */
var CheckoutListener = function() {
    function e() {
        this.fields = {
            credit_card: {}
        };
        this.brand_wrapper = null;
        this.fields.credit_card = this.mountFields("credit_card");
        this.brand_wrapper = document.getElementById("pagamentos-para-woocommerce-com-appmax-credit-card--flag");
    }

    e.prototype.isPaymentMethodSelected = function(e) {
        var t = document.getElementById("payment_method_pagamentos_para_woocommerce_com_appmax_".concat(e));
        return !!t && t.checked;
    };

    // --- Funções de Validação (sem alterações) ---
    e.prototype.validateNumber = function(e) {
        if (0 === e.length) return "Preenchimento obrigatório";
        var t = e.replace(/[^0-9.]/g, "");
        return /^[0-9]{13,19}$/.test(t) ? !!this.luhnCheck(t) || "O número do cartão está inválido" : "O número do cartão está incompleto";
    };

    e.prototype.luhnCheck = function(e) {
        for (var t = e.replace(/\D/g, ""), n = t.length, r = n % 2, a = 0, o = 0; o < n; o++) {
            var i = Number(t.charAt(o));
            o % 2 === r && (i *= 2) > 9 && (i -= 9), a += i;
        }
        return a % 10 == 0;
    };

    e.prototype.validateName = function(e) {
        return 0 !== e.length || "Preenchimento obrigatório";
    };

    e.prototype.validateCvv = function(e) {
        if (0 === e.length) return "Preenchimento obrigatório";
        var t = e.replace(/[^0-9.]/g, "");
        return !!/^[0-9]{3,4}$/.test(t) || "O CVV está inválido";
    };

    // Encontra os elementos HTML do formulário.
    e.prototype.catchFields = function(e, t, n) {
        var r = "#pagamentos-para-woocommerce-com-appmax-".concat(e);
        return {
            number: {
                render: n.querySelector("".concat(r, "-previewer-number")),
                input: t.querySelector("".concat(r, "-card_number"))
            },
            name: {
                render: n.querySelector("".concat(r, "-previewer-name")),
                input: t.querySelector("".concat(r, "-card_name"))
            },
            cvv: {
                render: n.querySelector("".concat(r, "-previewer-cvv")),
                input: t.querySelector("".concat(r, "-card_cvv"))
            },
            month: {
                render: n.querySelector("".concat(r, "-previewer-month")),
                input: t.querySelector("".concat(r, "-card_month"))
            },
            year: {
                render: n.querySelector("".concat(r, "-previewer-year")),
                input: t.querySelector("".concat(r, "-card_year"))
            }
        };
    };

    // Monta e inicializa os campos do formulário.
    e.prototype.mountFields = function(e) {
        var t = this,
            n = document.getElementById("pagamentos-para-woocommerce-com-appmax-".concat(e)),
            r = document.getElementById("pagamentos-para-woocommerce-com-appmax-".concat(e, "-previewer"));
        if (!n || !r) return {};

        var a = this.catchFields(e, n, r),
            o = {}; // Objeto que guardará as instâncias de InputField

        // Cria as instâncias de InputField para cada campo.
        if (a.number) {
            o.number = new InputField(a.number, this.validateNumber.bind(this));
            // Adiciona um listener extra APENAS no campo de número para calcular a bandeira.
            a.number.input.addEventListener("keyup", function() {
                t.calculateBrand(a.number.input.value);
            });
        }
        if (a.name) {
            o.name = new InputField(a.name, this.validateName.bind(this));
        }
        if (a.cvv) {
            o.cvv = new InputField(a.cvv, this.validateCvv.bind(this));
        }
        // Para mês e ano, a validação é simples (sempre retorna true), mas a estrutura permite expandir.
        if (a.month) {
            o.month = new InputField(a.month, (function() {
                return true;
            }));
        }
        if (a.year) {
            o.year = new InputField(a.year, (function() {
                return true;
            }));
        }
        return o;
    };

    // As funções displayError e hideError foram removidas daqui, pois a InputField agora cuida disso.

    // --- Funções de Bandeira (sem alterações) ---
    e.prototype.calculateBrand = function(e) {
        var t, n;
        if (this.brand_wrapper) {
            var r = this.solveBrand(e);
            if (!r) return this.brand_wrapper.dataset.brand = "none", void(this.brand_wrapper.style.display = "none");
            this.brand_wrapper.dataset.brand !== r && (this.brand_wrapper.dataset.brand = r, this.brand_wrapper.style.display = "block", this.brand_wrapper.innerHTML = null !== (n = null === (t = document.getElementById("pagamentos-para-woocommerce-com-appmax-credit-card--flag-".concat(r))) || void 0 === t ? void 0 : t.outerHTML) && void 0 !== n ? n : "");
        }
    };

    e.prototype.solveBrand = function(e) {
        var t = e.replace(/[^\d]*/g, ""),
            n = new RegExp("^3[47][0-9]*"),
            r = new RegExp("^4[0-9]*"),
            a = new RegExp("^5[1-5][0-9]*"),
            o = new RegExp("^2[2-7][0-9]*"),
            i = new RegExp("^6011[0-9]*"),
            c = new RegExp("^62[24568][0-9]*"),
            p = new RegExp("^6[45][0-9]*"),
            d = new RegExp("^606282[0-9]*"),
            u = new RegExp("^3841(?:[0|4|6]{1})0[0-9]*"),
            m = new RegExp("^3[0689][0-9]*");
        return r.test(t) ? "visa" : n.test(t) ? "amex" : a.test(t) || o.test(t) ? "mastercard" : d.test(t) || u.test(t) ? "hipercard" : i.test(t) || c.test(t) || p.test(t) ? "discover" : m.test(t) ? "dinners" : void 0;
    };

    return e;
}();


    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */
    function __awaiter(t,e,r,n){return new(r||(r=Promise))((function(o,a){function i(t){try{u(n.next(t));}catch(t){a(t);}}function c(t){try{u(n.throw(t));}catch(t){a(t);}}function u(t){var e;t.done?o(t.value):(e=t.value,e instanceof r?e:new r((function(t){t(e);}))).then(i,c);}u((n=n.apply(t,e||[])).next());}))}function __generator(t,e){var r,n,o,a,i={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return a={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(a[Symbol.iterator]=function(){return this}),a;function c(a){return function(c){return function(a){if(r)throw new TypeError("Generator is already executing.");for(;i;)try{if(r=1,n&&(o=2&a[0]?n.return:a[0]?n.throw||((o=n.return)&&o.call(n),0):n.next)&&!(o=o.call(n,a[1])).done)return o;switch(n=0,o&&(a=[2&a[0],o.value]),a[0]){case 0:case 1:o=a;break;case 4:return i.label++,{value:a[1],done:!1};case 5:i.label++,n=a[1],a=[0];continue;case 7:a=i.ops.pop(),i.trys.pop();continue;default:if(!(o=i.trys,(o=o.length>0&&o[o.length-1])||6!==a[0]&&2!==a[0])){i=0;continue}if(3===a[0]&&(!o||a[1]>o[0]&&a[1]<o[3])){i.label=a[1];break}if(6===a[0]&&i.label<o[1]){i.label=o[1],o=a;break}if(o&&i.label<o[2]){i.label=o[2],i.ops.push(a);break}o[2]&&i.ops.pop(),i.trys.pop();continue}a=e.call(t,i);}catch(t){a=[6,t],n=0;}finally{r=o=0;}if(5&a[0])throw a[1];return {value:a[0]?a[1]:void 0,done:!0}}([a,c])}}}

    var PaymentChecker=function(){function t(){var t=this;this.countdown={minute:4,second:59},this.countdownInterval=void 0,this.checkingTimeout=void 0;var o=document.getElementById("pagamentos-para-woocommerce-com-appmax-countdown");if(o){var n=parseInt(o.dataset.seconds||"3600",10)-1,e=Math.floor(n/60);this.countdown={minute:e,second:n-60*e},this.countdownInterval=setInterval((function(){t.updateCountdown(o);}),1e3),this.checkingTimeout=setTimeout((function(){t.checkPayment(o.dataset.orderId||"0",o.dataset.redirectTo||window.location.href);}),15e3);}}return t.prototype.checkPayment=function(t,o){return void 0===o&&(o=window.location.href),__awaiter(this,void 0,void 0,(function(){var n,e,a,i=this;return __generator(this,(function(c){switch(c.label){case 0:return c.trys.push([0,2,,3]),n=window.pagamentos_para_woocommerce_com_appmax_front,e=n.ajax_url,a=n.x_security,[4,axios__default["default"].post(e,{payment_id:parseInt(t,10),x_security:a},{params:{action:"pagamentos_para_woocommerce_com_appmax_check_payment"}})];case 1:return !0===c.sent().data.data.status?(window.location.replace(o),[2]):[3,3];case 2:return c.sent(),[3,3];case 3:return this.checkingTimeout=setTimeout((function(){i.checkPayment(t);}),15e3),[2]}}))}))},t.prototype.stopChecking=function(){clearTimeout(this.checkingTimeout),this.countdownInterval?this.stopCountdown():location.reload();},t.prototype.stopCountdown=function(){clearInterval(this.countdownInterval),location.reload();},t.prototype.updateCountdown=function(t){var o=this.pad(this.countdown.minute,2),n=this.pad(this.countdown.second,2);t.innerHTML=o+":"+n,this.countdown.second--,this.countdown.second<=0&&(this.countdown.minute--,this.countdown.second=59),this.countdown.minute<=0&&this.stopCountdown();},t.prototype.pad=function(t,o){var n="00"+t;return n.substring(n.length-o)},t}();

    var App=function(){function e(){}return e.prototype.init=function(){new CheckoutListener,null!==document.getElementById("pagamentos-para-woocommerce-com-appmax-countdown")&&new PaymentChecker;},e}();document.addEventListener("DOMContentLoaded",(function(){Maska.create(".masked"),(new App).init(),jQuery(document.body).on("updated_checkout",(function(){(new App).init();}));}));

    exports.App = App;

    Object.defineProperty(exports, '__esModule', { value: true });

}));
