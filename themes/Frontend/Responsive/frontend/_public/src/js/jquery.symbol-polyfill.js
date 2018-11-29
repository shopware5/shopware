// https://github.com/stephband/jquery.event.move/issues/41#issuecomment-288851979

if (!window.Symbol) {
    (function(window) {
        var defineProperty = Object.defineProperty;
        var prefix = '__symbol-' + Math.ceil(Math.random() * 1000000000) + '-';
        var id = 0;

        function Symbol(description) {
            if (!(this instanceof Symbol)) { return new Symbol(description); }
            var symbol = prefix + id++;
            this._symbol = symbol;
        }

        defineProperty(Symbol.prototype, 'toString', {
            enumerable: false,
            configurable: false,
            writable: false,
            value: function toString() {
                return this._symbol;
            }
        });

        window.Symbol = Symbol;
    }(this));
}
