/**
 * очищаем инпуты при тыке в них
 */
function clear_input(){
    $('input[data-format]').click(function(){
        var element = $(this);
        var init_val = element.val();
        if(init_val == 'От' || init_val == 'До'){
            element.val('');
            element.focusout(function(){
                if(!element.val()){
                    element.val(init_val)
                }
            })
        }
    })
}

/**
 * Инициализируем календарь
 */
function date_pickers(){
    $('#date_from, #date_to').datetimepicker({
        'language': 'ru'
    });
}

/**
 * делаем календарь недоступным при выборе интервала "час/день/неделя/месяц"
 */
function date_time_picker_disable(){
    $('select[name=ago_interval]').change(function(){
        var interval = $(this).val();
        if(interval != "0"){
            $('#date_from input, #date_to input').attr('disabled', 'disabled');
            //$('#date_from input, #date_to input').val('');
            $('.add-on').css({'visibility': 'hidden'});
        }
        else{
            $('#date_from input, #date_to input').removeAttr('disabled');
            $('.add-on').css({'visibility': 'visible'});
        }
    })
}

/**
 * просто обрабочик тыка в кнопку построения графиков
 * @param min_timestamp
 * @param max_timestamp
 */
function plots_click_handler(){
    $('#draw_plots').click(function(){
        plots_handler();
    })
}

/**
 * Получение данных для графиков, и тут же вызов ф-и их отрисовки draw_all_plots()
 * @param min_timestamp
 * @param max_timestamp
 */
function plots_handler(min_timestamp, max_timestamp){

    var device_id = $('select[name=device]').val();
    //УБРАТЬ!!! Это дебаг
    device_id = "1";
    if(device_id == "0"){
        jAlert('Укажите КНС');
        return;
    }

    //эту же ф-ю вызываем для перерисовки при зумировании
    //эти переменные определены при вызове ф-и с аргументами, т.е. только при зумировании
    //проверка того, что это - даты или таймстампы с графика с миллисекундах,
    //производится PHP на сервере
    //эти переменные = 0 в режиме реалтайм, временной интервал определяется на сервере
    var date_from = 0;
    var date_to = 0;
    var ago_interval = 0;
    if(realtime_plots == 'no'){
        if(typeof min_timestamp == 'undefined'){
            date_from= ($('#date_from input').attr('disabled'))?0:$('#date_from input').val();
            date_to = ($('#date_to input').attr('disabled'))?0:$('#date_to input').val();
            ago_interval = $('select[name=ago_interval]').val();
            if(!((ago_interval != "0") || (((date_from && date_from != 'От') && (date_to && date_to != 'До'))))){
            //if(((ago_interval != "0") && (!date_from || (date_from == 'От')) && (!date_to || (date_to == 'До')))){
                //jAlert('Укажите интервал:<br>- либо час/сутки/неделя/месяц<br>- либо установите дату/время От и До');
            }
        }
        else{
            date_from = min_timestamp;
            date_to = max_timestamp;
        }
    }

    $.ajax({
        data: {
            'action': 'data_for_plots',
            'device_id': device_id,
            'date_from': date_from,
            'date_to': date_to,
            'ago_interval': ago_interval,
            'realtime': realtime_plots
        },
        success: function(resp){

            var plot = draw_all_plots($.parseJSON(resp));
            zoom_plots();
            tooltips_for_plot();

            if(realtime_plots == 'yes'){
                setInterval(function(){
                    draw_realtime_plot(plot, device_id);
                }, 3000);
            }

        }
    })

}

/**
 * Отрисовка графиков - главная ф-я
 * @param trends_data
 * @param signal_names
 */
function draw_all_plots(raw_data){

    //данные ответа сервера
    var
        //это данные из объединенной выборки айди сигнала + название оборудования
        signal_names = raw_data.signal_names,
        //это данные для построения графика
        trends_data = raw_data.trends_data,
        //это выборка из таблицы signal - все данные сигналов, отображаемых на графике
        signals = raw_data.signals;

    var options = options_for_plot(trends_data, signals);

    var data_all = [];

    var yaxis_counter = 1;

    $.each(trends_data, function(signal_id, signal_data_array){

        data_all.push({
            data: signal_data_array,
            label: (signal_names[signal_id].equipment_name + '>'
                + signal_names[signal_id].signal_name + ' (id: ' +
                + signal_id + ')'),
            //обратите внимание на написание слова *axis - через i, означает одну ось
            //и посмотрите ниже для множественных осей
            yaxis: yaxis_counter,
            xaxis: 1

        });
        yaxis_counter++;

    })
    var plot = $.plot("#plots", data_all, options);

    axes_paint(plot, legend_handler(signals));
    y_min_set();
    y_max_set();

    return plot;
}

function draw_realtime_plot(plot, device_id){

    if(realtime_plots == 'no') { return }

    $.ajax({
        data: {
            'action': 'data_for_plots',
            'device_id': device_id,
            'date_from': 0,
            'date_to': 0,
            'ago_interval': 0,
            'realtime': 'yes'
        },
        //уберем крутящееся колесико при загрузке аякса
        beforeSend: function(){
            document.body.style.cursor = 'default'
        },
        success: function(resp){

            var raw_data = $.parseJSON(resp);

                //данные ответа сервера
            var
                //это данные из объединенной выборки айди сигнала + название оборудования
                signal_names = raw_data.signal_names,
                //это данные для построения графика
                trends_data = raw_data.trends_data,
                //это выборка из таблицы signal - все данные сигналов, отображаемых на графике
                signals = raw_data.signals;

            var data_all = [];

            var yaxis_counter = 1;

            $.each(trends_data, function(signal_id, signal_data_array){

                data_all.push({
                    data: signal_data_array,
                    label: (signal_names[signal_id].equipment_name + '>'
                        + signal_names[signal_id].signal_name + ' (id: ' +
                        + signal_id + ')'),
                    //обратите внимание на написание слова *axis - через i, означает одну ось
                    //и посмотрите ниже для множественных осей
                    yaxis: yaxis_counter,
                    xaxis: 1

                });
                yaxis_counter++;

            })
            plot.setData(data_all);
            plot.setupGrid();
            plot.draw();


        }
    })

}

function options_for_plot(trends_data, signals){
    var opt = {
            //здесь если написать *axis, через i - это параметры одной оси, {}
            //а если написать *axes, через e - это массив с параметрами осей: [{}, {}, ...]
            //внимание - оси нумеруются с 1, не с 0
            xaxes: [{ show: true,
                mode: "time",
                minTickSize: [1, "minute"],
                timeformat: '<span class="md">%m/%d</span> <br> %H:%M'
                }],
            yaxes:  y_axes_set(trends_data, signals),
            grid: {
                show: true,
                aboveData: true,
                hoverable: true,
                clickable: true

            },
            series: {
                //points: {show: true},
                lines: {show: true},
                shadowSize: 0
            },
            selection: {
                mode: 'xy'
            },
/*
            legend: {
                container: '#legend'
            },
*/
            legend:{
                container: '#legend',
                backgroundOpacity: 0.5,
                noColumns: 0,
                backgroundColor: "green",
                position: 'ne'
            }
    }

    return opt;
}

/**
 * Проходимся по легенде. Контейнер легенды некритичен, лишь бы была
 * Вытаскиваем цвета, тексты и из текстов - реальные айди сигналов
 * Заодно в этом же цикле добавляем к легенде инпуты для ввода крайних значений шкал
 * @param signals
 */
function legend_handler(signals){

    //вытаскиваем из легенды цвета
    var legend_colors = [];
    $.each($('.legendColorBox > div > div'), function(index, element){
        var legend_row_style = $(element).attr('style');
        var rgb_pattern = /rgb\([\d,]+\)/;
        var style_array = rgb_pattern.exec(legend_row_style);
        if(style_array){
            legend_colors.push(style_array[0]);
        };
    })

    //вытаскиваем из легенды тексты и реальные ID сигналов,
    //иакже добавляем инпуты
    var legend_texts = [];
    var legend_id = [];
    $.each($('.legendLabel'), function(index, element){
        //тексты
        var l_text = $(element).text();
        l_text = l_text.replace('>', ' ');
        legend_texts.push(l_text);

        //реальные ID
        var id_pattern = /id:\s\d+\)/g;
        var id_array = id_pattern.exec(l_text);
        //без реальных ID делать дальше нечего
        if(!id_array){ return }
        var real_id;
        real_id = id_array[0].replace('id: ', '').replace(')', '');
        legend_id.push(real_id);

        //добавляем инпуты для ввода макс/мин значений каждой шкалы
        var inputs_html = '<td>' +
            '<input class="legend_input_min" data-index="' + real_id + '" value="' + signals[real_id].plots_y_min + '" title="Минимальная отметка шкалы">' +
            '<input class="legend_input_max" data-index="' + real_id + '" value="' + ((signals[real_id].plots_y_max)?signals[real_id].plots_y_max:'') +
            '" title="Максимальная отметка шкалы"></td>';

        //добавляем инпуты
        $(element).after(inputs_html)

    })

    var legend = [];
    legend['id'] = legend_id;
    legend['texts'] = legend_texts;
    legend['colors'] = legend_colors;

    return legend;
}

/**
 * раскрашиваем вертикальные оси по цветам, как у легенды/графика
 * @param plot
 * @param legend_id
 * @param legend_texts
 * @param legend_colors
 */
function axes_paint(plot, legend){

    var legend_id = legend.id;
    var legend_texts = legend.texts;
    var legend_colors = legend.colors;


    var legend_iterator = 0;

    $.each(plot.getAxes(), function (i, axis) {

        if (!axis.show || axis.direction == 'x'){
            return;
        }

        var box = axis.box;

        $("<div class='axisTarget' " +
              "style='position:absolute; left:" + box.left + "px; top:" + box.top +
              "px; width:" + box.width +  "px; height:" + box.height + "px;'" +
              "title='" + legend_texts[legend_iterator] + "' data-index='" + legend_id[legend_iterator] + "'></div>")
            .css({ backgroundColor: legend_colors[legend_iterator], opacity: 0.3, cursor: "pointer" })
            .appendTo(plot.getPlaceholder())
            .hover(
                function () { $(this).css({ opacity: 0.8 }) },
                function () { $(this).css({ opacity: 0.3 }) }
            )
        legend_iterator++;
        });
}

function y_axes_set(trends_data, signals){
    var y_a = [];
    $.each(trends_data, function(signal_id, signal_data_array){
        y_a.push({
            position: 'left',
            ticks: null,
            min: signals[signal_id].plots_y_min,
            max: (signals[signal_id].plots_y_max)?signals[signal_id].plots_y_max:null
        });

    });
    return y_a;
}

/**
 * зум графиков
 * пока зум только по оси Х
 */
function zoom_plots(){
    $("#plots").unbind();
    $("#plots").bind("plotselected", function (event, ranges){
        plots_handler(ranges.xaxis.from, ranges.xaxis.to);
    })
}

/**
 * Преобразуем объект в массив. SO рулит :)
 * Требуется в ф-и plots_handler(), там есть пояснение зачем
 * @param obj
 * @return {*}
 */
function obj_to_array(obj){
    var array = $.map(obj, function(value, index) {
        return [value];
    });

    return array;
}

/**
 * Меняем иконку при сворачивании/разворачивании панели
 */
function change_collapse_icon(){
    $('[data-toggle=collapse]').click(function(){
        setTimeout(function(){
            if(!$('#collapsed_div').hasClass('in')){
                $('img[data-toggle=collapse]').attr('src', 'views/img/expand.png')
            }
            else{
                $('img[data-toggle=collapse]').attr('src', 'views/img/collapse.png')
            }
        } ,200);
    });
}


/**
 * всплывающие подсказки при наведении на узлы графика
 * обрабатывается событие plothover - см. доки/прмиеры jquery.flot
 * с датой как-то плохо совсем. Объект правильный, таймстамп правильный,
 * но данные из него не получаются
 */
function tooltips_for_plot(){
    $("<div id='tooltip_plot'></div>").css({
  			position: "absolute",
  			display: "none",
  			border: "1px solid #fdd",
  			padding: "2px",
  			"background-color": "#fee",
  			opacity: 0.80
  		}).appendTo("body");

    $("#plots").bind("plothover", function (event, pos, item) { //jAlert(item);
        if (item) {

            var x = parseInt(item.datapoint[0]);
            var y = item.datapoint[1].toFixed(0);

            var dt = new Date(x);
            //var text_date = dt.getUTCFullYear() + '/' + dt.getUTCMonth() + '/' + dt.getUTCDay() //+ ' ' + dt.getHours() + ':' + dt.getMinutes();

            $("#tooltip_plot").html(item.series.label + "<br>Значение: " + y + "<br>Время: " + dt)
                .css({top: item.pageY+5, left: item.pageX+5})
                .fadeIn(200);
        } else {
            $("#tooltip_plot").hide();
        }
    })
}

/**
 * Обработка ввода в поле минимального (нижнего) значения шкалы соотв. сигнала
 */
function y_min_set(){
    $('.legend_input_min').change(function(){
        var min_value = validate_float_string($(this).val());
        if(!min_value){
            jAlert('Проверьте, что Вы ввели');
            return
        }
        //это костылик для дебага. когда не задается ID КНС
        var device_id = ($('[name=device]').val() && $('[name=device]').val() != 0)?$('[name=device]').val():1;
        var signal_id = $(this).attr('data-index');
        $.ajax({
            data: {
                'action': 'axis_y_min_set',
                'device_id': device_id,
                'signal_id': signal_id,
                'plots_y_min': min_value
            },
            success: function(resp){
                if(resp == 'ok'){
                    plots_handler();
                }
            }
        })
    })
}

/**
 * Обработка ввода в поле максимального (верхнего) значения шкалы соотв. сигнала
 */
function y_max_set(){
    $('.legend_input_max').change(function(){
        var max_value = validate_float_string($(this).val());
        if(!max_value){
            jAlert('Проверьте, что Вы ввели');
            return
        }
        //это костылик для дебага. когда не задается ID КНС
        var device_id = ($('[name=device]').val() && $('[name=device]').val() != 0)?$('[name=device]').val():1;
        var signal_id = $(this).attr('data-index');
        $.ajax({
            data: {
                'action': 'axis_y_max_set',
                'device_id': device_id,
                'signal_id': signal_id,
                'plots_y_min': max_value
            },
            success: function(resp){
                if(resp == 'ok'){
                    plots_handler();
                }
            }
        })
    })
}

/**
 * Проверяем/изменяем числа в инпутах
 * @param input_value
 * @returns {*}
 */
function validate_float_string(input_value){

    //заменяем запятые на точки, убираем пробелы
    input_value = input_value.replace(',', '.');
    input_value = input_value.replace(' ', '');

    //верим, верим в одаренных. Проверяем, не воткнул ли кто более одного разделителя
    var point_raw_array = input_value.split('.');
    if(point_raw_array.length > 2){
        return false;
    }
    return input_value;

}

/**
 * Включение/выключение режима реального времени
 * с перерисовкой иконок
 * используем глобальную переменную, дабы не обращаться к DOM, кукам и пр. -
 * это быстрее, на браузер и так нагрузка большая при перерисовке графика в реалтайме
 * @type {String}
 */
var realtime_plots = 'no';
function realtime_mode(){
    $('#realtime').click(function(){
        //если включен реалтайм
        if(realtime_plots == 'yes'){
            //отключаем его и меняем кнопку
            realtime_plots = 'no';
            $('#realtime').removeClass('realtime_border');
            $('#realtime').attr('src', 'views/img/clock.png');
            $('#draw_plots').css('visibility', 'visible');

            //имитируем нажатие для построения тех графиков, которые были
            //до включения реалтайма
            $('#draw_plots').click();
        }
        else{
            //соотв. включаем реалтайм и меняем кнопку
            realtime_plots = 'yes';
            $('#realtime').addClass('realtime_border');
            $('#realtime').attr('src', 'views/img/clock_red.png');
            $('#draw_plots').css('visibility', 'hidden');
            plots_handler();
        }
    })
}


$(document).ready(function(){
    clear_input();
    date_pickers();
    date_time_picker_disable();
    plots_click_handler();
    change_collapse_icon();
    realtime_mode();

    $('#draw_plots').click();

})