module.exports = {
    extends: 'standard',
    plugins: [
        'jest'
    ],
    env: {
        'jest/globals': true
    },
    rules: {
        'no-var': 'off',
        'node/no-callback-literal': 'off',
        'multiline-ternary': 'off',
        'arrow-parens': 0,
        'space-before-function-paren': 0,
        'keyword-spacing': [
            'warn'
        ],
        'padded-blocks': [
            'warn'
        ],
        'space-in-parens': [
            'warn'
        ],
        'generator-star-spacing': 0,
        'no-shadow-restricted-names': 0,
        eqeqeq: 0,
        'no-debugger': 0,
        semi: [
            'error',
            'always'
        ],
        'one-var': 0,
        indent: [
            'error',
            4,
            { SwitchCase: 1 }
        ],

        'standard/no-callback-literal': 0
    }
};
