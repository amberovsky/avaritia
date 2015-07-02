/**
 * @author amberovsky
 *
 * Общение с сервером ajax-запросами
 */

var api = {
    /**
     * Тип success callback
     * @name successCallback
     * @function
     * @param {String|undefined} сообщение об успехе, если есть
     */

    /**
     * Тип fail callback
     * @name failCallback
     * @function
     * @param {String|undefined} сообщение об ошибке, если есть
     * @param {Object|undefined} данные ответа от сервера, если есть
     */

    /**
     * Отправка cmd запроса на сервер. В ответе приходит json
     *
     * @param {String} url url запроса
     * @param {Object} data данные запроса
     * @param {successCallback} success обработчик успешного запроса
     * @param {failCallback} fail  обработчик неуспешного запроса (ошибка сервера или пришло errorMsg)
     */
    cmd: function(url, data, success, fail) {
        $.ajax({
            url: url,
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (data) {
                if (!data) {
                    fail();
                } else if (data.response.errorMsg) {
                    fail(data.response.errorMsg, data.response);
                } else {
                    success(data.response);
                }
            },
            error: fail
        });
    }
};
