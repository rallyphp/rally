var gulp = require('gulp'),
  watchify = require('watchify'),
  source = require('vinyl-source-stream'),
  buffer = require('vinyl-buffer'),
  jshint = require('gulp-jshint'),
  browserify = require('browserify'),
//  concat = require('gulp-concat'),
  rename = require('gulp-rename'),
  uglify = require('gulp-uglify'),
  minifyCss = require('gulp-minify-css'),
  browserSync = require('browser-sync').create();

var paths = {
  css: 'app/css/**/*.css',
  js: 'app/js/**/*.js',
  html: [
    'app/resources/**/*.html',
    'public/partials/**/*.html',
    'public/index.html'
  ]
};

gulp.task('js', function() {
  return browserify('app/js/main.js', {insertGlobals: true, debug: true}).bundle()
    .pipe(source())
    .pipe(gulp.dest('dist/js'))
//    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest('dist/js'))
    .pipe(gulp.dest('public/js'));
});

gulp.task('watchify', function() {
  var bundler = watchify(browserify('app/js/main.js', {
    insertGlobals: true,
    debug: true
  }));

  function rebundle() {
    return bundler.bundle()
      .pipe(source('main.js'))
      .pipe(gulp.dest('dist/js'))
      .pipe(buffer())
//      .pipe(uglify())
      .pipe(rename({
        suffix: '.min'
      }))
      .pipe(gulp.dest('dist/js'))
      .pipe(gulp.dest('public/js'))
      .pipe(browserSync.reload({stream: true}));
  }

  bundler.on('update', rebundle);

  return rebundle();
});

gulp.task('lint', function() {
  return gulp.src(paths.js)
    .pipe(jshint())
    .pipe(jshint.reporter('default'));
});

gulp.task('css', function() {
  return gulp.src(paths.css)
    .pipe(gulp.dest('dist/css'))
    .pipe(minifyCss())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest('dist/css'))
    .pipe(gulp.dest('public/css'))
    .pipe(browserSync.stream());
});

gulp.task('serve', ['watchify'], function() {
  browserSync.init({
    proxy: 'localhost'
  });

  gulp.watch(paths.css, ['css']);
  gulp.watch(paths.html, browserSync.reload);
});

gulp.task('default', ['css', 'js']);
