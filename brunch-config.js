'use strict';

exports.config = {
    optimize: false,
    paths: {
        watched: ["app"],
        public: "web"
    },
    files: {
        stylesheets: {
            "joinTo": "assets/app.min.css"
        },
        javascripts: {
            "joinTo": "assets/app.min.js"
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
