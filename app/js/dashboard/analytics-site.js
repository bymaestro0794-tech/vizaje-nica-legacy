(function () {
    'use strict';

    function renderFallbackChart(element, data) {
        var width = 720;
        var height = 240;
        var padding = { top: 16, right: 12, bottom: 30, left: 42 };
        var plotWidth = width - padding.left - padding.right;
        var plotHeight = height - padding.top - padding.bottom;
        var visitors = data.visitors || [];
        var sessions = data.sessions || [];
        var values = visitors.concat(sessions).map(Number);
        var maxValue = Math.max.apply(Math, values.concat([1]));
        var svgNs = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(svgNs, 'svg');
        var grid = document.createElementNS(svgNs, 'g');
        var labels = document.createElementNS(svgNs, 'g');
        var series = document.createElementNS(svgNs, 'g');

        svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
        svg.setAttribute('preserveAspectRatio', 'none');
        svg.setAttribute('role', 'img');
        svg.setAttribute('aria-label', 'График посетителей и сессий');

        for (var tick = 0; tick <= 4; tick += 1) {
            var y = padding.top + (plotHeight * tick / 4);
            var value = Math.round(maxValue * (4 - tick) / 4);
            var line = document.createElementNS(svgNs, 'line');
            var tickLabel = document.createElementNS(svgNs, 'text');
            line.setAttribute('x1', padding.left);
            line.setAttribute('x2', width - padding.right);
            line.setAttribute('y1', y);
            line.setAttribute('y2', y);
            line.setAttribute('class', 'analytics-site-chart__gridline');
            grid.appendChild(line);
            tickLabel.setAttribute('x', padding.left - 9);
            tickLabel.setAttribute('y', y + 3);
            tickLabel.setAttribute('text-anchor', 'end');
            tickLabel.setAttribute('class', 'analytics-site-chart__axis-label');
            tickLabel.textContent = value;
            labels.appendChild(tickLabel);
        }

        function pointAt(index, value, count) {
            return {
                x: count > 1 ? padding.left + (plotWidth * index / (count - 1)) : padding.left + plotWidth / 2,
                y: padding.top + plotHeight - (Math.max(0, Number(value) || 0) / maxValue * plotHeight)
            };
        }

        function drawLine(valuesToDraw, className) {
            if (!valuesToDraw.length) {
                return;
            }
            var points = valuesToDraw.map(function (value, index) {
                var point = pointAt(index, value, valuesToDraw.length);
                return point.x + ',' + point.y;
            }).join(' ');
            var polyline = document.createElementNS(svgNs, 'polyline');
            polyline.setAttribute('points', points);
            polyline.setAttribute('class', className);
            series.appendChild(polyline);

            if (valuesToDraw.length === 1) {
                var onlyPoint = pointAt(0, valuesToDraw[0], 1);
                var circle = document.createElementNS(svgNs, 'circle');
                circle.setAttribute('cx', onlyPoint.x);
                circle.setAttribute('cy', onlyPoint.y);
                circle.setAttribute('r', 4);
                circle.setAttribute('class', className + ' analytics-site-chart__point');
                series.appendChild(circle);
            }
        }

        drawLine(sessions, 'analytics-site-chart__sessions');
        drawLine(visitors, 'analytics-site-chart__visitors');

        (data.labels || []).forEach(function (label, index, allLabels) {
            var step = Math.max(1, Math.ceil(allLabels.length / 5));
            if (index !== 0 && index !== allLabels.length - 1 && index % step !== 0) {
                return;
            }
            var point = pointAt(index, 0, allLabels.length);
            var axisLabel = document.createElementNS(svgNs, 'text');
            axisLabel.setAttribute('x', point.x);
            axisLabel.setAttribute('y', height - 8);
            axisLabel.setAttribute('text-anchor', index === 0 ? 'start' : (index === allLabels.length - 1 ? 'end' : 'middle'));
            axisLabel.setAttribute('class', 'analytics-site-chart__axis-label');
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

    function renderTrafficChart() {
        var element = document.querySelector('[data-analytics-site-chart]');

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
            renderFallbackChart(element, data);
            return;
        }

        var chart;
        try {
            chart = window.echarts.init(element);
            chart.setOption({
            animationDuration: 450,
            color: ['#0075dd', '#cbd2db'],
            grid: { top: 15, right: 12, bottom: 18, left: 42, containLabel: true },
            tooltip: {
                trigger: 'axis',
                backgroundColor: '#151922',
                borderWidth: 0,
                textStyle: { color: '#fff', fontSize: 12 },
                padding: [9, 11]
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
                minInterval: 1,
                axisLine: { show: false },
                axisTick: { show: false },
                axisLabel: { color: '#9aa3b0', fontSize: 10 },
                splitLine: { lineStyle: { color: '#edf0f4', type: 'dashed' } }
            },
            series: [
                {
                    name: 'Посетители',
                    type: 'line',
                    smooth: true,
                    showSymbol: data.labels.length < 2,
                    symbolSize: 7,
                    lineStyle: { width: 2, color: '#0075dd' },
                    areaStyle: { color: 'rgba(0, 117, 221, 0.055)' },
                    data: data.visitors || []
                },
                {
                    name: 'Сессии',
                    type: 'line',
                    smooth: true,
                    showSymbol: data.labels.length < 2,
                    symbolSize: 6,
                    lineStyle: { width: 1.5, color: '#cbd2db' },
                    data: data.sessions || []
                }
            ]
            });
        } catch (error) {
            renderFallbackChart(element, data);
            return;
        }

        window.addEventListener('resize', function () {
            chart.resize();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderTrafficChart);
    } else {
        renderTrafficChart();
    }
}());
