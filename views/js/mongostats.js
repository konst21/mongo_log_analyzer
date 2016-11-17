/**
 * Получаем данные из URL
 */
function request_data(){
    var out = [];
    var req = (window.location.href).split('/');
    out['collection'] = req[4];
    out['days'] = req[6];
    out['interval'] = req[8];
    return out;

}

var plot_start_timestamp = 0;
var plot_finish_timestamp = 0;
var data_label = [];
var data_scale = [];
var data_type = [];
/**
 * основная ф-я отрисовки графиков
 */
function stats_plot() {

    var url = '/mongostats_data/' + request_data()['collection'] + '/days/' + request_data()['days'] + '/interval/' + request_data()['interval'] + '/';
    $.ajax({
        'url': url,
        'method': 'post',
        data: {
            plot_start_timestamp: parseInt(plot_start_timestamp/1000),
            plot_finish_timestamp: parseInt(plot_finish_timestamp/1000)
        },
        beforeSend: function(){
            show_preloader(1);
        },
        success: function(resp){
            show_preloader(0)
            var trotter = JSON.parse(resp);
            var pl = [];
            $.each(trotter, function(index, plotter){
                var out = [];
                $.each(plotter.data, function(timestamp, value){
                    out.push([parseInt(timestamp)*1000, parseInt(value)]); //*1000 - переводим сек в мс для яваскрипта
                });
                pl.push({label: plotter.label, data: out});
                data_label.push(plotter.label);
                data_scale.push(plotter.scale);
                data_type.push(plotter.type);

            });
            var plot = $.plot("#plots", pl, options_for_plot());
            //tooltip(plot);
            zoom_plots(plot);
            hide_legend();
            t2(plot);
        }
    });

}

/**
 * Основная ф-я для отображения данных. Глобальная переменная к ней же
 */
var latestPosition;
function t2(plot){
    function create_tooltip() {

        var pos = latestPosition;
        var hint_content_table_begin = '<table><tbody>';
        var hint_content = '';

        var axes = plot.getAxes();
        if (pos.x < axes.xaxis.min || pos.x > axes.xaxis.max ||
            pos.y < axes.yaxis.min || pos.y > axes.yaxis.max) {
            remove_tooltip();
            return;
        }


        var i, j, dataset = plot.getData();
        for (i = 0; i < dataset.length; ++i) {

            var series = dataset[i];
            //это костыль для Мозиллы - массив series.data[i] в Мозилле отсортирован в обратном порядке
            //связано это, скорее всего, с разной интерпретацией данных, полученных из БД - числа или строки - разными браузерами
            var mozilla_sign = Math.sign(parseInt(series.data[0][0]) - parseInt(series.data[dataset.length][0]));
            // ищем ближайшую по оси X точку
            for (j = 0; j < series.data.length; ++j) {
                if (Math.sign(parseInt(series.data[j][0]) - parseInt(pos.x)) == -mozilla_sign) {
                    break;
                }
            }

            //Интерполяция
            var y;
            var p1 = series.data[j - 1];
            var p2 = series.data[j];

            if(!p1){
                y = p2[1];
            } else if(!p2){
                y = p1[1];
            } else {
                y = p1[1] + (p2[1] - p1[1]) * (pos.x - p1[0]) / (p2[0] - p1[0]);
            }
            y = p2[1];
            //переводим доли в проценты
            if(data_type[i] == 'persentage'){
                y = y*100;
            }
            hint_content += '<tr style="color:' + plot_colors()[i] + ';"><td style="text-align: right; color:' + plot_colors()[i] + '">'
                                    + data_label[i] + ': </td><td>' + Math.round(y/data_scale[i]) + '</td></tr>';
            var event_time = pos.x;
            var date = new Date(event_time);
            var month = date.getMonth() + 1, day = date.getDate();
            var hours = (date.getHours() >= 10)?date.getHours():('0' + String(date.getHours()));
            var minutes = (date.getMinutes() >= 10)?date.getMinutes():('0' + String(date.getMinutes()));

            var time = hours + ':' + minutes;

        }
        hint_content = '<tr style="color: #fff;"><td style="text-align: right;"> ' +
            'Дата:</td><td>' + day + '/' + month  + ' ' + time + '</td></tr>' + hint_content;
        var hint_content_table_end = '</tbody></table>';
        var hc = hint_content_table_begin + hint_content + hint_content_table_end;
        show_hint(hc);

    }

    $("#plots").bind("plothover",  function (event, pos, item) {
        latestPosition = pos;
        setTimeout(function(){
            create_tooltip();
        }, 0);
    });
}

/**
 * Кроссбраузерное получение координат мыши
 */
var coord_X;
var coord_Y;
document.onmousemove = function (event){
    coord_X = event.clientX;
    coord_Y = event.clientY;
    moveHint();

};

/**
 * Отображение данных рядом с вертикальной crosshair - см. jquery flot
 * @param contents
 */
function show_hint(contents) {
    remove_tooltip();
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: coord_Y + 5,
        left: coord_X + 20,
        padding: '2px',
        size: '10',
        'border-radius': '6px 6px 6px 6px',
        'background-color': '#333333',
        opacity: 0.8,
        'box-shadow': '0 0 10px rgba(0,0,0,0.5)'
    }).appendTo("body").fadeIn(200);
}
function remove_tooltip(){
    $('#tooltip').remove();
}
function moveHint(){
    if($('#plots').width() - (coord_X + 35 + $('#tooltip').width()) > 0){
        $('#tooltip').css({'top': 20, 'left': coord_X + 20})
    }
    else{
        $('#tooltip').css({'top': 20, 'left': coord_X - 20 - $('#tooltip').width()})
    }
}




/**
 * зум графиков
 * пока зум только по оси Х
 */
function zoom_plots(){
    //$("#plots").unbind();
    $("#plots").bind("plotselected", function (event, ranges){
        plot_start_timestamp = ranges.xaxis.to;
        plot_finish_timestamp = ranges.xaxis.from;
        stats_plot();
    })
}


/**
 * Опции графика. Отдельной ф-ей для удобства
 */
var min_tick_size = 0;
var min_tick_size_time_period = 0;
function options_for_plot() {
    var opt = {
        xaxes: [{ show: true,
            mode: "time",
            //minTickSize: [10, 'min'],
            timeformat: '<span class="md">%m/%d</span>'
        }],
        series: {
            points: {
                show: true,
                radius: 0.5
            },
            lines: {
                show: true,
                color: '#39DB4E',
                lineWidth: 1,
                fill: 100,
                fillColor: {colors: [{opacity: 0.3}, {opacity: 0.3}, {opacity: 0.3}]}
            },
            shadowSize: 1
        },
        selection: {
            mode: 'xy'
        },
        legend: {
            container: null,
            show: true
        },
        crosshair: {
            mode: "x"
        },
        grid: {
            hoverable: true,
            clickable: true
        },

        colors: plot_colors()
    };

    return opt;
}


/**
 * Цвета графиков, по порядку: [<общее число обращений к АПИ>, <число ошибок>, <процент ошибок>]
 * @returns {string[]}
 */
function plot_colors(){
    return ["#39DB4E", "#D7DB39", "#DB3939"];
}

/**
 * Скрываем легенду - jquery flot ее отображает автоматически, если в массиве данных ассоциативный
 */
function hide_legend(){
    $('.legend').css({display: 'none'})
}
/**
 * Преобразуем объект в массив. Не используется, но может пригодиться
 * @param obj
 * @return {*}
 */
function obj_to_array(obj){
    var array = $.map(obj, function(value, index) {
        return [value];
    });

    return array;
}

function show_preloader(show){
    if(show){
        $('#preloader').css({visibility: 'visible'})
    }
    else{
        $('#preloader').css({visibility: 'hidden'})
    }
}

function reloader(){
    $('#plots').dblclick(function(){
        window.location.reload();
    })
}

$(document).ready(function(){
    stats_plot();
    hide_legend();
    reloader();
});