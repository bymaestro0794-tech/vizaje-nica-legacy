(function () {
    'use strict';

    function renderFallback(element, data) {
        var width = 720;
        var height = 230;
        var padding = { top: 14, right: 12, bottom: 30, left: 46 };
        var plotWidth = width - padding.left - padding.right;
        var plotHeight = height - padding.top - padding.bottom;
        var values = (data.product_revenue || []).map(Number);
        var maxValue = Math.max.apply(Math, values.concat([1]));
        var ns = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(ns, 'svg');
        var grid = document.createElementNS(ns, 'g');
        var labels = document.createElementNS(ns, 'g');
        var series = document.createElementNS(ns, 'g');

        svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
        svg.setAttribute('preserveAspectRatio', 'none');
        svg.setAttribute('role', 'img');
        svg.setAttribute('aria-label', 'Динамика выручки по товарам');

        for (var tick = 0; tick <= 4; tick += 1) {
            var y = padding.top + (plotHeight * tick / 4);
            var line = document.createElementNS(ns, 'line');
            var label = document.createElementNS(ns, 'text');
            line.setAttribute('x1', padding.left);
            line.setAttribute('x2', width - padding.right);
            line.setAttribute('y1', y);
            line.setAttribute('y2', y);
            line.setAttribute('class', 'analytics-sales-chart__gridline');
            grid.appendChild(line);
            label.setAttribute('x', padding.left - 8);
            label.setAttribute('y', y + 3);
            label.setAttribute('text-anchor', 'end');
            label.setAttribute('class', 'analytics-sales-chart__label');
            label.textContent = Math.round(maxValue * (4 - tick) / 4).toLocaleString('ru-RU');
            labels.appendChild(label);
        }

        function pointAt(index, value, count) {
            return {
                x: count > 1 ? padding.left + (plotWidth * index / (count - 1)) : padding.left + plotWidth / 2,
                y: padding.top + plotHeight - (Math.max(0, Number(value) || 0) / maxValue * plotHeight)
            };
        }

        if (values.length) {
            var points = values.map(function (value, index) {
                var point = pointAt(index, value, values.length);
                return point.x + ',' + point.y;
            }).join(' ');
            var polyline = document.createElementNS(ns, 'polyline');
            polyline.setAttribute('points', points);
            polyline.setAttribute('class', 'analytics-sales-chart__line');
            series.appendChild(polyline);

            if (values.length === 1) {
                var onlyPoint = pointAt(0, values[0], 1);
                var circle = document.createElementNS(ns, 'circle');
                circle.setAttribute('cx', onlyPoint.x);
                circle.setAttribute('cy', onlyPoint.y);
                circle.setAttribute('r', 4);
                circle.setAttribute('class', 'analytics-sales-chart__point');
                series.appendChild(circle);
            }
        }

        (data.labels || []).forEach(function (label, index, allLabels) {
            var step = Math.max(1, Math.ceil(allLabels.length / 5));
            if (index !== 0 && index !== allLabels.length - 1 && index % step !== 0) {
                return;
            }
            var point = pointAt(index, 0, allLabels.length);
            var axisLabel = document.createElementNS(ns, 'text');
            axisLabel.setAttribute('x', point.x);
            axisLabel.setAttribute('y', height - 7);
            axisLabel.setAttribute('text-anchor', index === 0 ? 'start' : (index === allLabels.length - 1 ? 'end' : 'middle'));
            axisLabel.setAttribute('class', 'analytics-sales-chart__label');
            axisLabel.textContent = label;
            labels.appendChild(axisLabel);
        });

        svg.appendChild(grid);
        svg.appendChild(series);
        svg.appendChild(labels);
        element.innerHTML = '';
        element.appendChild(svg);
        element.classList.add('is-fallback-chart');
    }

    function render() {
        var element = document.querySelector('[data-analytics-sales-chart]');
        if (!element) {
            return;
        }

        var data;
        try {
            data = JSON.parse(element.getAttribute('data-chart') || '{}');
        } catch (error) {
            return;
        }

        if (!data.labels || !data.labels.length) {
            return;
        }

        if (!window.echarts) {
            renderFallback(element, data);
            return;
        }

        var chart;
        try {
            chart = window.echarts.init(element);
            chart.setOption({
                animationDuration: 450,
                color: ['#0075dd'],
                grid: { top: 14, right: 12, bottom: 18, left: 50, containLabel: true },
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: '#151922',
                    borderWidth: 0,
                    textStyle: { color: '#fff', fontSize: 12 },
                    padding: [9, 11],
                    formatter: function (items) {
                        var index = items[0].dataIndex;
                        var revenue = Number(data.product_revenue[index] || 0).toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        return '<strong>' + data.labels[index] + '</strong><br>Выручка по товарам: ' + revenue + ' MDL<br>Завершённые заказы: ' + (data.finished_orders[index] || 0) + '<br>Проданные товары: ' + (data.sold_quantity[index] || 0);
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: data.labels,
                    axisLine: { lineStyle: { color: '#e6eaf0' } },
                    axisTick: { show: false },
                    axisLabel: { color: '#8b94a2', fontSize: 10, hideOverlap: true },
                    splitLine: { show: false }
                },
                yAxis: {
                    type: 'value',
                    min: 0,
                    axisLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: {
                        color: '#9aa3b0',
                        fontSize: 10,
                        formatter: function (value) {
                            return value >= 1000 ? (value / 1000).toFixed(value >= 10000 ? 0 : 1) + 'k' : value;
                        }
                    },
                    splitLine: { lineStyle: { color: '#edf0f4', type: 'dashed' } }
                },
                series: [{
                    name: 'Выручка по товарам',
                    type: 'line',
                    smooth: true,
                    showSymbol: data.labels.length < 2,
                    symbolSize: 7,
                    lineStyle: { width: 2.5, color: '#0075dd' },
                    areaStyle: { color: 'rgba(0, 117, 221, 0.07)' },
                    data: data.product_revenue || []
                }]
            });
        } catch (error) {
            renderFallback(element, data);
            return;
        }

        window.addEventListener('resize', function () {
            chart.resize();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', render);
    } else {
        render();
    }
}());
