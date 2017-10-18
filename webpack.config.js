// =============================================================================
// > Assets
// =============================================================================
const project                     = require("./package.json");
const webpack                     = require("webpack");
const autoprefixer                = require("autoprefixer");
const path                        = require("path");
const ExtractTextPlugin           = require("extract-text-webpack-plugin");
const UglifyJSPlugin              = require('uglifyjs-webpack-plugin');
const OptimizeCssAssetsPlugin     = require('optimize-css-assets-webpack-plugin');
const LiveReloadPlugin            = require('webpack-livereload-plugin');
const BrowserSyncPlugin           = require('browser-sync-webpack-plugin');
const FriendlyErrorsWebpackPlugin = require("friendly-errors-webpack-plugin")


// =============================================================================
// > Extracted outputs
// =============================================================================
const bundle_css = new ExtractTextPlugin("css/bundle.css");
const admin_css  = new ExtractTextPlugin("css/admin.css");


// =============================================================================
// > CONFIG
// =============================================================================
module.exports = env => {

    var config = {
        // ==================================================
        // > ENTRY
        // ==================================================
        entry: [
            "./scripts/builder.js",
            "./styles/builder.sass",
            "./styles/layouts/templates/admin.sass"
        ],

        devtool: "source-map",

        // ==================================================
        // > OUTPUT(S)
        // ==================================================
        output: {
            path: path.resolve(__dirname, "build"),
            filename: "js/bundle.js"
        },

        // ==================================================
        // > MODULES
        // ==================================================
        module: {
            rules: [

                // ========== COFFEESCRIPT ========== //
                {
                    test: /\.coffee$/,
                    use: ['coffee-loader?sourceMap']
                },

                // ========== SASS ========== //
                {
                    test: /admin\.sass$/,
                    use: admin_css.extract({
                        fallback: 'style-loader',
                        use: [
                            { loader: "css-loader", options: { url: false, sourceMap: true }, },
                            { loader: "postcss-loader", options: { plugins: () => [autoprefixer], sourceMap: true }},
                            "sass-loader?sourceMap"
                        ]
                    })
                },
                {
                    test: /builder\.sass$/,
                    use: bundle_css.extract({
                        fallback: 'style-loader',
                        use: [
                            { loader: "css-loader", options: { url: false, sourceMap: true }, },
                            { loader: "postcss-loader", options: { plugins: () => [autoprefixer], sourceMap: true }},
                            "sass-loader?sourceMap"
                        ]
                    })
                }
            ]
        },

        // ==================================================
        // > PUGINS
        // ==================================================
        plugins: [

            // ========== DEV ========== //
            new LiveReloadPlugin({
                appendScriptTag: true,
                ignore: /\.js$|\.map$/
            }),

            new BrowserSyncPlugin({
                proxy: "http://localhost/"+project.name,
                // port: 3000,
                files: [
                    {
                        match: [
                            '**/*.php',
                            "**/*.pug"
                        ],
                        fn: function(event, file) {
                            if (event === "change") require('browser-sync').get('bs-webpack-plugin').reload();
                        }
                    }
                ]
            },
            {
                reload: false
            }),

            new FriendlyErrorsWebpackPlugin(),

            bundle_css,
            admin_css
        ]
    };

    // ==================================================
    // > ENVIRONEMENTS PLUGINS
    // ==================================================
    if (env.prod) {
        config.plugins.push(new UglifyJSPlugin());
        config.plugins.push(new OptimizeCssAssetsPlugin());
    }


    // ========== RETURN ========== //
    return config;
};
