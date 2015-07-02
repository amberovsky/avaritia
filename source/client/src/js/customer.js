/**
 * @author amberovsky
 *
 * Заказчики
 */

var AvaritiaCustomer = {
    /**
     * Инициализация страницы
     */
    init: function() {
        this.initSaveOrder();
    },

    /**
     * Обработчик успешного добавления заказа
     */
    onAddSuccess: function() {
        this.hideLoader();
        $('#text').val('');
        $('#price').val('');

        $('#successMessage')
            .show()
            .text('Заказ добавлен')
            .fadeOut(2000, function () {
                $('#successMessage').hide();
            });
    },

    /**
     * Обработчик ошибки добавления заказа
     *
     * @param {String|undefined} errorMsg сообщение ошибки, если есть
     */
    onAddFail: function(errorMsg) {
        this.hideLoader();
        if (errorMsg) {
            $('#failMessage')
                .show()
                .text(errorMsg)
                .fadeOut(2000, function () {
                    $('#failMessage').hide();
                });
        }
    },

    /**
     * Иницилизация
     *
     * @returns {AvaritiaCustomer}
     */
    initSaveOrder: function () {
        var $this = this;

        $(document).on('click', '#saveOrder', function() {
            var
                text = $('#text').val(),
                price = $('#price').val().trim();

            if (text.length == 0) {
                alert('Описание пусто');
                return;
            }

            if (text.length > 1024) {
                alert('Описание не должно быть больше 1024 символов');
                return;
            }

            if (!/^\d+$/.test(price)) {
                alert('Неверное значение цены');
                return;
            }

            $this.showLoader();
            api.cmd(
                '/customer/add',
                {
                    text: text,
                    price: price,
                    token: $('#token').val()
                },
                function () { $this.onAddSuccess(); },
                function (errorMsg) { $this.onAddFail(errorMsg); }
            );
        });

        return this;
    },

    /**
     * Показываем индикатор отправки запроса
     */
    showLoader: function() {
        $('.loader').addClass('visible');
    },

    /**
     * Не показываем индикатор отправки запроса
     */
    hideLoader: function() {
        $('.loader').removeClass('visible');
    }
};

$( document ).ready(function() {
    AvaritiaCustomer.init();
});
