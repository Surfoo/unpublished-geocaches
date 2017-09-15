'use strict';

exports.config = {
    optimize: true,
    paths: {
        watched: ["app"],
        public: "web"
    },
    files: {
        stylesheets: {
            "joinTo": "css/app.min.css"
        },
        javascripts: {
            "joinTo": "js/app.min.js"
        }
    },
    overrides: {
        production: {
            optimize: true,
            sourceMaps: false,
            plugins: {
                autoReload: {
                    enabled: false
                }
            }
        }
    },
    plugins: {
        babel: {
            presets: ['latest']
        }
    }
}
