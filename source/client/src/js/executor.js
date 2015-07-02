/**
 * @author amberovsky
 *
 * Исполнители
 */

var AvaritiaExecutor = {
    /**
     * Инициализация страницы
     */
    init: function () {
        this
            .loadOrdersList()
            .executeOrder();
    },

    /**
     * Загрузка списка заказов
     *
     * @returns {AvaritiaExecutor}
     */
    loadOrdersList: function() {
        var $this = this;
        $this.showLoader();

        api.cmd(
            '/executor/loadOrders',
            {
                token: $('#token').val()
            },
            function (data) {
                for (var i in data) {
                    if (data.hasOwnProperty(i)) {
                        $this.createOrderRow(data[i]);
                    }
                }

                $this.hideLoader();
            },
            function (errorMsg) { $this.onRequestFail(errorMsg); }
        );

        return $this;
    },

    /**
     * Рисует строку с данными заказа
     *
     * @param {Object} orderData
     */
    createOrderRow: function(orderData) {
        var table         = $('#orders-list'),
            tr            = $('<tr></tr>').addClass('order-item'),
            tdId          = $('<td></td>').text(orderData.id),
            tdPrice       = $('<td></td>').addClass('text-center').text(orderData.price),
            tdDescription = $('<td></td>').html('<textarea class="form-control" rows="10">' + orderData.text + '</textarea>'),
            tdButton      = $('<td></td>').addClass('text-right'),
            button        = $('<button></button>')
                .addClass('btn')
                .addClass('btn-sm')
                .addClass('btn-primary')
                .addClass('execute-order')
                .data('orderId', orderData.id)
                .text('Выполнить');
        tdButton.html(button);
        tr
            .append(tdId)
            .append(tdPrice)
            .append(tdDescription)
            .append(tdButton);

        table.append(tr);
    },

    /**
     * Выпонение заказа
     */
    executeOrder: function() {
        var $this = this;

        $(document).on('click', '.execute-order', function() {
            $this.showLoader();

            var
                orderId = $(this).data('orderId'),
                orderTr = $(this).closest('tr');

            api.cmd(
                '/executor/execute',
                {
                    orderId: orderId,
                    token: $('#token').val()
                },
                function (data) {
                    orderTr.remove();
                    $this.hideLoader();
                    $this.onRequestSuccess('Успешно выполнено');
                    $('#salary').html(data.salary);
                },
                function (errorMsg, data) {
                    if (data.deleted) {
                        orderTr.remove();
                    }
                    $this.onRequestFail(errorMsg);
                }
            );
        });
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
    },

    /**
     * Обработчик ошибки выполнения запроса
     *
     * @param {String|undefined} errorMsg сообщение ошибки, если есть
     */
    onRequestFail: function(errorMsg) {
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
     * Обработчик успешного выполнения запроса
     */
    onRequestSuccess: function($message) {
        this.hideLoader();

        $('#successMessage')
            .show()
            .text($message)
            .fadeOut(2000, function () {
                $('#successMessage').hide();
            });
    }
};

$( document ).ready(function() {
    AvaritiaExecutor.init();
});
