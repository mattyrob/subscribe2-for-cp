module.exports = {
    "env": {
        "browser": true,
        "jquery": true
    },

    "extends": "eslint:recommended",

	"rules": {
		"block-scoped-var": "error",
		"comma-dangle": "error",
		"comma-spacing": [
			"error",
			{
				"before": false,
				"after": true
			}
		],
		"comma-style": [
			"error", "last"
		],
		"curly": "error",
		"eol-last": [
			"error",
			"always"
		],
		"eqeqeq": "error",
		"indent": [
        	"error",
        	"tab",
        	{
        		"SwitchCase": 1
        	}
        ],
		"key-spacing": "error",
		"linebreak-style": [
			"error",
			"unix"
		],
		"no-console": "error",
		"no-else-return": "error",
		"no-eval": "error",
		"no-extra-parens": "error",
		"no-implied-eval": "error",
		"one-var-declaration-per-line": [
			"error",
			"initializations"
		],
        "semi": [
        	"error",
        	"always"
        ],
        "semi-spacing": "error",
		"space-in-parens": [
			"error",
			"always",
			{
				"exceptions": [
					"empty",
				]
			}
		],
		"space-unary-ops": [
			"error",
			{
				"words": true,
         	 	"nonwords": true,
         	 	"overrides": {
         	 		"++": false,
         	 		"-": false
         	 	}
			}
		],
		"vars-on-top": "error",
		"yoda": [
			"error",
			"always"
		]
	},

    "parserOptions": {
        "ecmaVersion": 6
    }
};
