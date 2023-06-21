var Encore = require("@symfony/webpack-encore");
const CopyPlugin = require("copy-webpack-plugin");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  // public path used by the web server to access the output path
  .setPublicPath("/build")
  // only needed for CDN's or sub-directory deploy
  //.setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry("base", "./assets/base.js")
  .addEntry("app", "./assets/app.js")
  .addEntry("login", "./assets/login.js")
  .addEntry("switch-plateforms", "./assets/switch-plateforms")

  .addEntry("back-office", "./assets/back-office.js")
  .addEntry("bo-dashboard", "./assets/bo-dashboard.js")

  .addEntry("vueapp", "./assets/vueapp.js")
  .addEntry("dashboard", "./assets/dashboard.js")
  .addEntry("multisociete", "./assets/multisociete.js")
  .addEntry("timesheet-pdf", "./assets/timesheet-pdf.js")
  .addEntry("mon-suivi", "./assets/mon-suivi.js")
  .addEntry("foundation", "./assets/foundation.js")
  .addEntry("fiche-projet", "./assets/fiche-projet.js")
  .addEntry("users-list", "./assets/users-list.js")
  .addEntry("projet-gantt", "./assets/projet-gantt.js")
  .addEntry("projet-events-calendar", "./assets/projet-events-calendar.js")
  .addEntry("users-events-calendar", "./assets/users-events-calendar.js")

  .addEntry("fiche-etude", "./assets/fiche-etude.js")

  .addPlugin(
    new CopyPlugin({
      patterns: [{ from: "assets/ckeditor", to: "ckeditor" }],
    })
  )

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  //.enableStimulusBridge('./assets/controllers.json')

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  // RDI-Manager: disabled because breaks emails template generation because of foundation.css
  //.splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .configureBabel((config) => {
    config.plugins.push("@babel/plugin-proposal-class-properties");
  })

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = 3;
  })

  // Follow same directories structure for images
  .configureFilenames({
    images: "[path][name].[ext]",
  })

  // enables Sass/SCSS support
  .enableSassLoader()

  .enableVueLoader(() => {}, {
    // "false" would be better but not working.
    // Setting "true" to silent this message while building:
    // "To create a smaller (and CSP-compliant) build, see https://symfony.com/doc/current/frontend/encore/vuejs.html#runtime-compiler-build"
    runtimeCompilerBuild: true,
  })

  // uncomment if you use TypeScript
  //.enableTypeScriptLoader()

  // uncomment if you use React
  //.enableReactPreset()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  //.enableIntegrityHashes(Encore.isProduction())

  // uncomment if you're having problems with a jQuery plugin
  // .autoProvidejQuery()

  .autoProvideVariables({
    $: "jquery",
    jQuery: "jquery",
    "window.jQuery": "jquery",
  });

module.exports = Encore.getWebpackConfig();
