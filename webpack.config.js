const Path = require('path');
const dir = require('node-dir');
// Import the core config
const webpackConfig = require('@silverstripe/webpack-config');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const {
  resolveJS,
  externalJS,
  moduleJS,
  pluginJS,
  moduleCSS,
  pluginCSS,
} = webpackConfig;

const ENV = process.env.NODE_ENV;
const PATHS = {
  MODULES: 'node_modules',
  MODULES_ABS: Path.resolve('node_modules'),
  FILES_PATH: '../',
  ROOT: Path.resolve(),
  SRC: Path.resolve('client/src'),
  DIST: Path.resolve('client/dist'),
  DIST_JS: Path.resolve('client/dist/js'),
  THIRDPARTY: Path.resolve('thirdparty'),
};

const copyData = [
  {
    from: 'client/src/images',
    to: 'images'
  },
  {
    from: PATHS.MODULES + '/jquery/dist/jquery.min.js',
    to: PATHS.DIST_JS
  },
  {
    from: PATHS.MODULES + '/jquery.are-you-sure/jquery.are-you-sure.js',
    to: PATHS.DIST_JS + '/jquery.are-you-sure/jquery.are-you-sure.js'
  },
];

/**
 * Builds a list of files matching the `*.min.js` pattern to copy from a source
 * directory to a dist directory.
 */
const addMinFiles = (from, to) => {
  const sourceDir = PATHS.MODULES_ABS + from;
  dir.files(sourceDir, (err, files) => {
    if (err) throw err;
    files.forEach(file => {
      filename = file.replace(sourceDir, '');
      if (!filename.match(/\.min\.js$/)) {
        return;
      }
      copyData.push({
        from: PATHS.MODULES + from + filename,
        to: PATHS.DIST_JS + to + filename
      })
    });
  });
};

addMinFiles('/jquery-validation/dist', '/jquery-validation');


const config = [
  {
    name: 'js-frontend',
    entry: {
      userforms: `${PATHS.SRC}/bundles/bundle.js`,
    },
    output: {
      path: PATHS.DIST,
      filename: 'js/[name].js',
    },
    devtool: (ENV !== 'production') ? 'source-map' : '',
    resolve: resolveJS(ENV, PATHS),
    externals: externalJS(ENV, PATHS),
    module: moduleJS(ENV, PATHS),
    plugins: pluginJS(ENV, PATHS),
  },
  {
    name: 'js-cms',
    entry: {
      'userforms-cms': `${PATHS.SRC}/bundles/bundle-cms.js`,
    },
    output: {
      path: PATHS.DIST,
      filename: 'js/[name].js',
    },
    devtool: (ENV !== 'production') ? 'source-map' : '',
    resolve: resolveJS(ENV, PATHS),
    externals: externalJS(ENV, PATHS),
    module: moduleJS(ENV, PATHS),
    plugins: pluginJS(ENV, PATHS).concat([
      new CopyWebpackPlugin(copyData)
    ])
  },
  {
    name: 'css',
    entry: {
      userforms: `${PATHS.SRC}/styles/bundle.scss`,
      'userforms-cms': `${PATHS.SRC}/styles/bundle-cms.scss`,
    },
    output: {
      path: PATHS.DIST,
      filename: 'styles/[name].css'
    },
    devtool: (ENV !== 'production') ? 'source-map' : '',
    module: moduleCSS(ENV, PATHS),
    plugins: pluginCSS(ENV, PATHS),
  },
];

module.exports = config;
