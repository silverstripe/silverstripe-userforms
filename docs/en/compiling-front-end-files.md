# Compiling front-end files

UserForms stylesheets are written in SASS and JavaScript follows ES6 syntax. Both are compiled with Webpack into distributable, minified files.

To get started, you will first need NodeJS, NPM, Webpack and Yarn installed. For more information on this process, [see "Build Tooling" in the SilverStripe documentation](https://docs.silverstripe.org/en/4/contributing/build_tooling/).

## Watching for changes

As you make changes to SASS or JavaScript, you can ask Yarn to watch and rebuild the deltas as their are saved:

```sh
yarn watch
```

This will not minify the dist files.

## Compile assets for production

When you're happy with your changes and are ready to make a pull request you should run a "build" command to compile and minify everything:

```sh
yarn build
```
