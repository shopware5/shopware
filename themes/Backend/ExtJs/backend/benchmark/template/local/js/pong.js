(function($) {

    function PongGame(el) {
        this.$el = $(el);

        this.initGlobals();
        this.setupCanvas.call(this);
        this.initializeStartButton();
        this.initializeSounds();
        this.initializeGame();
    };

    PongGame.prototype.initGlobals = function () {
        this.states = {
            'idle': 0,
            'up': 1,
            'down': 2,
            'left': 3,
            'right': 4
        };

        this.colors = {
            background: '#1B2C3D',
            paddle: '#FFFFFF',
            ball: '#189EFF'
        };
    };

    PongGame.prototype.initializeGame = function () {
        this.player = this.createPaddle('left');
        this.paddle = this.createPaddle('right');
        this.ball = this.createBall();

        this.running = false;
        this.turn = this.paddle;
        this.timer = this.round = 0;
        this.color = this.colors.background;

        this.draw();
        this.listen();
    };

    PongGame.prototype.initializeSounds = function () {
        this.sounds = {
            beep1: new Audio("data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU="),
            beep2: new Audio("data:audio/wav;base64,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")
        };

        this.sounds.beep1.volume = 0.3;
        this.sounds.beep2.volume = 0.1;
    };

    PongGame.prototype.createBall = function () {
        return {
            width: 12,
            height: 12,
            x: (this.canvas.width / 2) - 5,
            y: (this.canvas.height / 2) - 5,
            moveX: this.states.idle,
            moveY: this.states.idle,
            speed: 9
        }
    };

    // Creates a paddle. If side === 'left', the player is created
    PongGame.prototype.createPaddle = function (side) {
        return {
            width: 18,
            height: 100,
            // 'Left' === Player
            x: side === 'left' ? 30 : this.canvas.width - 30 - 18,
            y: (this.canvas.height / 2) - 35,
            move: this.states.idle,
            speed: side === 'left' ? 10 : 7
        }
    };

    PongGame.prototype.setupCanvas = function () {
        this.canvas = this.$el[0];
        this.context = this.canvas.getContext('2d');

        this.canvas.width = 900;
        this.canvas.height = 480;

        this.canvas.style.width = (this.canvas.width / 2) + 'px';
        this.canvas.style.height = (this.canvas.height / 2) + 'px';
    };

    PongGame.prototype.initializeStartButton = function () {
        var me = this;
        this.startButton = document.querySelector('.pong-start');
        this.startButton.addEventListener('click', function () {
            // Handle the 'Press any key to begin' function and start the game.
            if (me.running === false) {
                me.running = true;
                window.requestAnimationFrame($.proxy(me.loop, me));
                me.startButton.style.display = 'none';
            }
        });
    };

    // Setup necessary events
    PongGame.prototype.listen = function () {
        var me = this;

        document.addEventListener('keydown', function (key) {
            // Handle up arrow and w key events
            if (key.keyCode === 38 || key.keyCode === 87) me.player.move = me.states.up;

            // Handle down arrow and s key events
            if (key.keyCode === 40 || key.keyCode === 83) me.player.move = me.states.down;
        });

        // Stop the player from moving when there are no keys being pressed.
        document.addEventListener('keyup', function () {
            me.player.move = me.states.idle;
        });
    };

    PongGame.prototype.loop = function () {
        this.update();
        this.draw();

        // If the game is not over, draw the next frame.
        if (!this.over) requestAnimationFrame($.proxy(this.loop, this));
    };

    PongGame.prototype.update = function () {
        var playerLeft = this.player.x,
            playerRight = playerLeft + this.player.width,
            ballLeft = this.ball.x,
            ballRight = ballLeft + this.ball.width,
            // Ball doesn't fly up and down as fast as it's flying to the sides
            ballSpeedY = this.ball.speed / 1.5;

        // Ball left the left bound limit - player failed to hit the ball
        if (ballLeft <= 0) {
            this.resetTurn(this.player);
        }

        // Ball left the right bound limit - paddle failed to hit the ball
        if (ballRight >= this.canvas.width) {
            this.resetTurn(this.paddle);
        }

        // Ball hit the upper bound limits, turn direction
        if (this.ball.y  - this.ball.height <= 0) {
            this.ball.moveY = this.states.down
        }

        // Ball hit the bottom bound limits, turn direction
        if (this.ball.y + this.ball.height >= this.canvas.height) {
            this.ball.moveY = this.states.up;
        }

        // Move player in direction
        if (this.player.move === this.states.up) {
            this.player.y -= this.player.speed
        } else if (this.player.move === this.states.down) {
            this.player.y += this.player.speed;
        }

        // On new serve (start of each turn) move the ball to the correct side and randomize the direction to add some challenge
        if (this.turnDelayIsOver() && this.turn) {

            // Depending on the last loser, the ball will start on his side
            this.ball.moveX = this.turn === this.player ? this.states.left : this.states.right;

            // Randomize flying direction
            this.ball.moveY = [this.states.up, this.states.down][Math.round(Math.random())];
            this.ball.y = Math.floor(Math.random() * this.canvas.height - 200) + 200;

            // Reset last loser
            this.turn = null;
        }

        // Limit the player movement to the canvas bound limits
        if (this.player.y <= 0) {
            this.player.y = 0;
        } else if (this.player.y >= this.canvas.height - this.player.height) {
            this.player.y = this.canvas.height - this.player.height;
        }

        // Handle player - ball collisions
        // Checks if ball is inside the player on X axis
        if (ballLeft <= playerRight + this.ball.width && ballLeft >= playerRight) {
            // Check if player hit the ball on Y axis
            if (this.ball.y <= this.player.y + this.player.height && this.ball.y + this.ball.height >= this.player.y) {
                // Set the ball to the front of the player
                this.ball.x = (this.player.x + this.player.width + this.ball.width);

                this.ball.moveX = this.states.right;
                this.sounds.beep1.play();
            }
        }

        // Move ball
        // Handle Y axis
        if (this.ball.moveY === this.states.up) {
            this.ball.y -= ballSpeedY;
        } else if (this.ball.moveY === this.states.down) {
            this.ball.y += ballSpeedY;
        }

        // Handle X axis
        if (this.ball.moveX === this.states.left) {
            this.ball.x -= this.ball.speed;
        } else if (this.ball.moveX === this.states.right) {
            this.ball.x += this.ball.speed
        }

        // Handle paddle UP and DOWN movement
        // Try to have the center of the paddle to be on the same height as the ball
        // Handle moving upwards
        if (this.paddle.y > this.ball.y - (this.paddle.height / 2)) {
            // Make it look like the paddle is trying harder once the ball flies into its direction
            // Seems to chill when ball is flying to the player, not to the paddle
            if (this.ball.moveX === this.states.right) {
                this.paddle.y -= this.paddle.speed / 1.5
            } else {
                this.paddle.y -= this.paddle.speed / 4;
            }
        }

        // Handle moving downwards
        if (this.paddle.y < this.ball.y - (this.paddle.height / 2)) {
            // Make it look like the paddle is trying harder once the ball flies into its direction
            // Seems to chill when ball is flying to the player, not to the paddle
            if (this.ball.moveX === this.states.right) {
                this.paddle.y += this.paddle.speed / 1.5;
            } else {
                this.paddle.y += this.paddle.speed / 4;
            }
        }

        // Handle paddle ball collsion
        // Handle X axis hit
        if (this.ball.x - this.ball.width <= this.paddle.x && this.ball.x >= this.paddle.x - this.paddle.width) {
            // Handle Y axis hit
            if (this.ball.y <= this.paddle.y + this.paddle.height && this.ball.y + this.ball.height >= this.paddle.y) {
                // Place ball in front of paddle
                this.ball.x = (this.paddle.x - this.ball.width);
                this.ball.moveX = this.states.left;

                this.sounds.beep2.play();
            }
        }

        // Limit the paddle movement to the canvas bound limits
        if (this.paddle.y <= 0) {
            this.paddle.y = 0;
        } else if (this.paddle.y >= this.canvas.height - this.paddle.height) {
            this.paddle.y = this.canvas.height - this.paddle.height;
        }
    };

    PongGame.prototype.draw = function () {
        // Clear the Canvas
        this.context.clearRect(
            0,
            0,
            this.canvas.width,
            this.canvas.height
        );

        // Set the fill style for the background
        this.context.fillStyle = this.color;

        // Draw the background
        this.context.fillRect(
            0,
            0,
            this.canvas.width,
            this.canvas.height
        );

        // Set the fill style for the paddles
        this.context.fillStyle = this.colors.paddle;

        // Draw the Player
        this.context.fillRect(
            this.player.x,
            this.player.y,
            this.player.width,
            this.player.height
        );

        // Draw the Paddle
        this.context.fillRect(
            this.paddle.x,
            this.paddle.y,
            this.paddle.width,
            this.paddle.height
        );

        // Draw the Ball
        if (this.turnDelayIsOver()) {
            this.context.beginPath();
            this.context.arc(this.ball.x, this.ball.y, this.ball.width, 0, 2 * Math.PI, false);
            this.context.fillStyle = this.colors.ball;
            this.context.fill();
            this.context.stroke();
        }

        // Reset fill style
        this.context.fillStyle = this.colors.paddle;
    };

    // Wait for a delay to have passed after each turn.
    PongGame.prototype.turnDelayIsOver = function () {
        return ((new Date()).getTime() - this.timer >= 1000);
    };

    PongGame.prototype.resetTurn = function (loser) {
        this.ball = this.createBall();
        this.turn = loser;
        this.timer = (new Date()).getTime();
    };

    $.fn.pongGame = function() {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_pongGame')) {
                return;
            }

            var plugin = new PongGame(this);
            $el.data('plugin_pongGame', plugin);
        });
    };

    $(function() {
        $('*[data-pong="true"]').pongGame();
    });
})(jQuery);
