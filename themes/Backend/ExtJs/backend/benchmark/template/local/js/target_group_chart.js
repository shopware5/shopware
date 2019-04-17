(function ($) {
    'use strict';

    var globals = {
        width: 255,
        height: 255,
        lineThickness: 33,
        gapBetweenLines: 4,
        colors: {
            local: {
                desktop: 'rgba(52, 220, 221, 1.0)',
                mobile: 'rgba(52, 220, 221, 0.5)',
                tablet: 'rgba(52, 220, 221, 0.8)'
            },
            industry: {
                desktop: 'rgba(106, 99, 252, 1.0)',
                mobile: 'rgba(106, 99, 252, 0.5)',
                tablet: 'rgba(106, 99, 252, 0.8)'
            }
        }
    };

    function TargetGroupGraph(el) {
        this.$el = $(el);
        this.isIndustry = (el.getAttribute('data-industry') === 'true');

        this.init();
    }

    TargetGroupGraph.prototype.init = function () {
        var outerRadius = (globals.width / 2),
            innerRadius = outerRadius - 30,
            benchmarkData = window.benchmarkData,
            key = this.isIndustry ? 'industry' : 'local',
            colorKey = key,
            circles,
            svg;

        if (!benchmarkData['industry']) {
            key = 'local';
        }

        circles = [
            { name: 'desktop', percent: benchmarkData[key].devices.desktop, color: globals.colors[colorKey].desktop },
            { name: 'mobile', percent: benchmarkData[key].devices.mobile, color: globals.colors[colorKey].mobile },
            { name: 'tablet', percent: benchmarkData[key].devices.tablet, color: globals.colors[colorKey].tabvar }
        ];

        svg = d3.select('.' + this.$el.attr('class'))
            .append("svg")
            .attr({
                width: globals.width,
                height: globals.height,
                class: 'shadow'
            })
            .append('g')
            .attr({
                transform: 'translate(' + globals.width / 2 + ',' + globals.height / 2 + ')'
            });

        for (var i = 0; i < circles.length; ++i) {
            if (i > 0) {
                outerRadius = innerRadius - globals.gapBetweenLines;
            }
            innerRadius = outerRadius - globals.lineThickness;

            circles[i].chart = this.createCircle(svg, outerRadius, innerRadius, circles[i].color, circles[i].percent);

            this.addText(svg, circles[i].percent, outerRadius);
        }
    };

    TargetGroupGraph.prototype.createCircle = function (svg, outerRadius, innerRadius, color, percent) {
        var ratio = percent / 100,
            arcBackground,
            arcForeground,
            pathForeground,
            chart;

        arcBackground = d3.svg.arc()
            .innerRadius(innerRadius)
            .outerRadius(outerRadius)
            .startAngle(0)
            .endAngle(2 * Math.PI);

        svg.append('path')
            .attr({
                d: arcBackground
            })
            .style({
                fill: 'rgba(52, 220, 221, 0.4)',
                opacity: .2
            });

        arcForeground = d3.svg.arc()
            .innerRadius(innerRadius)
            .outerRadius(outerRadius)
            .cornerRadius(20)
            .startAngle(-0.05);

        pathForeground = svg.append('path')
            .datum({ endAngle:0 })
            .attr({
                d: arcForeground
            })
            .style({
                fill: color
            });

        pathForeground.transition()
            .duration(1500)
            .ease('elastic')
            .call(this.arcTween, ((2 * Math.PI)) * ratio, arcForeground);

        chart = { path: pathForeground, arc: arcForeground };

        return chart;
    };

    TargetGroupGraph.prototype.arcTween = function (transition, newAngle, arc) {
        transition.attrTween("d", function (d) {
            var interpolate = d3.interpolate(d.endAngle, newAngle);

            return function (t) {
                d.endAngle = interpolate(t);
                return arc(d);
            };
        });
    };

    TargetGroupGraph.prototype.addText = function (svg, value, outerRadius) {
        svg.append('text')
            .attr({
                'font-size': '15px',
                transform:'translate(5,' + (-outerRadius + 23) + ')',
                style: 'fill: #FFFFFF;'
            })
            .html(value);
    };

    $.fn.targetGroupGraph = function() {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_targetGroupGraph')) {
                return;
            }

            var plugin = new TargetGroupGraph(this);
            $el.data('plugin_targetGroupGraph', plugin);

        });
    };

    $(function() {
        $('*[data-target-group-chart="true"]').targetGroupGraph();
    });
})(jQuery);
