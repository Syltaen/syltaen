// =============================================================================
// > ASSETS
// =============================================================================

// Utils
const project              = require("./package.json");
const webpack              = require("webpack");
const path                 = require("path")

// Plugins
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const LiveReloadPlugin     = require("webpack-livereload-plugin")
const BrowserSyncPlugin    = require("browser-sync-webpack-plugin")
const TerserPlugin         = require("terser-webpack-plugin");
const CssMinimizerPlugin   = require("css-minimizer-webpack-plugin")
const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack-plugin')


module.exports = {
    cache: true,
    stats: "errors-only",

    // IN
    entry: {
        "bundle": [
            "./scripts/builder.coffee",
            "./styles/builder.sass"
        ],
        "admin" : [
            "./scripts/admin.coffee",
            "./styles/admin.sass",
        ]
    },

    // OUT
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: 'js/[name].js',
    },

    // BUILD
    optimization: {
        minimizer: [
            new TerserPlugin(),
            new CssMinimizerPlugin()
        ],
    },

    // ==================================================
    // > EXTERNAL(S)
    // ==================================================
    externals: {
        jquery: "jQuery"
    },

    // ==================================================
    // > RULES
    // ==================================================
    module: {
        rules: [

            // Coffee
            {
                test: /\.coffee$/,
                loader: "coffee-loader",
            },

            // Sass
            {
                test: /\.sass$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    { loader: "css-loader", options: { url: false }, },
                    { loader: "postcss-loader", options: { postcssOptions: { plugins: [require("autoprefixer")({"overrideBrowserslist": ["> 1%", "last 10 versions"]})] }}},
                    "sass-loader"
                ],
            },
        ],
    },

    // ==================================================
    // > PLUGINS
    // ==================================================
    plugins: [

        // Makes jQuery Available everywhere
        new webpack.ProvidePlugin({
            $:      "jquery",
            jQuery: "jquery"
        }),

        // Extract CSS to their own files
        new MiniCssExtractPlugin({
            chunkFilename: "[name].css",
            filename: "css/[name].css"
        }),

        // Allow SASS live reload
        new LiveReloadPlugin({
            appendScriptTag: true,
            ignore: /\.js$|\.map$|\.html$/
        }),

        // Allow brower auto-reload on php/pug file changes
        new BrowserSyncPlugin({
            proxy: "http://localhost/" + project.name,
            files: [
                "**/*.php",
                "**/*.pug"
            ]
        }, {reload: false}),

        new FriendlyErrorsWebpackPlugin(),
    ]
}