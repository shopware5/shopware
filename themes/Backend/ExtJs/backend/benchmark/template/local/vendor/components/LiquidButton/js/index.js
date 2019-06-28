$(function () {
    // Vars
    var pointsA = [],
        pointsB = [],
        $canvas = null,
        canvas = null,
        context = null,
        vars = null,
        points = 8,
        viscosity = 5,
        mouseDist = 70,
        damping = 0.05,
        showIndicators = false;
    mouseX = 0,
        mouseY = 0,
        relMouseX = 0,
        relMouseY = 0,
        mouseLastX = 0,
        mouseLastY = 0,
        mouseDirectionX = 0,
        mouseDirectionY = 0,
        mouseSpeedX = 0,
        mouseSpeedY = 0;

    /**
     * Get mouse direction
     */
    function mouseDirection(e) {
        if (mouseX < e.pageX)
            mouseDirectionX = 1;
        else if (mouseX > e.pageX)
            mouseDirectionX = -1;
        else
            mouseDirectionX = 0;

        if (mouseY < e.pageY)
            mouseDirectionY = 1;
        else if (mouseY > e.pageY)
            mouseDirectionY = -1;
        else
            mouseDirectionY = 0;

        mouseX = e.pageX;
        mouseY = e.pageY;

        relMouseX = (mouseX - $canvas.offset().left);
        relMouseY = (mouseY - $canvas.offset().top);
    }

    $(document).on('mousemove', mouseDirection);

    /**
     * Get mouse speed
     */
    function mouseSpeed() {
        mouseSpeedX = mouseX - mouseLastX;
        mouseSpeedY = mouseY - mouseLastY;

        mouseLastX = mouseX;
        mouseLastY = mouseY;

        setTimeout(mouseSpeed, 50);
    }

    mouseSpeed();

    /**
     * Init button
     */
    function initButton() {
        // Get button
        var button = $('.btn-liquid');
        var buttonWidth = button.width();
        var buttonHeight = button.height();

        // Create canvas
        $canvas = $('<canvas></canvas>');
        button.append($canvas);

        canvas = $canvas.get(0);
        canvas.width = buttonWidth + 100;
        canvas.height = buttonHeight + 100;
        context = canvas.getContext('2d');

        // Add points

        var x = buttonHeight / 2;
        for (var j = 1; j < points; j++) {
            addPoints((x + ((buttonWidth - buttonHeight) / points) * j), 0);
        }
        addPoints(buttonWidth - buttonHeight / 5, 0);
        addPoints(buttonWidth + buttonHeight / 10, buttonHeight / 2);
        addPoints(buttonWidth - buttonHeight / 5, buttonHeight);
        for (var j = points - 1; j > 0; j--) {
            addPoints((x + ((buttonWidth - buttonHeight) / points) * j), buttonHeight);
        }
        addPoints(buttonHeight / 5, buttonHeight);

        addPoints(-buttonHeight / 10, buttonHeight / 2);
        addPoints(buttonHeight / 5, 0);

        // Start render
        renderCanvas();
    }

    /**
     * Add points
     */
    function addPoints(x, y) {
        pointsA.push(new Point(x, y, 1));
        pointsB.push(new Point(x, y, 2));
    }

    /**
     * Point
     */
    function Point(x, y, level) {
        this.x = this.ix = 50 + x;
        this.y = this.iy = 50 + y;
        this.vx = 0;
        this.vy = 0;
        this.cx1 = 0;
        this.cy1 = 0;
        this.cx2 = 0;
        this.cy2 = 0;
        this.level = level;
    }

    Point.prototype.move = function () {
        this.vx += (this.ix - this.x) / (viscosity * this.level);
        this.vy += (this.iy - this.y) / (viscosity * this.level);

        var dx = this.ix - relMouseX,
            dy = this.iy - relMouseY;
        var relDist = (1 - Math.sqrt((dx * dx) + (dy * dy)) / mouseDist);

        // Move x
        if ((mouseDirectionX > 0 && relMouseX > this.x) || (mouseDirectionX < 0 && relMouseX < this.x)) {
            if (relDist > 0 && relDist < 1) {
                this.vx = (mouseSpeedX / 4) * relDist;
            }
        }
        this.vx *= (1 - damping);
        this.x += this.vx;

        // Move y
        if ((mouseDirectionY > 0 && relMouseY > this.y) || (mouseDirectionY < 0 && relMouseY < this.y)) {
            if (relDist > 0 && relDist < 1) {
                this.vy = (mouseSpeedY / 4) * relDist;
            }
        }
        this.vy *= (1 - damping);
        this.y += this.vy;
    };

    /**
     * Render canvas
     */
    function renderCanvas() {
        // rAF
        requestAnimationFrame(renderCanvas);

        // Clear scene
        context.clearRect(0, 0, $canvas.width(), $canvas.height());
        context.fillStyle = '#213448';
        context.fillRect(0, 0, $canvas.width(), $canvas.height());

        // Move points
        for (var i = 0; i <= pointsA.length - 1; i++) {
            pointsA[i].move();
            pointsB[i].move();
        }

        // Create dynamic gradient
        var gradientX = Math.min(Math.max(mouseX - $canvas.offset().left, 0), $canvas.width());
        var gradientY = Math.min(Math.max(mouseY - $canvas.offset().top, 0), $canvas.height());
        var distance = Math.sqrt(Math.pow(gradientX - $canvas.width() / 2, 2) + Math.pow(gradientY - $canvas.height() / 2, 2)) / Math.sqrt(Math.pow($canvas.width() / 2, 2) + Math.pow($canvas.height() / 2, 2));

        var gradient = context.createRadialGradient(gradientX, gradientY, 300 + (300 * distance), gradientX, gradientY, 0);
        gradient.addColorStop(0, '#511CFC');
        gradient.addColorStop(1, '#55D3E1');

        // Draw shapes
        var groups = [pointsA, pointsB]

        for (var j = 0; j <= 1; j++) {
            var points = groups[j];

            if (j == 0) {
                // Background style
                context.fillStyle = '#189EFF';
            } else {
                // Foreground style
                context.fillStyle = gradient;
            }

            context.beginPath();
            context.moveTo(points[0].x, points[0].y);

            for (var i = 0; i < points.length; i++) {
                var p = points[i];
                var nextP = points[i + 1];
                if (nextP != undefined) {
                    p.cx1 = (p.x + nextP.x) / 2;
                    p.cy1 = (p.y + nextP.y) / 2;
                    p.cx2 = (p.x + nextP.x) / 2;
                    p.cy2 = (p.y + nextP.y) / 2;

                    context.bezierCurveTo(p.x, p.y, p.cx1, p.cy1, p.cx1, p.cy1);
                } else {
                    nextP = points[0];
                    p.cx1 = (p.x + nextP.x) / 2;
                    p.cy1 = (p.y + nextP.y) / 2;

                    context.bezierCurveTo(p.x, p.y, p.cx1, p.cy1, p.cx1, p.cy1);
                }
            }

            context.fill();
        }

        if (showIndicators) {
            // Draw points
            context.beginPath();
            for (var i = 0; i < pointsA.length; i++) {
                var p = pointsA[i];

                context.rect(p.x - 1, p.y - 1, 2, 2);
            }
            context.fill();

            // Draw controls
            context.beginPath();
            for (var i = 0; i < pointsA.length; i++) {
                var p = pointsA[i];

                context.rect(p.cx1 - 1, p.cy1 - 1, 2, 2);
                context.rect(p.cx2 - 1, p.cy2 - 1, 2, 2);
            }
            context.fill();
        }
    }

    // Init
    initButton();
});
