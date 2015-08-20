# Compiling Front-End Files

UserForms stylesheets are written in SASS, so to make changes you'll need to have Compass installed.

## Debian/Ubuntu

```sh
$ apt-get update
$ apt-get install ruby
$ gem install compass
```

## OSX

[Install homebrew](http://brew.sh). Then:

```sh
$ brew update
$ brew install ruby
$ gem install compass
```

## Compile assets

Make your changes to `scss/UserForms.scss` or `scss/UserForms_cms.scss`. Then navigate to the `userforms` folder and run:

```sh
$ compass compile
```
