const Path = require('path');
const { JavascriptWebpackConfig, CssWebpackConfig } = require('@silverstripe/webpack-config');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const ENV = process.env.NODE_ENV;
const PATHS = {
  MODULES: 'node_modules',
  ROOT: Path.resolve(),
  SRC: Path.resolve('client/src'),
  DIST: Path.resolve('client/dist'),
};

const frontendJsConfig = new JavascriptWebpackConfig('js-frontend', PATHS, 'silverstripe/userforms')
  .setEntry({
    userforms: `${PATHS.SRC}/bundles/bundle.js`,
  })
  .mergeConfig({
    plugins: [
      new CopyWebpackPlugin({
        patterns: [
          {
              from: `${PATHS.MODULES}/jquery/dist/jquery.min.js`,
              to: `${PATHS.DIST}/js`
          },
          {
            from: `${PATHS.MODULES}/jquery.are-you-sure/jquery.are-you-sure.js`,
            to: `${PATHS.DIST}/js/jquery.are-you-sure/`
          },
          {
            context: `${PATHS.MODULES}/jquery-validation/dist`,
            from: '**/*.min.js',
            to: `${PATHS.DIST}/js/jquery-validation/`
          },
        ],
      }),
    ],
  })
  .getConfig();

// Don't apply any externals, as this js will be used on the front-end.
frontendJsConfig.externals = {};

const config = [
  frontendJsConfig,
  // Main JS bundle
  new JavascriptWebpackConfig('js-cms', PATHS, 'silverstripe/userforms')
  .setEntry({
    'userforms-cms': `${PATHS.SRC}/bundles/bundle-cms.js`,
  })
  .mergeConfig({
    plugins: [
      new CopyWebpackPlugin({
        patterns: [
          {
            context: `${PATHS.SRC}/images`,
            from: '**/*.png',
            to: `${PATHS.DIST}/images`
          },
        ],
      }),
    ],
  })
  .getConfig(),
  // sass to css
  new CssWebpackConfig('css', PATHS)
    .setEntry({
      userforms: `${PATHS.SRC}/styles/bundle.scss`,
      'userforms-cms': `${PATHS.SRC}/styles/bundle-cms.scss`,
    })
    .getConfig(),
];

module.exports = config;
